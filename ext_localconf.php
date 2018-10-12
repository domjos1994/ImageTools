<?php
    if (!defined('TYPO3_MODE')) {
        die('Access denied.');
    }

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Extbase\\Domain\\Model\\File'] = array(
        'className' => 'DominicJoas\Imgcompromizer\Domain\Model\File'
    );

