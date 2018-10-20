<?php
namespace DominicJoas\Imgcompromizer\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

use DominicJoas\Imgcompromizer\Domain\Repository\FileRepository;
use DominicJoas\Imgcompromizer\Domain\Model\File;
use DominicJoas\Imgcompromizer\Utility\Helper;

class FileController extends ActionController {
    private $tinifyKey = '';
    private $width, $height;
    private $overwrite, $sameFolder, $uploadPath;
    private $fileRepository;
    protected $configurationManager;

    public function injectFileRepository(FileRepository $fileRepository) {
        $this->fileRepository = $fileRepository;
    }

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
        parent::injectConfigurationManager($configurationManager);
        $this->configurationManager = $configurationManager;
        $tsSettings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, "imgcompromizer_module1");

        $this->tinifyKey = $tsSettings['settings']['tinifyKey'];
        $this->width = $tsSettings['settings']['widthForAll'];
        $this->height = $tsSettings['settings']['heightForAll'];
        $this->overwrite = $tsSettings['settings']['overwrite'];
        $this->sameFolder = $tsSettings['settings']['sameFolder'];
        $this->uploadPath = $tsSettings['settings']['uploadPath'];

        $extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath("imgcompromizer");
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Exception.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/ResultMeta.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Result.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Source.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Client.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify.php');
        
        
        
    }
    
    public function listAction() {
        if ($this->tinifyKey == NULL || $this->tinifyKey == "key") {
            $this->addFlashMessage("No Tinify-Key was found in the configuration!", "No Key", Helper::getMessageType("error"), false);
        }

        $uid = $this->configurationManager->getContentObject()->data['uid'];
        $files = $this->fileRepository->getContentElementEntries();
        
        $this->view->assign('files', $files->toArray());
        $this->view->assign('uid', $uid);
        $this->view->assign('width', $this->width);
        $this->view->assign('height', $this->height);
        return $this->view->render();
    }
    
    public function updateAction(File $file) {
        $file->setOriginalResource($this->fileRepository->getContentElementEntries($file->getUid())->toArray()[0]->getOriginalResource());

        \Tinify\setKey($this->tinifyKey);
        var_dump($file);
        $source = $this->setSource($file->getTxImgcompromizerHeight(), $file->getTxImgcompromizerWidth(), \Tinify\fromBuffer($file->getOriginalResource()->getContents()));
        
        $file->getOriginalResource()->setContents($source->toBuffer());
        $file->setTxImgcompromizerCompressed(1);
        var_dump(intval($this->sameFolder));
        
        $this->fileRepository->save($file);
        
        $this->redirect("list");
    }
    
    public function updateAllAction() {
        \Tinify\setKey($this->tinifyKey);
        $files = $this->fileRepository->getContentElementEntries();
        
        foreach($files as $file) {
            $source = $this->setSource($this->height, $this->width, \Tinify\fromBuffer($file->getOriginalResource()->getContents()));
                
            $file->getOriginalResource()->setContents($source->toBuffer());
            $file->setTxImgcompromizerCompressed(1);
            $this->fileRepository->save($file);
        }
        
        $this->redirect("list");
    }
    
    public function undoAction() {
        $files = $this->fileRepository->getAllEntries();
        
        foreach($files as $file) {
            $file->setTxImgcompromizerCompressed(0);
            $file->setTxImgcompromizerWidth(-1);
            $file->setTxImgcompromizerHeight(-1);
            $this->fileRepository->save($file);
        }
        
        $this->redirect("list");
    }
    
    private function setSource($height, $width, $source) {
        if ($width != "0" && $width != "-1") {
            return $source->resize(array("method" => "scale", "width" => intval($width)));
        } else {
            if ($height != "0" && $height != "-1") {
                return $source->resize(array("method" => "scale", "height" => intval($height)));
            }
        }
        return $source;
    }
}

