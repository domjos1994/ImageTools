<?php
    if (!defined('TYPO3_MODE')) {
        die('Access denied.');
    }

    call_user_func(function($extKey) {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'DominicJoas.' . $extKey,
            'web',
            'tx_imagetools_module1',
            'bottom',
            ['File' => 'list, update, updateAll, undo', 'Structure' => 'list, update'],
            [
                'icon' => 'EXT:dj_imagetools/Resources/Public/Icons/ImageTools.svg',
                'labels' => 'LLL:EXT:dj_imagetools/Resources/Private/Language/locallang_mod.xlf',
            ]
        );
        
        TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, "Configuration/Typoscript", "ImageTools");
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
        $iconRegistry->registerIcon("imagetools-icon", \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class, ['source' => 'EXT:dj_imagetools/Resources/Public/Icons/ImageTools.svg']);
    }, $_EXTKEY);
