<?php

defined('TYPO3_MODE') || die();

(static function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'T3inquisitor',
        'system',
        't3inquisitor',
        '',
        [
            \MyForksFiles\T3inquisitor\Controller\BackEndController::class => 'list',

        ],
        [
            'access' => 'user,group',
            'icon'   => 'EXT:t3inquisitor/Resources/Public/Icons/user_mod_t3inquisitor.svg',
            'labels' => 'LLL:EXT:t3inquisitor/Resources/Private/Language/locallang_t3inquisitor.xlf',
        ]
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
        'tx_t3inquisitor_domain_model_log',
        'EXT:t3inquisitor/Resources/Private/Language/locallang_csh_tx_t3inquisitor_domain_model_log.xlf'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_t3inquisitor_domain_model_log');
})();
