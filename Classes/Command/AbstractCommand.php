<?php

declare(strict_types=1);

namespace MyForksFiles\T3inquisitor\Command;

use Exception;
use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use MyForksFiles\T3inquisitor\Traits\T3iTrait;
use MyForksFiles\T3inquisitor\Domain\Model\Log;

/**
 * This file is part of the "T3inquisitor" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2022 myForksFiles <myForksFiles(at)github.com>, home
 *
 *
 *- -***
 *
 * class AbstractCommand
 */
abstract class AbstractCommand extends Command
{
    use T3iTrait;

    protected $path = '';

    public $execOutput = [];

    protected function configure()
    {
        $o = InputOption::VALUE_OPTIONAL;
        $this->addOption('all', 'a', $o, 'force all')
            ->addOption('exec', 'e', $o, 'execute selected action')
            ->addOption('lang', 'l', $o, 'language as string en')
            ->addOption('file', 'f', $o, 'input/output ');

        $this->setDescription(self::$defaultDescription ?? '');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);

        $this->options = $this->input->getOptions();

        $this->now = time();
        $this->dt = Carbon::now();
    }

    public function consoleLog(string $msg, bool $when = false): void
    {
        if (php_sapi_name() !== 'cli') {
            return;
        }

        if (null !== $this->output && $this->output->isVerbose()) {
            if ($when) {
                $msg = (new DateTime())->format('Y-m-d H:i:s.u') . ' :: ' . $msg;
            }

            $this->output->writeln($msg);
        }
    }

    protected static function getTempPath(): string
    {
        $path = Environment::getPublicPath() . DIRECTORY_SEPARATOR . 'typo3temp' . DIRECTORY_SEPARATOR;

        if (is_dir($path)) {
            return $path;
        }

        throw new Exception('typo3temp error: ' . $path);

        return '';
    }

    protected function getTempSizeLinux(string $path): int
    {
        $io = popen('du -sk ' . $path, 'r');
        $size = fgets($io, 4096);
        $size = substr($size, 0, strpos($size, "\t"));
        pclose($io);

        return (int)$size;
    }

    protected function getTempSizeWindows(string $path): int
    {
        $comObj = new COM('scripting.filesystemobject');
        if (is_object($comObj)) {
            $ref = $comObj->getfolder($path);
            return (int)$ref->size;
        }

        return 0;
    }

    protected function getTempSize(string $path = '')
    {
        if (empty($path)) {
            $path = self::getTempPath();
        }

        if (empty($path)) {
            $this->consoleLog('typo3temp size: error: ' . $path);

            return 0;
        }

        $phpOs = strtolower(PHP_OS_FAMILY);
        switch ($phpOs) {
            case 'Linux':
            case 'linux':
            case 'lin':
            case 'Darwin':
            case 'darwin':
            case 'apple':
                return $this->getTempSizeLinux($path);
            case 'Windows':
            case 'win':
                return $this->getTempSizeWindows($this->tempPath);
            default:
                return 0;
        }
    }

    protected function sizeFormat(int $bytes = 0)
    {
        $kb = 1024;
        $mb = $kb * 1024;
        $gb = $mb * 1024;
        $tb = $gb * 1024;

        if (($bytes >= 0) && ($bytes < $kb)) {
            return $bytes . ' B';
        } elseif (($bytes >= $kb) && ($bytes < $mb)) {
            return ceil($bytes / $kb) . ' KB';
        } elseif (($bytes >= $mb) && ($bytes < $gb)) {
            return ceil($bytes / $mb) . ' MB';
        } elseif (($bytes >= $gb) && ($bytes < $tb)) {
            return ceil($bytes / $gb) . ' GB';
        } elseif ($bytes >= $tb) {
            return ceil($bytes / $tb) . ' TB';
        } else {
            return $bytes . ' B';
        }
    }

    protected function exec(string $cmd, string $path)
    {
        $process = Process::fromShellCommandline($cmd, $path);
        $process->run();
//        $this->execOutput = $process->addOutput();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);

            $this->io->error(' cmd ' . $cmd . ' failed');

            return Command::FAILURE;
        }

        $this->io->success(' executed ' . $cmd);

        return Command::SUCCESS;
    }
}
