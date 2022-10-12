<?php

declare(strict_types=1);

namespace MyForksFiles\T3inquisitor\Interfaces;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
interface CommandInterface
{
    public function execute(InputInterface $input, OutputInterface $output);

    public function process(): void;
}
