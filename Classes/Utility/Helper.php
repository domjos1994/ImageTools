<?php
namespace DominicJoas\DjImagetools\Utility;

use \TYPO3\CMS\Core\Messaging\FlashMessage;

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
}

