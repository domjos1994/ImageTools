<?php
namespace DominicJoas\DjImagetools\Utility;

use DominicJoas\DjImagetools\Controller\SettingsController;
use Exception;
use \TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageRendererResolver;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class Helper {
    public const EXTENSIONS = array('png', 'jpg', 'JPG', 'PNG', 'jpeg');

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

    public static function alert($data) {
        echo '<script>';
        echo 'alert('. json_encode( $data ) .')';
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
            $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
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

    public static function addFlashMessage($type, $title, $content = null, ActionController $controller = null) {
        if(is_null($content)) {
            $key = $title;
            $title = Helper::getLang('messages.' . $type . '.' . $key . '.title');
            $content = Helper::getLang('messages.' . $type . '.' . $key . '.content');
        }

        if(is_null($controller)) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $flashMessageService = $objectManager->get(FlashMessageService::class);
            $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
            $messageQueue->addMessage(new FlashMessage($content, $title, Helper::getMessageType($type)));
        } else {
            $controller->addFlashMessage($content, $title, Helper::getMessageType($type));
        }
    }

    /**
     * 
     * @param ResourceStorage $storage
     * @param string $identifier
     * @param array $settings
     * @return string
     */
    private static function getIdentifier($storage, $identifier, $settings) {
        if($settings[SettingsController::SAME_FOLDER]=="0") {
            try {
                $upload = $settings[SettingsController::UPLOAD_PATH];
                if(!$storage->hasFolder($upload)) {
                    $upload_identifier = $storage->createFolder($settings[SettingsController::UPLOAD_PATH])->getIdentifier();
                } else {
                    $upload_identifier = $storage->getFolder($settings[SettingsController::UPLOAD_PATH])->getIdentifier();
                }
                return $upload_identifier;
            } catch (Exception $ex) {
                return $storage->getFolder($settings[SettingsController::UPLOAD_PATH] . $identifier)->getIdentifier();
            }
        }
        return $identifier;
    }
}

