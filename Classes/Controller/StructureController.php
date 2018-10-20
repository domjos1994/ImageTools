<?php

namespace DominicJoas\Imgcompromizer\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

use DominicJoas\Imgcompromizer\Domain\Model\FileMeta;
use DominicJoas\Imgcompromizer\Domain\Repository\FileRepository;
use DominicJoas\Imgcompromizer\Utility\Helper;

class StructureController extends ActionController {
    private $fileRepository;
    protected $configurationManager;

    public function injectFileRepository(FileRepository $fileRepository) {
        $this->fileRepository = $fileRepository;
    }

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
        parent::injectConfigurationManager($configurationManager);
        $this->configurationManager = $configurationManager;
        $tsSettings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, "imgcompromizer_module1");
    }

    public function listAction() {
        $files = $this->fileRepository->getFilesAndReferences();
        $this->view->assign('files', $files);
        return $this->view->render();
    }
    
    /**
     * 
     * @param FileMeta $file
     */
    public function updateAction(FileMeta $file) {
        $this->fileRepository->saveMeta($file);
        $this->addFlashMessage("Data saved successfully!", "Success", Helper::getMessageType("ok"), false);
        $this->redirect("list");
    }
}