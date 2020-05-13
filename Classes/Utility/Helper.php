<?php
namespace DominicJoas\DjImagetools\Utility;

use \TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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

    public static function log($data) {
        echo '<script>';
        echo 'console.log('. json_encode( $data ) .')';
        echo '</script>';
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
            $custFile = $fileRepository->getAllEntries($newFile->getUid())[0];
            $custFile->setTxDjImagetoolsCompressed(1);
            $fileRepository->save($custFile);
        }
        
        $file->setTxDjImagetoolsCompressed(1);
        $fileRepository->save($file);
    }

    public static function getLang($key) {
        return LocalizationUtility::translate("LLL:EXT:dj_imagetools/Resources/Private/Language/locallang_mod.xlf:" . $key, "imagetools_module1", array());
    }

    public static function saveSettings($key, $value) {
        $GLOBALS['BE_USER']->uc['tx_imagetools_module1'][$key] = $value;
        $GLOBALS['BE_USER']->writeUC();
    }

    public static function getSettings($key = "") {
        if($key=="") {
            return $GLOBALS['BE_USER']->uc['tx_imagetools_module1'];
        } else {
            return $GLOBALS['BE_USER']->uc['tx_imagetools_module1'][$key];
        }
    }

    public static function getBase(Request $request) {
        return substr($request->getBaseUri(), 0, strrpos($request->getBaseUri(), "typo3/"));
    }

    public static function addFlashMessageFromLang($type, $key, ActionController $controller) {
        Helper::addFlashMessage($type, Helper::getLang('messages.' . $type . '.' . $key . '.title'), Helper::getLang('messages.' . $type . '.' . $key . '.content'), $controller);
    }

    public static function addFlashMessage($type, $title, $content, ActionController $controller) {
        $controller->addFlashMessage($title, $content, Helper::getMessageType($type));
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

