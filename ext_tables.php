<?php
    if (!defined('TYPO3_MODE')) {
        die('Access denied.');
    }

    call_user_func(function($extKey) {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'DominicJoas.' . $extKey,
            'web',
            'tx_imgcompromizer_module1',
            'bottom',
            ['File' => 'list, edit, update, undo'],
            [
                'icon' => 'EXT:imgcompromizer/Resources/Public/Icons/IMGCompromizer.svg',
                'labels' => 'LLL:EXT:imgcompromizer/Resources/Private/Language/locallang_mod.xlf',
            ]
        );
        
        TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, "Configuration/Typoscript", "ImgCompromizer");
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
        $iconRegistry->registerIcon("imgcompromizer-icon", \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class, ['source' => 'EXT:imgcompromizer/Resources/Public/Icons/IMGCompromizer.svg']);
    }, $_EXTKEY);
