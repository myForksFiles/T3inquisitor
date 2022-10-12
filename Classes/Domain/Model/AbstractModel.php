<?php

declare(strict_types=1);

namespace MyForksFiles\T3inquisitor\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

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
 * class AbstractModel
 * @package MyForksFiles\T3inquisitor\Domain\Model
 */
abstract class AbstractModel extends AbstractEntity
{
    /**
     * @see stackoverflow.com/questions/33781458/extbase-model-setsyslanguageuid-not-working
     * _languageUid
     * @var int
     */
    protected $_languageUid;

    /**
     * @var int
     */
    protected $hidden = 0;

    /**
     * @var int
     */
    protected $deleted = 0;

    /**
     * @var int
     */
    protected $tstamp = 0;

    /**
     * @var int
     */
    protected $crdate = 0;

    /**
     * @var int
     */
    protected $sorting;

    /**
     * name
     *
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $fileurl = '';

    /**
     * @var int
     */
    protected $productId = 0;

    /**
     * @var string
     */
    public $source = '';

    /**
     * @var string
     */
    public $note = '';

    public function setValues(array $values = []): void
    {
        if (!$values) {
            return;
        }

        foreach ($values as $key => $val) {
            $setMethod = 'set' . ucfirst($key);
            $this->$setMethod($val);
        }
    }

    /**
     * Get sys language
     *
     * @return int
     */
    public function getSysLanguageUid(): int
    {
        return (int)$this->_languageUid;
    }

    /**
     * Set sys language
     *
     * @param int $sysLanguageUid language uid
     * @return void
     */
    public function setSysLanguageUid(int $value = 0): void
    {
        $this->_languageUid = $value;
    }

    /**
     * Returns the name
     *
     * @return string $name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the name
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name = ''): void
    {
        $this->name = $name;
    }

    /**
     * Get tstamp
     *
     * @return \DateTime
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Set tstamp
     *
     * @param \DateTime $tstamp tstamp
     */
    public function setTstamp($tstamp): void
    {
        $this->tstamp = $tstamp;
    }

    /**
     * Get creation date
     *
     * @return \DateTime
     */
    public function getCrdate()
    {
        return $this->crdate;
    }

    /**
     * Set Creation Date
     *
     * @param \DateTime $crdate crdate
     */
    public function setCrdate($crdate): void
    {
        $this->crdate = $crdate;
    }

    /**
     * Get deleted flag
     *
     * @return int
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Get sorting id
     *
     * @return int sorting id
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * Set sorting id
     *
     * @param int $sorting sorting id
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;
    }

    /**
     * Set deleted flag
     *
     * @param int $deleted deleted flag
     */
    public function setDeleted($value): void
    {
        $this->deleted = $value;
    }

    /**
     * Get hidden flag
     *
     * @return int
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set hidden flag
     *
     * @param int $hidden hidden flag
     */
    public function setHidden($value): void
    {
        $this->hidden = $value;
    }

    public function getFileurl(): string
    {
        return $this->fileurl;
    }

    public function setFileurl($value = ''): void
    {
        $this->fileurl = $value;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function setProductId(int $value = 0): void
    {
        $this->productId = $value;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $value = ''): void
    {
        $this->source = $value;
    }

    public function getNote(): string
    {
        return $this->note;
    }

    public function setNote(string $value = ''): void
    {
        $this->note = $value;
    }
}
