<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$icon = 'EXT:dj_imagetools/Resources/Public/Icons/ImageTools.svg';

call_user_func(function($extKey) use ($icon) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, "Configuration/Typoscript", "ImageTools");
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'DominicJoas.' . $extKey,
        'file',
        'tx_imagetools_module1',
        'bottom',
        ['File' => 'list, update, updateAll, disableEntry, undo', 'Meta' => 'list, update', 'Structure' => 'list, delete', 'Settings' => 'list, update'],
        [
            'icon' => $icon,
            'labels' => 'LLL:EXT:dj_imagetools/Resources/Private/Language/locallang_mod.xlf',
        ]
    );
}, "dj_imagetools");

$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
$iconRegistry->registerIcon("imagetools-icon", \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class, ['source' => $icon]);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('xMOD_tx_dj_imagetools', 'EXT:dj_imagetools/Resources/Private/Language/locallang_csh.xlf');

