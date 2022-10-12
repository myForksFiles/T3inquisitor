<?php

declare(strict_types=1);

namespace MyForksFiles\T3inquisitor\Command;

use Exception;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use MyForksFiles\T3inquisitor\Interfaces\CommandInterface;

/**
 * This file is part of the "T3inquisitor" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 myForksFiles <myForksFiles(at)github.com>, home
 *
 * @see
 *     github.com/fabpot/local-php-security-checker
 *     MAILTO=sysadmins@example.com
 *     50 23 * * * croncape php-security-checker --path=/path/to/php/project
 *
 *- -***
 *
 * $ ./vendor/bin/typo3 t3i:security --exec=composer
 * class LocalPhpSecurityCheckerCommand
 */
class CheckerCommand extends AbstractCommand implements CommandInterface
{
    protected static $defaultName = 't3i:check';
    protected static $defaultDescription = 'security,composer check';

    public $logsDir = '';
    public $vendorPath = '';

    protected $allowedOptions = [
            'abort'            => 'exit',
            'security'         => 'symfony local-php-security-checker',
            'security-checker' => 'sensiolabs/security-checker !!!DEPERECATED!!! ',
            'composer'         => 'composer',
            'unused'           => 'composer plugin unused package check',
            'unused-scanner'   => 'Insolita/unused-scanner package check',
            'checker'          => 'legacy sensiolabs/security-checker',
            'typo3scan'        => 'python typo3 host scan',
        ];

    protected function configure()
    {
        parent::configure();

        $help = PHP_EOL . 'available "EXEC" option:'
            . PHP_EOL . ' >> example: ./vendor/bin/typo3 t3i:security --exec=composer' . PHP_EOL;
        foreach ($this->allowedOptions as $k => $v) {
            $help .= '    ' . $k . ' - ' . $v . PHP_EOL;
        }
        $help .= PHP_EOL;

        $this->setHelp($help);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        ini_set('max_execution_time', '600');
        $this->logsDir = self::getVarDir('logs');
        $this->projectDir = Environment::getProjectPath();
        $this->path = self::getVarDir('security');
        $this->today = $this->dt->format('Ymd_His');
        $this->action = $this->options['exec'] ?? '';

        if (empty($this->action)) {
            return $this->localPhpSecurityChecker();
        }

        $this->process();

        return Command::SUCCESS;
    }

    public function process(): void
    {
        switch ($this->action) {
            case 'unused-scanner':
                $this->unusedScanner();
                break;
            case 'unused':
                $this->composerUnused();
                break;
            case 'composer':
                $this->composerOutdated();
                break;
            case 'checker':
                $this->securityChecker();
                break;
            case 'typo3scan':
                $this->typo3scan();
                break;
            case 'all':
                $this->localPhpSecurityChecker();
                $this->unusedScanner();
                $this->composerUnused();
                $this->composerOutdated();
                $this->securityChecker();
                $this->typo3scan();
                break;
            case 'symfony':
            case '':
            default:
                $this->localPhpSecurityChecker();
                break;
        }
    }

    /**
     * sensiolabs/security-checker
     *
     * @deprecated
     *
     * @return int
     * @throws Exception
     */
    public function securityChecker()
    {
        $cmd = $this->getVendorPath() . 'security-checker security:check composer.lock';
        $exec = $this->exec($cmd, $this->projectDir);

        return Command::SUCCESS;
    }

    /**
     * github.com/Insolita/unused-scanner
     *
     * @return void
     */
    public function unusedScanner()
    {
        $cmd = $this->getVendorPath() . 'unused_scanner '
            . $this->getExtPath('Classes/Utility') .'/InsolitaUnusedScannerConfig.php >> '
            . $this->logsDir . $this->today . '__composerUnused__.txt';

        $exec = $this->exec($cmd, $this->projectDir);

        return Command::SUCCESS;
    }

    /**
     * github.com/composer-unused/composer-unused
     *
     * @return int
     */
    public function composerUnused()
    {
        $cmd = $this->getVendorPath() . 'composer-unused >> ' . $this->logsDir . $this->today . '__composerUnused__.txt';
        $exec = $this->exec($cmd, $this->projectDir);

        return Command::SUCCESS;
    }

    public function composerOutdated()
    {
        foreach (
            [
                'outdated',
                'suggest',
                'validate',
            ] as $param
        ) {
            $cmd = 'composer ' . $param . ' >> ' . $this->logsDir . $this->today . '__composer__' . $param . '.txt';

            $exec = $this->exec($cmd, $this->projectDir);
        }

        return Command::SUCCESS;
    }

    /**
     * @return void
     * @see github.com/whoot/Typo3Scan.git
     *
     * sudo apt install python3 python3-pip
     *
     */
    public function typo3scan()
    {
        $path = $this->path . 'Typo3Scan';
        $git = 'git clone https://github.com/whoot/Typo3Scan.git';
        if (!is_dir($path)) {
            $gitClone = $this->exec($git, $path);
        }

        $pip3 = exec('pip3 --version');
        if (stristr($pip3, 'command not found')) {
            $pip3 = 'python3 -m pip install -r requirements.txt';
            $install = $this->exec($pip3, $path);
        }

        $scan = 'python3 typo3scan.py -d ' . $this->getBaseUrl() . ' --vuln > '
            . $this->logsDir . $this->today . '_typo3scan.txt';

        $run = $this->exec($scan, $path);

        return Command::SUCCESS;
    }

    /**
     *     github.com/fabpot/local-php-security-checker/releases/download/v2.0.5/local-php-security-checker_2.0.5_linux_amd64
     *     github.com/fabpot/local-php-security-checker/releases/latest
     * releases/latest/download/
     * curl -s https://api.github.com/repos/fabpot/local-php-security-checker/releases/latest | grep -E "browser_download_url(.+)darwin_amd64" | cut -d : -f 2,3 | tr -d \" | wget -qi -
     * curl -s https://api.github.com/repos/fabpot/local-php-security-checker/releases/latest | grep -E "browser_download_url(.+)linux_amd64" | cut -d : -f 2,3 | tr -d \" | wget -qi -
     * curl -s https://api.github.com/repos/fabpot/local-php-security-checker/releases/latest | grep -E "browser_download_url(.+)linux_amd64" | cut -d : -f 2,3 | tr -d \" | wget -O local-php-security-checker -qi -
     *   local-php-security-checker_2.0.5_darwin_amd64
     *   local-php-security-checker_2.0.5_darwin_arm64
     *   local-php-security-checker_2.0.5_linux_386
     *   local-php-security-checker_2.0.5_linux_amd64
     *   local-php-security-checker_2.0.5_linux_arm64
     *   local-php-security-checker_2.0.5_windows_386.exe
     *   local-php-security-checker_2.0.5_windows_amd64.exe
     *   local-php-security-checker_2.0.5_windows_arm64.exe
     *
     * @return void
     */
    public function localPhpSecurityChecker()
    {
        $securityChecker = 'local-php-security-checker';
        $path = self::getVarDir('bin');
//dump($path);
//die(PHP_EOL . __FILE__ . '::' . __LINE__ . PHP_EOL . PHP_EOL);
        if (!file_exists($path . $securityChecker)) {
            if (!$this->getLocalPhpSecurityChecker($path, $securityChecker)) {
                return Command::FAILURE;
            }
        } else {
            chmod($path . $securityChecker, 0755);
        }

        $this->io->writeln(' run: ' . $securityChecker);
//        chmod($path . $securityChecker, 0644);
        $cmd = $path . $securityChecker
            . ' -path="' . $this->projectDir . '/composer.lock" -format=markdown > '
            . $this->logsDir . $this->today . '_' . $securityChecker . '.txt';

        return $this->exec($cmd, $this->path);
    }

    public function getLocalPhpSecurityChecker(string $path, string $file)
    {
        $url = 'https://api.github.com/repos/fabpot/local-php-security-checker/releases/latest';
        $cUrl = 'curl -s ' . $url . ' | grep -E "browser_download_url(.+)linux_amd64" | cut -d : -f 2,3 | tr -d \" | wget -O %s -qi -';

        $cUrl = sprintf($cUrl, $path . $file);
echo $cUrl;
        return $this->exec($cUrl, $path);
    }

    private function getVendorPath()
    {
        if (empty($this->vendorPath)) {
            $this->vendorPath = Environment::getProjectPath() . '/vendor/bin' . DIRECTORY_SEPARATOR;
            if (!is_dir($this->vendorPath)) {
                throw new Exception('vendor dir not exist');
            }
        }

        return $this->vendorPath;
    }
}
