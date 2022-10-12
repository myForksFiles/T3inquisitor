<?php

declare(strict_types=1);

namespace MyForksFiles\T3inquisitor\Utility;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Repository;
use MyForksFiles\T3inquisitor\Domain\Repository\TechnicalSafetyRepository;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
 * class Class DevTools
 * @package MyForksFiles\T3inquisitor\Utility
 */
class DevTools
{
    public $langs = [
            0 => 'de',
            1 => 'en',
            2 => 'fr',
            3 => 'pl',
        ];

    public function getPagesList()
    {
        $pages = $this->getPagesListWithPlugins();
        $results = '';

        return $results;
    }

    protected function parseXml($xmlData)
    {
        $xml = simplexml_load_string($xmlData);
        $xml = json_encode($xml);
        $xml = json_decode($xml, true);

        $xml = $xml['data']['sheet']['language']['field']['value'];

        return $xml;
    }

    /** @noinspection */
    private function getSqlPagesListWithPlugins()
    {
        return 'SELECT
    p.slug,
#     p.sys_language_uid,
#     p.uid,
    c.pid,
    c.sys_language_uid,
    c.tstamp,
    c.hidden,
    c.deleted,
    c.header,
    c.list_type AS plugin,
    c.pi_flexform AS selectedValues
FROM tt_content AS c
    LEFT JOIN pages p on c.pid = p.uid
WHERE 1
#  AND c.list_type LIKE "tx_%"
  AND c.hidden = 0
  AND c.deleted = 0
ORDER BY plugin, p.uid';
    }

    public function getPagesListWithPlugins()
    {
        $query = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(TechnicalSafetyRepository::class)
            ->createQuery();
        $query->statement($this->getSqlPagesListWithPlugins());

        $data = $query->execute(true);

        $results = [];
        foreach ($data as $value) {
            $results[$value['pid']][] = $value;
        }

        return $results;
    }
}
