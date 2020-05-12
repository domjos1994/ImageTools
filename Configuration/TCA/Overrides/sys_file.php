<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3_MODE') or die();

$temporaryColumns = array(
    'tx_dj_imagetools_compressed' => array(
        'label' => 'LLL:EXT:dj_imagetools/Resources/Private/Language/locallang_mod.xlf:compressed',
        'config' => array(
            'type' => 'input',
            'size' => 10,
            'eval' => 'trim,int',
        )
    ),
    'tx_dj_imagetools_width' => array(
        'label' => 'LLL:EXT:dj_imagetools/Resources/Private/Language/locallang_mod.xlf:width',
        'config' => array(
            'type' => 'input',
            'size' => 10,
            'eval' => 'trim,int',
        )
    ),
    'tx_dj_imagetools_height' => array(
        'label' => 'LLL:EXT:dj_imagetools/Resources/Private/Language/locallang_mod.xlf:height',
        'config' => array(
            'type' => 'input',
            'size' => 10,
            'eval' => 'trim,int',
        )
    ),
);

ExtensionManagementUtility::addTCAcolumns('sys_file', $temporaryColumns);
ExtensionManagementUtility::addToAllTCAtypes("sys_file", 'tx_dj_imagetools_compressed,tx_dj_imagetools_width,tx_dj_imagetools_Height');

//$GLOBALS['TCA']['sys_file']['types'][$type]['showitem'] .= ',tx_dj_imagetools_compressed,tx_dj_imagetools_width,tx_dj_imagetools_Height';
