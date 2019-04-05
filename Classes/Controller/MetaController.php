<?php

namespace DominicJoas\DjImagetools\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

use DominicJoas\DjImagetools\Domain\Model\FileMeta;
use DominicJoas\DjImagetools\Domain\Repository\FileRepository;
use DominicJoas\DjImagetools\Utility\Helper;

class MetaController extends ActionController {
    private $fileRepository;
    protected $configurationManager;

    public function injectFileRepository(FileRepository $fileRepository) {
        $this->fileRepository = $fileRepository;
    }

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
        parent::injectConfigurationManager($configurationManager);
        $this->configurationManager = $configurationManager;
    }

    public function listAction() {
        Helper::saveSettings("lastActionMenuItem", "Meta");

        $files = $this->fileRepository->getFilesAndReferences($this->request);
        $this->view->assign('files', $files);
        $this->view->assign('path', Helper::getFolIdent());
        return $this->view->render();
    }
    
    /**
     * 
     * @param FileMeta $file
     * @throws
     */
    public function updateAction(FileMeta $file) {
        $this->fileRepository->saveMeta($file);
        $this->addFlashMessage("Data saved successfully!", "Success", Helper::getMessageType("ok"), false);
        $this->redirect("list");
    }
}