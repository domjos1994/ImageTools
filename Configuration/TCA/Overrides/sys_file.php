<?php
defined('TYPO3_MODE') or die();

$temporaryColumns = array(
    'tx_imgcompromizer_compressed' => array(
        'label' => 'LLL:EXT:imgcompromizer/Resources/Private/Language/locallang_mod.xlf:compressed',
        'config' => array(
            'type' => 'input',
            'size' => 10,
            'eval' => 'trim,int',
        )
    ),
    'tx_imgcompromizer_width' => array(
        'label' => 'LLL:EXT:imgcompromizer/Resources/Private/Language/locallang_mod.xlf:width',
        'config' => array(
            'type' => 'input',
            'size' => 10,
            'eval' => 'trim,int',
        )
    ),
    'tx_imgcompromizer_height' => array(
        'label' => 'LLL:EXT:imgcompromizer/Resources/Private/Language/locallang_mod.xlf:height',
        'config' => array(
            'type' => 'input',
            'size' => 10,
            'eval' => 'trim,int',
        )
    ),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
        'sys_file', $temporaryColumns
);

$GLOBALS['TCA']['sys_file']['types'][$type]['showitem'] .= ',tx_imgcompromizer_compressed,tx_imgcompromizer_width,tx_imgcompromizer_Height';
