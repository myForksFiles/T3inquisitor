<?php

return [
    'ctrl'    => [
        'title'         => 'LLL:EXT:t3inquisitor/Resources/Private/Language/locallang_db.xlf:tx_t3inquisitor_domain_model_log',
        'label'         => 'data',
        'tstamp'        => 'tstamp',
        'crdate'        => 'crdate',
        'cruser_id'     => 'cruser_id',
        'delete'        => 'deleted',
        'enablecolumns' => [
            'disabled'  => 'hidden',
            'starttime' => 'starttime',
            'endtime'   => 'endtime',
        ],
        'searchFields'  => 'data',
        'iconfile'      => 'EXT:t3inquisitor/Resources/Public/Icons/tx_t3inquisitor_domain_model_log.gif'
    ],
    'types'   => [
        '1' => ['showitem' => 'data, --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, hidden, starttime, endtime'],
    ],
    'columns' => [
        'hidden'    => [
            'exclude' => true,
            'label'   => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
            'config'  => [
                'type'       => 'check',
                'renderType' => 'checkboxToggle',
                'items'      => [
                    [
                        0                    => '',
                        1                    => '',
                        'invertStateDisplay' => true
                    ]
                ],
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config'  => [
                'type'       => 'input',
                'renderType' => 'inputDateTime',
                'eval'       => 'datetime,int',
                'default'    => 0,
                'behaviour'  => [
                    'allowLanguageSynchronization' => true
                ]
            ],
        ],
        'endtime'   => [
            'exclude' => true,
            'label'   => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config'  => [
                'type'       => 'input',
                'renderType' => 'inputDateTime',
                'eval'       => 'datetime,int',
                'default'    => 0,
                'range'      => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ],
                'behaviour'  => [
                    'allowLanguageSynchronization' => true
                ]
            ],
        ],

        'data' => [
            'exclude' => true,
            'label'   => 'LLL:EXT:t3inquisitor/Resources/Private/Language/locallang_db.xlf:tx_t3inquisitor_domain_model_log.data',
            'config'  => [
                'type'    => 'input',
                'size'    => 30,
                'eval'    => 'trim',
                'default' => ''
            ],
        ],

    ],
];
