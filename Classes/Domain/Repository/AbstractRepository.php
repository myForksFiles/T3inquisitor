<?php

declare(strict_types=1);

namespace MyForksFiles\T3inquisitor\Domain\Repository;

use Exception;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use MyForksFiles\T3inquisitor\Traits\GuwTrait;
use MyForksFiles\T3inquisitor\Traits\Pagination;

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
 * class AbstractRepository
 * @package MyForksFiles\T3inquisitor\Domain\Repository
 */
abstract class AbstractRepository extends Repository
{

}
