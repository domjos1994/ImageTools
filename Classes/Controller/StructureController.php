<?php

namespace DominicJoas\Imgcompromizer\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Domain\Repository\FileMountRepository;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

use DominicJoas\Imgcompromizer\Domain\Repository\FileRepository;

class StructureController extends ActionController {
    private $fileMountRepository;
    private $tinifyKey = '';
    private $width, $height;
    private $fileRepository;
    protected $configurationManager;

    public function injectFileRepository(FileRepository $fileRepository) {
        $this->fileRepository = $fileRepository;
    }
    
    public function injectFileMountRepository(FileMountRepository $fileMountRepository) {
        $this->fileMountRepository = $fileMountRepository;
    }

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
        parent::injectConfigurationManager($configurationManager);
        $this->configurationManager = $configurationManager;
        $tsSettings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, "imgcompromizer_module1");

        $this->tinifyKey = $tsSettings['settings']['tinifyKey'];
        $this->width = $tsSettings['settings']['widthForAll'];
        $this->height = $tsSettings['settings']['heightForAll'];

        $extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath("imgcompromizer");
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Exception.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/ResultMeta.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Result.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Source.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Client.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify.php');
    }

    public function listAction() {
        $files = $this->fileRepository->getAllEntries()->toArray();
        var_dump($files[2]->getOriginalResource()->_getMetaData()['title']);
        foreach($files as $file) {
            //var_dump($file->getOriginalResource()->_getMetaData());
        }
        $this->view->assign('files', $this->fileRepository->getFilesAndReferences());
        return $this->view->render();
    }
}