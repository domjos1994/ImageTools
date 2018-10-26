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
    
    public static function getFolIdent() {
        return str_replace("1:", "", $GLOBALS["_GET"]["id"]);
    }
    
    public static function saveFile($file, $source, $settings, $fileRepository) {
        if($settings['overwrite']) {
           $file->getOriginalResource()->setContents($source->toBuffer());
        } else {
            $resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
            $storage = $resourceFactory->getDefaultStorage();
            $name = $file->getOriginalResource()->getName();
            $newName = "tinify.". $name;
            
            $identifier = str_replace($name, "", $file->getOriginalResource()->getIdentifier());
            $temp = tempnam(sys_get_temp_dir(), 'tinify');
            file_put_contents($temp, $source->toBuffer());
            
            
            $newFile = $storage->addFile($temp, $storage->getFolder(Helper::getIdentifier($storage, $identifier, $settings)), $newName);
            $custFile = $this->fileRepository->getAllEntries($newFile->getUid())->toArray()[0];
            $custFile->setTxDjImagetoolsCompressed(1);
            $fileRepository->save($custFile);
        }
        
        $file->setTxDjImagetoolsCompressed(1);
        $fileRepository->save($file);
    }
    
    /**
     * 
     * @param type $storage
     * @param type $identifier
     * @param type $settings
     * @return type
     */
    private static function getIdentifier($storage, $identifier, $settings) {
        if(!$settings["sameFolder"]) {
            try {
                if(!$storage->getFolder($identifier)) {
                    return $storage->createFolder($settings["uploadPath"] . $identifier)->getIdentifier();
                }
            } catch (Exception $ex) {
                return $storage->getFolder($settings["uploadPath"] . $identifier)->getIdentifier();
            }
        }
        return $identifier;
    }
}

