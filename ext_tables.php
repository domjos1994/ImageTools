<?php

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function($extKey) {
    ExtensionUtility::registerModule(
        'DominicJoas.' . $extKey,
        'file',
        'tx_imagetools_module1',
        'bottom',
        ['File' => 'list, update, updateAll, disableEntry, undo', 'Meta' => 'list, update', 'Structure' => 'list, delete', 'Settings' => 'list, update'],
        [
            'icon' => 'EXT:dj_imagetools/Resources/Public/Icons/ImageTools.svg',
            'labels' => 'LLL:EXT:dj_imagetools/Resources/Private/Language/locallang_mod.xlf',
        ]
    );

    $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
    $iconRegistry->registerIcon("imagetools-icon", SvgIconProvider::class, ['source' => 'EXT:dj_imagetools/Resources/Public/Icons/ImageTools.svg']);
}, $_EXTKEY);

ExtensionManagementUtility::addLLrefForTCAdescr('xMOD_tx_dj_imagetools', 'EXT:dj_imagetools/Resources/Private/Language/locallang_csh.xlf');

