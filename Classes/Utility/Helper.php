<?php
namespace DominicJoas\DjImagetools\Utility;

use \TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Helper {
    
    public static function getMessageType($type) {
        $messageType = NULL;
        switch($type) {
            case 'error': $messageType = FlashMessage::ERROR; break;
            case 'warning': $messageType = FlashMessage::WARNING; break;
            case 'notice': $messageType = FlashMessage::NOTICE; break;
            case 'info': $messageType = FlashMessage::INFO; break;
            case 'ok': $messageType = FlashMessage::OK; break;
        }
        
        return $messageType;
    }
    
    public static function includeLibTinify($tinifyKey) {
        $extPath = Helper::getExtPath();
        require_once($extPath . 'Resources/Private/PHP/lib/lib_tinify/Tinify/Exception.php');
        require_once($extPath . 'Resources/Private/PHP/lib/lib_tinify/Tinify/ResultMeta.php');
        require_once($extPath . 'Resources/Private/PHP/lib/lib_tinify/Tinify/Result.php');
        require_once($extPath . 'Resources/Private/PHP/lib/lib_tinify/Tinify/Source.php');
        require_once($extPath . 'Resources/Private/PHP/lib/lib_tinify/Tinify/Client.php');
        require_once($extPath . 'Resources/Private/PHP/lib/lib_tinify/Tinify.php');
        \Tinify\setKey($tinifyKey);
    }
    
    public static function url_exists($url) {
        $file_headers = @get_headers($url);
        if(!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
            return false;
        } else {
            return true;
        }
    }
    
    public static function getExtPath() {
        $extManUtility = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::class);
        return $extManUtility::extPath("dj_imagetools");
    }
}

