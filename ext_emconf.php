<?php

$EM_CONF[$_EXTKEY] = [
    'title'            => 'T3inquisitor',
    'description'      => 'tests, checker etc',
    'category'         => 'module',
    'author'           => 'myForksFiles',
    'author_email'     => 'myForksFiles@github.com',
    'state'            => 'experimental',
    'clearCacheOnLoad' => 0,
    'version'          => '0.0.1',
    'constraints'      => [
        'depends'   => [
            'typo3' => '10.4.0-11.4.99',
        ],
        'conflicts' => [],
        'suggests'  => [],
    ],
];
