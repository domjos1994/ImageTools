<?php
    if (!defined('TYPO3_MODE')) {
        die('Access denied.');
    }

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Extbase\\Domain\\Model\\File'] = array(
        'className' => 'DominicJoas\Imgcompromizer\Domain\Model\File'
    );
    
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\DominicJoas\Imgcompromizer\Task\CompressTask::class] = [
        'extension' => 'imgcompromizer',
        'title' => 'Compress images',
        'description' => 'Compress all images which aren\'t compressed!',
        'additionalFields' => '',
    ];

