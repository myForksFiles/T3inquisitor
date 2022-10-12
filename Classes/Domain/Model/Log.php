<?php

declare(strict_types=1);

namespace MyForksFiles\T3inquisitor\Domain\Model;

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
 * class Log
 */
class Log extends AbstractModel
{

    /**
     * data
     *
     * @var string
     */
    protected $data = '';

    /**
     * Returns the data
     *
     * @return string $data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the data
     *
     * @param string $data
     * @return void
     */
    public function setData(string $data)
    {
        $this->data = $data;
    }
}
