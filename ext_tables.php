<?php

use DominicJoas\DjImagetools\Controller\FileController;
use DominicJoas\DjImagetools\Controller\MetaController;
use DominicJoas\DjImagetools\Controller\SettingsController;
use DominicJoas\DjImagetools\Controller\StructureController;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$icon = 'EXT:dj_imagetools/Resources/Public/Icons/ImageTools.svg';

call_user_func(function($extKey) use ($icon) {
    ExtensionManagementUtility::addStaticFile($extKey, "Configuration/Typoscript", "ImageTools");
    ExtensionUtility::registerModule(
        'DominicJoas.' . $extKey,
        'file',
        'tx_imagetools_module1',
        'bottom',
        [
            FileController::class => 'list, update, updateAll, disableEntry, undo',
            MetaController::class => 'list, update',
            StructureController::class => 'list, delete',
            SettingsController::class => 'list, update'
        ],
        [
            'icon' => $icon,
            'labels' => 'LLL:EXT:dj_imagetools/Resources/Private/Language/locallang_mod.xlf',
        ]
    );
}, "dj_imagetools");

$iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
$iconRegistry->registerIcon("imagetools-icon", SvgIconProvider::class, ['source' => $icon]);

ExtensionManagementUtility::addLLrefForTCAdescr('xMOD_tx_dj_imagetools', 'EXT:dj_imagetools/Resources/Private/Language/locallang_csh.xlf');

