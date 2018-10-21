<?php
    if (!defined('TYPO3_MODE')) {
        die('Access denied.');
    }

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Extbase\\Domain\\Model\\File'] = array(
        'className' => 'DominicJoas\DjImagetools\Domain\Model\File'
    );
    
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\DominicJoas\ImageTools\Task\CompressTask::class] = [
        'extension' => 'dj_imagetools',
        'title' => 'LLL:EXT:dj_imagetools/Resources/Private/Language/locallang_mod.xlf:imageTools',
        'description' => 'Compress all images which aren\'t compressed!',
        'additionalFields' => '',
    ];

