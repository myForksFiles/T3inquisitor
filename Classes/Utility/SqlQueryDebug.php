<?php

declare(strict_types=1);

namespace MyForksFiles\T3inquisitor\Utility;

use Symfony\Component\VarDumper\VarDumper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class SqlQueryDebug
 * @package MyForksFiles\T3inquisitor\Utility
 */
class SqlQueryDebug
{
    public $objManager = null;

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjManager(): ObjectManager
    {
        if (null === $this->objManager) {
            $this->objManager = GeneralUtility::makeInstance(ObjectManager::class);
        }

        return $this->objManager;
    }

    public function getSql($query, $plainText = true)
    {
        $queryParser = GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class
        );
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
            $queryParser->convertQueryToDoctrineQueryBuilder($query)->getSQL(),
            null,
            8,
            true
        );
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(
            $queryParser->convertQueryToDoctrineQueryBuilder($query)->getParameters(),
            null,
            8,
            true
        );
    }

    public function getQuerySql($query)
    {
        $queryParser = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class);

        $sql = $queryParser->convertQueryToDoctrineQueryBuilder($query)->getSQL();
        $params = $queryParser->convertQueryToDoctrineQueryBuilder($query)->getParameters();

        if (php_sapi_name() === 'cli') {
            VarDumper::dump($sql);
            VarDumper::dump($params);
        } else {
            \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($sql);
            \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($params);
        }
    }

    private function debugQuery($query, $format = true, $exit = true)
    {
        function getFormattedSQL($sql_raw)
        {
            if (empty($sql_raw) || !is_string($sql_raw)) {
                return false;
            }
            $sql_reserved_all = [
                'ACCESSIBLE',
                'ACTION',
                'ADD',
                'AFTER',
                'AGAINST',
                'AGGREGATE',
                'ALGORITHM',
                'ALL',
                'ALTER',
                'ANALYSE',
                'ANALYZE',
                'AND',
                'AS',
                'ASC',
                'AUTOCOMMIT',
                'AUTO_INCREMENT',
                'AVG_ROW_LENGTH',
                'BACKUP',
                'BEGIN',
                'BETWEEN',
                'BINLOG',
                'BOTH',
                'BY',
                'CASCADE',
                'CASE',
                'CHANGE',
                'CHANGED',
                'CHARSET',
                'CHECK',
                'CHECKSUM',
                'COLLATE',
                'COLLATION',
                'COLUMN',
                'COLUMNS',
                'COMMENT',
                'COMMIT',
                'COMMITTED',
                'COMPRESSED',
                'CONCURRENT',
                'CONSTRAINT',
                'CONTAINS',
                'CONVERT',
                'CREATE',
                'CROSS',
                'CURRENT_TIMESTAMP',
                'DATABASE',
                'DATABASES',
                'DAY',
                'DAY_HOUR',
                'DAY_MINUTE',
                'DAY_SECOND',
                'DEFINER',
                'DELAYED',
                'DELAY_KEY_WRITE',
                'DELETE',
                'DESC',
                'DESCRIBE',
                'DETERMINISTIC',
                'DISTINCT',
                'DISTINCTROW',
                'DIV',
                'DO',
                'DROP',
                'DUMPFILE',
                'DUPLICATE',
                'DYNAMIC',
                'ELSE',
                'ENCLOSED',
                'END',
                'ENGINE',
                'ENGINES',
                'ESCAPE',
                'ESCAPED',
                'EVENTS',
                'EXECUTE',
                'EXISTS',
                'EXPLAIN',
                'EXTENDED',
                'FAST',
                'FIELDS',
                'FILE',
                'FIRST',
                'FIXED',
                'FLUSH',
                'FOR',
                'FORCE',
                'FOREIGN',
                'FROM',
                'FULL',
                'FULLTEXT',
                'FUNCTION',
                'GEMINI',
                'GEMINI_SPIN_RETRIES',
                'GLOBAL',
                'GRANT',
                'GRANTS',
                'GROUP',
                'HAVING',
                'HEAP',
                'HIGH_PRIORITY',
                'HOSTS',
                'HOUR',
                'HOUR_MINUTE',
                'HOUR_SECOND',
                'IDENTIFIED',
                'IF',
                'IGNORE',
                'IN',
                'INDEX',
                'INDEXES',
                'INFILE',
                'INNER',
                'INSERT',
                'INSERT_ID',
                'INSERT_METHOD',
                'INTERVAL',
                'INTO',
                'INVOKER',
                'IS',
                'ISOLATION',
                'JOIN',
                'KEY',
                'KEYS',
                'KILL',
                'LAST_INSERT_ID',
                'LEADING',
                'LEFT',
                'LEVEL',
                'LIKE',
                'LIMIT',
                'LINEAR',
                'LINES',
                'LOAD',
                'LOCAL',
                'LOCK',
                'LOCKS',
                'LOGS',
                'LOW_PRIORITY',
                'MARIA',
                'MASTER',
                'MASTER_CONNECT_RETRY',
                'MASTER_HOST',
                'MASTER_LOG_FILE',
                'MASTER_LOG_POS',
                'MASTER_PASSWORD',
                'MASTER_PORT',
                'MASTER_USER',
                'MATCH',
                'MAX_CONNECTIONS_PER_HOUR',
                'MAX_QUERIES_PER_HOUR',
                'MAX_ROWS',
                'MAX_UPDATES_PER_HOUR',
                'MAX_USER_CONNECTIONS',
                'MEDIUM',
                'MERGE',
                'MINUTE',
                'MINUTE_SECOND',
                'MIN_ROWS',
                'MODE',
                'MODIFY',
                'MONTH',
                'MRG_MYISAM',
                'MYISAM',
                'NAMES',
                'NATURAL',
                'NOT',
                'NULL',
                'OFFSET',
                'ON',
                'OPEN',
                'OPTIMIZE',
                'OPTION',
                'OPTIONALLY',
                'OR',
                'ORDER',
                'OUTER',
                'OUTFILE',
                'PACK_KEYS',
                'PAGE',
                'PARTIAL',
                'PARTITION',
                'PARTITIONS',
                'PASSWORD',
                'PRIMARY',
                'PRIVILEGES',
                'PROCEDURE',
                'PROCESS',
                'PROCESSLIST',
                'PURGE',
                'QUICK',
                'RAID0',
                'RAID_CHUNKS',
                'RAID_CHUNKSIZE',
                'RAID_TYPE',
                'RANGE',
                'READ',
                'READ_ONLY',
                'READ_WRITE',
                'REFERENCES',
                'REGEXP',
                'RELOAD',
                'RENAME',
                'REPAIR',
                'REPEATABLE',
                'REPLACE',
                'REPLICATION',
                'RESET',
                'RESTORE',
                'RESTRICT',
                'RETURN',
                'RETURNS',
                'REVOKE',
                'RIGHT',
                'RLIKE',
                'ROLLBACK',
                'ROW',
                'ROWS',
                'ROW_FORMAT',
                'SECOND',
                'SECURITY',
                'SELECT',
                'SEPARATOR',
                'SERIALIZABLE',
                'SESSION',
                'SET',
                'SHARE',
                'SHOW',
                'SHUTDOWN',
                'SLAVE',
                'SONAME',
                'SOUNDS',
                'SQL',
                'SQL_AUTO_IS_NULL',
                'SQL_BIG_RESULT',
                'SQL_BIG_SELECTS',
                'SQL_BIG_TABLES',
                'SQL_BUFFER_RESULT',
                'SQL_CACHE',
                'SQL_CALC_FOUND_ROWS',
                'SQL_LOG_BIN',
                'SQL_LOG_OFF',
                'SQL_LOG_UPDATE',
                'SQL_LOW_PRIORITY_UPDATES',
                'SQL_MAX_JOIN_SIZE',
                'SQL_NO_CACHE',
                'SQL_QUOTE_SHOW_CREATE',
                'SQL_SAFE_UPDATES',
                'SQL_SELECT_LIMIT',
                'SQL_SLAVE_SKIP_COUNTER',
                'SQL_SMALL_RESULT',
                'SQL_WARNINGS',
                'START',
                'STARTING',
                'STATUS',
                'STOP',
                'STORAGE',
                'STRAIGHT_JOIN',
                'STRING',
                'STRIPED',
                'SUPER',
                'TABLE',
                'TABLES',
                'TEMPORARY',
                'TERMINATED',
                'THEN',
                'TO',
                'TRAILING',
                'TRANSACTIONAL',
                'TRUNCATE',
                'TYPE',
                'TYPES',
                'UNCOMMITTED',
                'UNION',
                'UNIQUE',
                'UNLOCK',
                'UPDATE',
                'USAGE',
                'USE',
                'USING',
                'VALUES',
                'VARIABLES',
                'VIEW',
                'WHEN',
                'WHERE',
                'WITH',
                'WORK',
                'WRITE',
                'XOR',
                'YEAR_MONTH'
            ];

            $sql_skip_reserved_words = ['AS', 'ON', 'USING'];
            $sql_special_reserved_words = ['(', ')'];
            $sql_raw = str_replace("\n", ' ', $sql_raw);
            $sql_formatted = '';
            $prev_word = '';
            $word = '';
            for ($i = 0, $j = strlen($sql_raw); $i < $j; $i++) {
                $word .= $sql_raw[$i];
                $word_trimmed = trim($word);
                if ($sql_raw[$i] === ' ' || in_array(
                        $sql_raw[$i],
                        $sql_special_reserved_words
                    )) {
                    $word_trimmed = trim($word);
                    $trimmed_special = false;
                    if (in_array($sql_raw[$i], $sql_special_reserved_words)) {
                        $word_trimmed = substr($word_trimmed, 0, -1);
                        $trimmed_special = true;
                    }
                    $word_trimmed = strtoupper($word_trimmed);
                    if (in_array($word_trimmed, $sql_reserved_all)
                        && !in_array($word_trimmed, $sql_skip_reserved_words)
                    ) {
                        if (in_array($prev_word, $sql_reserved_all)) {
                            $sql_formatted .= '<b>' . strtoupper(trim($word)) . '</b>' . '&nbsp;';
                        } else {
                            $sql_formatted .= '<br/>&nbsp;';
                            $sql_formatted .= '<b>' . strtoupper(trim($word)) . '</b>' . '&nbsp;';
                        }
                        $prev_word = $word_trimmed;
                        $word = "";
                    } else {
                        $sql_formatted .= trim($word) . '&nbsp;';
                        $prev_word = $word_trimmed;
                        $word = "";
                    }
                }
            }
            $sql_formatted .= trim($word);
            return $sql_formatted;
        }

        $queryParser = GeneralUtility::makeInstance(
            \TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser::class
        );
        $preparedStatement = $queryParser->convertQueryToDoctrineQueryBuilder($query)->getSQL();
        $parameters = $queryParser->convertQueryToDoctrineQueryBuilder($query)->getParameters();
        $stringParams = [];
        foreach ($parameters as $key => $parameter) {
            $stringParams[':' . $key] = $parameter;
        }
        $statement = strtr($preparedStatement, $stringParams);
        if ($format) {
            echo '<code>' . getFormattedSQL($statement) . '</code>';
        } else {
            echo $statement;
        }
        if ($exit) {
            exit;
        }
    }
}
