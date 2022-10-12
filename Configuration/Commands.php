<?php

return [

#### Typo3 #####################################################################
    't3i:clean' => [
        'class'       => \MyForksFiles\T3inquisitor\Command\CleanCacheCommand::class,
        'schedulable' => true,
    ],
    't3i:check' => [
        'class'       => \MyForksFiles\T3inquisitor\Command\CheckerCommand::class,
        'schedulable' => true,
    ],

#### BE #####################################################################


#### FE #####################################################################
    't3i:sitemap' => [
        'class'       => \MyForksFiles\T3inquisitor\Command\SiteMapCommand::class,
        'schedulable' => true,
    ],

];

//$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['cliKeys']
