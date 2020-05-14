<?php

use DominicJoas\DjImagetools\Task\CompressTask;

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Extbase\\Domain\\Model\\File'] = array(
    'className' => 'DominicJoas\DjImagetools\Domain\Model\File'
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][CompressTask::class] = [
    'extension' => 'dj_imagetools',
    'title' => 'LLL:EXT:dj_imagetools/Resources/Private/Language/locallang_mod.xlf:compressImages',
    'description' => 'Compress all images which aren\'t compressed!',
    'additionalFields' => '',
];

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_extfilefunc.php']['processData'][] = DominicJoas\DjImagetools\Hooks\CompressHook::class;

