<?php

declare(strict_types=1);

namespace MyForksFiles\T3inquisitor\Command;

use Exception;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use MyForksFiles\T3inquisitor\Traits\T3iTrait;

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
 * class
 */
class CleanCacheCommand extends AbstractCommand
{
    protected static $defaultName = 't3i:cache:clear';
    protected static $defaultDescription = 'clear different Typo3 cache';

    protected $be = null;

    protected $dbConnection = null;

    protected $cliAuth = null;

    protected $clearOptions = [
        'abort'        => 'exit',
        'all'          => 'run all available option and clear it',
        'cache'        => 'cache Typo3 cache',
        'cache:remove' => 'removing all from ./var/cache and public/typo3temp',
        'tables'       => 'table cache',
        'logs'         => 'logs',
        'opcache'      => 'opcache',
        'redis'        => 'redis cache',
        'apc'          => 'apc cache',
        'l10n'         => 'langs XLF cache from cache/db',
    ];

    protected $cacheTables = [
        'available' => [],
        'logs' => [
//            'sys_history',
            'sys_log',
        ],
        'cache' => [
            'cache_md5params',
            'cache_treelist',
            'cf_cache_hash',
            'cf_cache_hash_tags',
            'cf_cache_imagesizes',
            'cf_cache_imagesizes_tags',
            'cf_cache_pages',
            'cf_cache_pages_tags',
            'cf_cache_pagesection',
            'cf_cache_pagesection_tags',
            'cf_cache_rootline',
            'cf_cache_rootline_tags',

            'cache_adminpanel_requestcache',
            'cache_adminpanel_requestcache_tags',
//            'cache_news_category',
//            'cache_news_category_tags',
            'cache_treelist',
//            'cache_ttaddress_category',
//            'cache_ttaddress_category_tags',
//            'cache_ttaddress_geocoding',
//            'cache_ttaddress_geocoding_tags',
//            'cache_vhs_main',
//            'cache_vhs_main_tags',
//            'cache_vhs_markdown',
//            'cache_vhs_markdown_tags',
        ],
        'extbase' => [
            'cf_extbase_datamapfactory_datamap',
            'cf_extbase_datamapfactory_datamap_tags',
            'cf_extbase_object',
            'cf_extbase_object_tags',
            'cf_extbase_reflection',
            'cf_extbase_reflection_tags',

            'cf_extbase_typo3dbbackend_queries',
            'cf_extbase_typo3dbbackend_queries_tags',
            'cf_extbase_typo3dbbackend_tablecolumns',
            'cf_extbase_typo3dbbackend_tablecolumns_tags',
            'cf_fluidcontent',
            'cf_fluidcontent_tags',
            'cf_flux',
            'cf_flux_tags',
            'cf_schemaker',
            'cf_schemaker_tags',
            'cf_vhs_main',
            'cf_vhs_main_tags',
            'cf_vhs_markdown',
            'cf_vhs_markdown_tags',
        ],
    ];

    protected $objManager = null;

    protected $io = null;

    protected function configure()
    {
        parent::configure();

        $help = PHP_EOL . 'available "EXEC" option:'
            . PHP_EOL . ' >> example: ./vendor/bin/typo3 t3i:clean --exec=cache:remove' . PHP_EOL;
        foreach ($this->clearOptions as $k => $v) {
            $help.= '    ' . $k . ' - ' . $v . PHP_EOL;
        }
        $help.= PHP_EOL;
        $this->setHelp($help);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appContext = Environment::getContext()->__toString();
        $this->isDev = Environment::getContext()->isDevelopment();
        if (!$this->isDev) {

            throw new Exception('allowed only on dev');

            return Command::INVALID;
        }

        $this->input = $input;
        $this->output = $output;
        $this->now = time();
        $this->io = new SymfonyStyle($input, $output);
        $this->options = $this->input->getOptions();

        $this->consoleLog('Typo3 cache/tables/files will be cleared');

        $this->action = $this->options['exec'] ?? '';

        if (empty($this->action)) {
//            $this->io->note('missing action, please chose: action');
            foreach ($this->clearOptions as $k => $v) {
                $output->writeln(' ' . $k . ' - ' . $v);
            }
            $options = array_keys($this->clearOptions);
            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion('action not selected, please chose: action', $options, 0);

//        $question->setErrorMessage('%s is invalid');

            $this->action = $helper->ask($input, $output, $question);
            $output->writeln('selected: ' . $this->action);
        }

        if ($this->action === 'abort') {
            return Command::SUCCESS;
        }

        return $this->executeSelectedAction();
    }

    private function executeSelectedAction(): int
    {
        if ($this->action === 'all') {
            return $this->runAll();
        }

        $method = $this->getMethodName($this->action);

        if (method_exists($this, $method)) {
            $this->consoleLog(PHP_EOL . 'action: ' . $method);
            return $this->$method();
        }

        return Command::FAILURE;
    }

    public function runAll(): int
    {
        $this->initBE();
        $GLOBALS['BE_USER']->user['admin'] = 1;

        $tce = GeneralUtility::makeInstance(DataHandler::class);
        $tce->start([], []);
        $tce->clear_cacheCmd('all');

        foreach ($this->clearOptions as $key => $description) {
            $this->consoleLog('>>>  run - ' . $description);
            $this->runClean($key);
        }

        return Command::SUCCESS;
    }

    private function runClean(string $option): void
    {
        $method = 'clear' . ucfirst($option);

        if (method_exists($this, $method)) {
            $this->consoleLog(PHP_EOL . 'action: ' . $method);
            $this->$method();
        }
    }

    public function initBE(): void
    {
        $this->be = GeneralUtility::makeInstance(BackendUserAuthentication::class);
        $this->be->start();

        $this->cliAuth = GeneralUtility::makeInstance(CommandLineUserAuthentication::class);
        $this->cliAuth->start();
        $this->cliAuth->authenticate('_cli_');
    }

    private function getMethodName(string $string): string
    {
        $regExpAlpha = '/[^A-Za-z0-9 ]/'; // "/[^A-Za-z0-9 ]/";
        $result = preg_replace($regExpAlpha, '_', $string);
        $result = 'clear_' . $result;
        $result = GeneralUtility::underscoredToLowerCamelCase($result);

        return $result;
    }

    /**
     * @noinspection
     *
     * @return string
     */
    protected function getTablesSizeSql(): string
    {
        /** @noinspection $sql */
        $sql = 'SELECT `table_name`, round(((data_length + index_length) / 1024 / 1024), 2) AS size
 FROM `INFORMATION_SCHEMA`.`TABLES`
 WHERE 1
     AND `TABLE_SCHEMA` = "' . $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['dbname'] . '"
 ORDER BY `table_name`;';
//dump(str_replace(PHP_EOL, '', $sql));
        return $sql;
    }

    protected function clearTables(): int
    {
        $results = [];
        $tablesList = array_merge($this->cacheTables['logs'], $this->cacheTables['cache']);
        $tables = $this->executeSql($this->getTablesSizeSql());

        $db = $this->getDbConnection();
        foreach ($tables as $table) {
            if (!in_array($table['table_name'], $tablesList)) {
                continue;
            }

            try {
                $status = $db->getConnectionForTable($table['table_name'])->truncate($table['table_name']);
                $results[$table['table_name']] = [
                    'name'   => $table['table_name'],
                    'size'   => $table['size'],
                    'status' => $status,
                ];

                $this->io->success('cleared tables cache');
            } catch (Exception $e) {
                $this->consoleLog('table error: ' . $e->getMessage());

                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }

    protected function clearCache(): int
    {
        $this->consoleLog('cache size: ' . $this->sizeFormat($this->getTempSize()));

        $cacheService = GeneralUtility::makeInstance(CacheManager::class);
        $cacheService->flushCaches();

        $this->io->success('cleat Typo3 cache: ' . $this->sizeFormat($this->getTempSize()));

        return Command::SUCCESS;
    }

    protected function clearCacheRemove(): int
    {
        $cmd = 'find %s -type f -exec rm -i {} \;';

        $this->io->writeln('--- removing cache ---');
        $path = Environment::getVarPath() . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
        $this->io->writeln('cache size before' . $path . ': ' . $this->sizeFormat($this->getTempSize($path)));

        $this->exec(sprintf($cmd, $path), $path);

        $this->io->success('cache size AFTER' . $path . ': ' . $this->sizeFormat($this->getTempSize($path)));

        $this->io->writeln('---');

        $path = self::getTempPath();
        $this->io->writeln('cache size before' . $path . ': ' . $this->sizeFormat($this->getTempSize($path)));

        $this->exec(sprintf($cmd, $path), $path);
//        $process = Process::fromShellCommandline(sprintf($cmd, $path), $path);
//        $process->run();
//        if (!$process->isSuccessful()) {
//            throw new ProcessFailedException($process);
//        }
        $this->io->success('cache size AFTER' . $path . ': ' . $this->sizeFormat($this->getTempSize($path)));

        return Command::SUCCESS;
    }

    protected function clearLogs(): int
    {
        $cmd = 'find %s -type f -exec rm -i {} \;';
        $path = Environment::getVarPath() . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR;

        $this->exec(sprintf($cmd, $path), $path);
        $this->io->success('cleared logs');

        $db = $this->getDbConnection();
        $status = $db->getConnectionForTable('sys_log')->truncate('sys_log');
        $this->io->success('cleared sys_log table');

        return Command::SUCCESS;
    }

    protected function clearOpcache(): int
    {
        $status = opcache_get_status();

        if (!$status['opcache_enabled']) {
            $this->consoleLog('OpCache DISABLED');

            return Command::INVALID;
        }

        if (opcache_reset()) {
            $this->io->success('cleared OpCache Reset: OK');

            return Command::SUCCESS;
        }
        $this->consoleLog('OpCache Reset: Failed.');

        return Command::FAILURE;
    }

    protected function clearApc(): int
    {
        if (!extension_loaded('apc') || !ini_get('apc.enabled')) {
            $this->consoleLog('ApcCache DISABLED');

            return Command::INVALID;
        }

        if (apc_clear_cache()) {
            $this->io->success('cleared APC');

            return Command::SUCCESS;
        }

        $this->consoleLog('ApcCache Reset: Failed.');

        return Command::FAILURE;
    }

    protected function clearRedis(): int
    {
        if (!class_exists(Predis::class)) {
            return Command::SUCCESS;
        }

        try {
            $redis = \Predis::instance();
            $redis->flushAll();

            $this->io->success('cleared predis');
        } catch (\RedisException $e) {
            $this->consoleLog('table error: ' . $e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function clearL10n(): int
    {
        try {
            $cacheFrontend = GeneralUtility::makeInstance(CacheManager::class)->getCache('l10n');
            $cacheFrontend->flush();

            $this->io->success('cleared language cache l10n');
        } catch (Exception $e) {
            $this->io->error(
                sprintf(
                    'Failed to clear the language cache (l10n). Error: %s (%d)',
                    $e->getMessage(),
                    $e->getCode()
                )
            );

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
