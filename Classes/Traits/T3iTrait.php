<?php

declare(strict_types=1);

namespace MyForksFiles\T3inquisitor\Traits;

use DateTime;
use Exception;
use MyForksFiles\T3inquisitor\Domain\Repository\ProductsRepository;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Mail\MailMessage;

/*******************************************************************************
 *
 *
 *- -***
 *
 * Trait T3iTrait
 * @package MyForksFiles\T3inquisitor\Traits
 */
trait T3iTrait
{
    protected $db = null;

    public $currentFilePath = '';

    /**
     * @var string
     */
    public $currentLang = null; // 'de'; // default

    /**
     * @var int
     */
    public $currentLangId = null; // = 0; // 'de' default

    /**
     * @var string
     */
    public $extKey = 't3inquisitor';

    public static $devEmail = ['myForksFiles', 'github.com'];
    public static $devEmailSubject = ['github.com/myForksFiles/T3inquisitor'];

    public $sys_language_uid = '';

    /**
     * @see sys_language
     * @var array
     */
    public $langs = [
        0 => 'de',
        1 => 'en',
        2 => 'fr',
        3 => 'pl',
//        4 => 'ru',
    ];

    public $langsIds = []; //populaterd with flips

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objManager = null;

    /**
     * @var array
     */
    public $log = [];

    /**
     * @var array
     */
    public $config = [];

    /**
     * @var string
     */
    public $summaryLog = [];

    public $logger = null; // @todo LoggerAwareTrait

    public $dt = null;

//    public $uriBuilder = null;

#### Typo3 #####################################################################
    public function getBaseUrl()
    {
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId(1);
        $uri = $site->getBase();

        return $uri->getScheme() . '://' . $uri->getHost();
    }

    protected function getExtPath(string $dir = '')
    {
        $results = ExtensionManagementUtility::extPath($this->extKey);

        if ('' !== $dir) {
            $results .= $dir;
        }

        return $results;
    }

    protected function getExtConf(): array
    {
        if ([] === $this->config) {
            $extConfig = GeneralUtility::makeInstance(ExtensionConfiguration::class);
            $this->config = $extConfig->get($this->extKey);
        }

        return $this->config;
    }

    /**
     * @return \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    public function getObjManager(): ObjectManager
    {
        if (null === $this->objManager) {
            $this->objManager = GeneralUtility::makeInstance(ObjectManager::class);
        }

        return $this->objManager;
    }

    public function getPathForFiles(string $path, string $dir = ''): string
    {
        $result = '';

        if (empty($dir)) {
            $dir = $this->dt(null, true);
        }

        $result = Environment::getPublicPath()
            . DIRECTORY_SEPARATOR
            . 'fileadmin'
            . DIRECTORY_SEPARATOR
            . $path
            . DIRECTORY_SEPARATOR
            . $dir;

        return $result;
    }

    public function setCurrentLang(string $lang = ''): void
    {
        if (!empty($lang)) {
            if (is_numeric($lang) && isset($this->langs[$lang]))
            {
                $this->currentLang   = $this->langs[$lang];
                $this->currentLangId = $lang;

                return;

            } else {
                if (in_array($lang, $this->langs))
                {
                    $this->currentLang   = $lang;
                    $this->currentLangId = (array_flip($this->langs))[$lang];

                    return;
                }
            }
        }

//        if (null !== $this->currentLang && null !== $this->currentLangId) {
//            return;
//        }

        if (!empty($this->input->getOption('lang'))) {
            $option = $this->input->getOption('lang');

            if (is_numeric($option) && isset($this->langs[$option]))
            {
                $this->currentLang   = $this->langs[$option];
                $this->currentLangId = $option;

                return;

            } else {
                if (in_array($option, $this->langs))
                {
                    $this->currentLang   = $option;
                    $this->currentLangId = (array_flip($this->langs))[$this->currentLang];

                    return;
                }
            }
        }


        $this->currentLang = 'de';
        $this->currentLangId = 0;

        $this->consoleLog(
            'setting default language: '
            . 'currentLang: ' . $this->currentLang
            . 'currentLangID: ' . $this->currentLangId
        );

        return;
    }

    public function getCurrentLang(): string
    {
        if (null === $this->currentLang && null === $this->currentLangId) {
            $this->currentLang = 'de';
            $this->currentLangId = 0;
        }

        return $this->currentLang;
    }

    /**
     * @Annotation alias
     * @return string
     */
    public function getCurrentLanguage(): string
    {
        return $this->getCurrentLang();
    }

    /**
     * @Annotation alias
     * @return int
     */
    public function getCurrentLanguageId(): int
    {
        return $this->getCurrentLangId();
    }

    public function getCurrentLangId(): int
    {
        if (empty($this->sys_language_uid)) {
            if ((int)TYPO3_version >= 9 || $this->compat_version('9.0')) {
                $languageAspect = GeneralUtility::makeInstance(Context::class)->getAspect('language');

                $this->sys_language_uid = $languageAspect->getId();
            } else {
                $this->sys_language_uid = $GLOBALS['TSFE']->sys_language_uid;
            }
        }

        return $this->sys_language_uid;
    }

    private function compat_version($verNumberStr) {
        return VersionNumberUtility::convertVersionNumberToInteger(TYPO3_branch) >= VersionNumberUtility::convertVersionNumberToInteger($verNumberStr);
    }

    public function getPageId(): int
    {
        if (null === $this->configurationManager) {
            $contentObject = GeneralUtility::makeInstance(ConfigurationManager::class);
            $contentObject = $contentObject->getContentObject();
        } else {
            $contentObject = $this->configurationManager->getContentObject();
        }

        if (isset($contentObject->data['pid'])) {
            return $contentObject->data['pid'];
        }

        return $GLOBALS['TSFE']->id; // @deprecated fixME
    }

    public function getUrlById(int $pageId): string
    {
        if ($pageId < 1) {
            return '';
        }

        if (null === $this->uriBuilder) {
            $this->uriBuilder = $this->getObjManager()->getEmptyObject(\TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder::class);
        }

        $uri = $this->uriBuilder->reset()
            ->setTargetPageUid($pageId)
            ->setCreateAbsoluteUri(true)
//            ->buildFrontendUri()
            ->build()
        ;

        if (null === $uri || (substr_count($uri, '/') < 2)) {
            return '';
        }

        return $uri;
    }

#### Typo3 DB ##################################################################
    /**
     * @param string $typoScriptPath with dots also at the end if this is array
     * @example config.tx_extbase.persistence.updateReferenceIndex
     * @return values as array or string from path or all ts setup array try to avoid it
     * @throws \Exception
     */
    protected function getTypoScriptSetupValues(string $typoScriptPath)
    {
        $configurationManager = $this->getObjManager()->get(ConfigurationManager::class);
        $extBaseFrameworkConfiguration = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

        $tsPathKeys = [];
        if (!empty($typoScriptPath)) {
            if (stristr($typoScriptPath, '.')) {
                $tsPathKeys = explode('.', $typoScriptPath);
            }

            if (!$tsPathKeys) {
                return $extBaseFrameworkConfiguration[$typoScriptPath];
            }

            $values = $extBaseFrameworkConfiguration;
//            $cnt = count($tsPathKeys);
            foreach ($tsPathKeys as $label) {
                if (empty($label)) {
                    continue;
                }

                if (isset($values[$label . '.'])) {
                    $values = $values[$label . '.'];
                } else if(isset($values[$label])) {
                    $values = $values[$label];
                } else {
                    throw new Exception('ts path incorrect');
                }

            }

            return $values;
        }

        return $extBaseFrameworkConfiguration;
    }

    /**
     * @see only for controller
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getTypoScriptSettings()
    {
        $configurationManager = $this->getObjManager()->get(ConfigurationManager::class);
        $results = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);

        if (!$results) {
            throw new Exception('configuration empty');
        }

        return $results;
    }

    protected function executeSql(string $sql): array
    {
        $db = $this->getDbConnection();
        $query = $db->getConnectionByName('Default');
        $results = $query->executeQuery($sql)->fetchAllAssociative();

        return $results;
    }

#### Utils #####################################################################
    public function oneDimensionalArray(array $array)
    {
        return array_reduce($array, 'array_merge', []);
    }

    public function flattenArray(array $array): array
    {
        $result = [];

        array_walk_recursive(
            $array,
            function ($a) use (&$result) {
                $result[] = $a;
            }
        );

        return $result;
    }

    public function dt($timestamp = null, $pathUsable = false): string
    {
        $dataFormat = ($pathUsable) ? 'Y-m-d_Hisu' : 'Y-m-d H:i:s.u';

        $results =(new DateTime());

        if (null !== $timestamp) {
            $results->setTimestamp($timestamp);
        }

        $results = $results->format($dataFormat);

        return $results;
    }

    public function getLangId(string $lang): int
    {
        if (!$this->langsIds) {
            $this->langsIds = array_flip($this->langs);
        }

        return (int)$this->langsIds[$lang];
    }

    protected function log(string $msg = '')
    {
        if (empty($msg)) {
            return;
        }

        $msg = $this->dt() . ' :: ' . $msg . PHP_EOL;

        if (PHP_SAPI === 'cli') {
            $this->output->writeln($msg);
        }

        $this->summaryLog .= $msg;
    }

    public function isJson(string $string): bool
    {
        json_decode($string);

        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function cleanString(string $s, bool $lower = false): string
    {
        $s = trim($s);
        $s = str_replace(['   ', '  '], [' ', ' '], $s);
        $s = str_replace('  ', ' ', $s);
        if ($lower) {
            $s = strtolower($s);
        }
        $s = trim($s);

        return $s;
    }

    public static function isHTML(string $string): bool
    {
        return $string != strip_tags($string) ? true : false;
    }

    public static function notificationMail(array $m = []): void
    {
        $toEmail = implode('@', self::$devEmail);
        $default = [
            'from' => $toEmail,
            'fromName' => 'TYPO3 Website',
            'to' => [$toEmail => 'DevTeam'],
            'subject' => self::$devEmailSubject,
            'files' => [],
            'content' => '',
        ];

        foreach ($default as $k => $v){
            if (isset($m[$k])) {
                continue;
            }

            $m[$k] = $default[$k];
        }

        /** @var MailMessage $mail */
        $mail = GeneralUtility::makeInstance(MailMessage::class);

        $mail->setFrom($m['from'], $m['fromName']);

        if (is_string($m['to'])) {
            $m['to'] = [$m['to'] => $m['to']];
        }

        foreach ($m['to'] as $email => $name) {
            $mail->setTo($m['from'], $m['fromName']);
        }

        $mail->setSubject($m['subject']);

        if (is_string($m['files'])) {
            $m['files'] = [$m['files']];
        }

        if (empty($m['content'])) {
            $m['content'] = 'something wrong, this email has no content';
        }

        if (self::isHTML($m['content'])) {
            $contentType = 'text/html';
            $content = $m['content'];
        } else {
            $contentType = 'text/plain';
            $content = strip_tags($m['content']);
        }

        $mail->setContentType($contentType);
        $mail->setBody($content); // Give it the text message

        if ($m['files']) {
            foreach ($m['files'] as $file) {
                if (empty($file)) {
                    continue;
                }

                $filePath = Environment::getPublicPath() . $file;
                $fileName = explode('/', $file);
                $fileName = end($fileName);

                if (!file_exists($filePath)) {
                    continue;
                }

//                $mail>attachFromPath($file); // Optionally add any attachments
                $mail->attach(\Swift_Attachment::fromPath($filePath->setFilename($fileName)));
            }
        }

        $results = $mail->send(); // And finally send it
    }

#### DEV aliases because faul bin ##############################################
    public function toSql($query)
    {
        $this->varDump($query->getSQL());
    }

    public function sqlDD($query)
    {
        $this->varDump($query->getSQL());

        die(PHP_EOL . __FILE__ . '::' . __LINE__ . PHP_EOL . PHP_EOL);
    }

    public function varDump($values,  $title = null, $maxDepth = 10, $plainText = false)
    {
        if (PHP_SAPI == 'cli') {
            $plainText = true;
        }

        DebuggerUtility::var_dump($values, $title, $maxDepth, $plainText);
    }

    public function varDD($values, $title = null, $maxDepth = 8)
    {
        $this->varDump($values, $title, $maxDepth);

        die(PHP_EOL . __FILE__ . '::' . __LINE__ . PHP_EOL . PHP_EOL);
    }

#### CLI #######################################################################
    public function consoleLog(string $message = '', bool $timeStamp = true): void
    {
        if (empty($message)) {
            return;
        }

        if ($timeStamp) {
            $message = $this->dt() . ' :: ' . $message;
        }

        $this->log[] = $message;

        if ($this->output->isVerbose() && php_sapi_name() === 'cli') {
            $this->output->writeln($message . PHP_EOL);
        }
    }

    public function addSysLog(string $message = ''): void
    {
        if (!count($this->log)) {
            return;
        }

        $log = implode(PHP_EOL, $this->log);

        $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);

        $logger->log(
            LogLevel::INFO,
            $message . PHP_EOL . '-------------' . $log,
            ['extension' => $this->extKey]
        );
    }

    public function getPluginSettings(string $plugin)
    {
        $configurationManager = $this->getObjManager()->get(ConfigurationManager::class);
        $ext = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $s = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
//        $storagePid = $ext['plugin.']['tx_' . $plugin . '.']['settings.']['storagePid'];

        return $ext['plugin.']['tx_' . $plugin . '.']['settings.'];
    }

    public static function getVarDir(string $dir): string
    {
        $varDir = Environment::getVarPath() . DIRECTORY_SEPARATOR . 't3i' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR;

        if (!is_dir($varDir) && !mkdir($varDir, 0777, true) && !is_dir($varDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $varDir));
        }

        return $varDir;
    }
}
