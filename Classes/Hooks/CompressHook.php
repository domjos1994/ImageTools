<?php

namespace DominicJoas\DjImagetools\Hooks;

use DominicJoas\DjImagetools\Controller\SettingsController;
use DominicJoas\DjImagetools\Domain\Repository\FileRepository;
use DominicJoas\DjImagetools\Utility\Helper;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\File\ExtendedFileUtility;
use TYPO3\CMS\Core\Utility\File\ExtendedFileUtilityProcessDataHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use function Tinify\fromBuffer;

class CompressHook implements ExtendedFileUtilityProcessDataHookInterface{

    /**
     * @var FileRepository
     */
    private $fileRepository;

    public function processData_postProcessAction($action, array $cmdArr, array $result, ExtendedFileUtility $parentObject) {
        $objManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->fileRepository = $objManager->get(FileRepository::class);

        if(Helper::getSettings(SettingsController::COMPRESS_ON_UPLOAD) == "1") {
            if($action == "upload") {
                Helper::includeLibTinify(Helper::getSettings('tinifyKey'));

                foreach ($result as $resultItem) {
                    $resultFile = $resultItem[0];
                    if(in_array($resultFile->getExtension(), Helper::EXTENSIONS)) {
                        $file = $this->fileRepository->findByUid($resultFile->getUid());
                        $tinifySource = fromBuffer($file->getOriginalResource()->getContents());
                        $array = ["overwrite" => true];
                        Helper::saveFile($file, $tinifySource, $array, $this->fileRepository);
                        $this->printFlashMessage($objManager, "compressHook");
                    }
                }
            }
        }
    }

    private function printFlashMessage(ObjectManager $objectManager, $key) {
        $title = Helper::getLang("messages.info.$key.title");
        $content = Helper::getLang("messages.info.$key.content");
        $message = GeneralUtility::makeInstance(FlashMessage::class, $title, $content, FlashMessage::OK, true);
        $flashMessageService = $objectManager->get(FlashMessageService::class);
        $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $messageQueue->addMessage($message);

    }
}