<?php
namespace DominicJoas\DjImagetools\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

use DominicJoas\DjImagetools\Domain\Repository\FileRepository;
use DominicJoas\DjImagetools\Domain\Model\File;
use DominicJoas\DjImagetools\Utility\Helper;

class FileController extends ActionController {
    private $tinifyKey = '';
    private $width, $height;
    //private $overwrite, $sameFolder, $uploadPath;
    private $fileRepository;
    protected $configurationManager;

    public function injectFileRepository(FileRepository $fileRepository) {
        $this->fileRepository = $fileRepository;
    }

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
        parent::injectConfigurationManager($configurationManager);
        $this->configurationManager = $configurationManager;
        
        // load user-settings from static template
        $tsSettings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, "imagetools_module1");
        $this->tinifyKey = $tsSettings['settings']['tinifyKey'];
        $this->width = $tsSettings['settings']['widthForAll'];
        $this->height = $tsSettings['settings']['heightForAll'];
        //$this->overwrite = $tsSettings['settings']['overwrite'];
        //$this->sameFolder = $tsSettings['settings']['sameFolder'];
        //$this->uploadPath = $tsSettings['settings']['uploadPath'];

        // include tinify-library
        Helper::includeLibTinify($this->tinifyKey);
    }
    
    public function listAction() {
        if ($this->tinifyKey == NULL || $this->tinifyKey == "key") {
            $this->addFlashMessage("No Tinify-Key was found in the configuration!", "No Key", Helper::getMessageType("error"), false);
        }

        $files = $this->fileRepository->getContentElementEntries()->toArray();
        $base = str_replace("typo3/", "", $this->request->getBaseUri());
        $existingFiles = array();
        $i = 0;
        foreach ($files as $file) {
            if(Helper::url_exists($base . $file->getOriginalResource()->getPublicUrl())) {
                $existingFiles[$i] = $file;
                $i++;
            }
        }
        
        $this->view->assign('files', $existingFiles);
        $this->view->assign('width', $this->width);
        $this->view->assign('height', $this->height);
        return $this->view->render();
    }
    
    public function updateAction(File $file) {
        $this->changeSize($file);
        
        $height = $file->getTxDjImagetoolsHeight();
        $width = $file->getTxDjImagetoolsWidth();
        $tinifySource = \Tinify\fromBuffer($file->getOriginalResource()->getContents());
        
        $tmp = $this->fileRepository->getContentElementEntries($file->getUid())->toArray();
        $file->setOriginalResource($tmp[0]->getOriginalResource());
        
        $source = $this->setSource($height, $width, $tinifySource);
        $file->getOriginalResource()->setContents($source->toBuffer());
        $file->setTxDjImagetoolsCompressed(1);

        $this->fileRepository->save($file);
       
        $this->redirect("list");
    }
    
    public function updateAllAction() {
        $files = $this->fileRepository->getContentElementEntries()->toArray();
        $base = str_replace("typo3/", "", $this->request->getBaseUri());
        
        foreach($files as $file) {
            if(Helper::url_exists($base . $file->getOriginalResource()->getPublicUrl())) {
                $this->changeSize($file, true);

                $height = $file->getTxDjImagetoolsHeight();
                $width = $file->getTxDjImagetoolsWidth();
                $tinifySource = \Tinify\fromBuffer($file->getOriginalResource()->getContents());

                $tmp = $this->fileRepository->getContentElementEntries($file->getUid())->toArray();
                $file->setOriginalResource($tmp[0]->getOriginalResource());

                $source = $this->setSource($height, $width, $tinifySource);
                $file->getOriginalResource()->setContents($source->toBuffer());
                $file->setTxDjImagetoolsCompressed(1);

                $this->fileRepository->save($file);
            }
        }
        $this->redirect("list");
    }

    public function undoAction() {
        $files = $this->fileRepository->getAllEntries();

        foreach($files as $file) {
            
            $file->setTxDjImagetoolsCompressed(0);
            $file->setTxDjImagetoolsWidth(-1);
            $file->setTxDjImagetoolsHeight(-1);
            $this->fileRepository->save($file);
        }
        
        $this->redirect("list");
    }
    
    private function setSource($height, $width, $source) {
        if ($width != NULL && $width != "0" && $width != "-1") {
            return $source->resize(array("method" => "scale", "width" => intval($width)));
        } else {
            if ($height != NULL && $height != "0" && $height != "-1") {
                return $source->resize(array("method" => "scale", "height" => intval($height)));
            }
        }
        return $source;
    }
    
    private function changeFile($file) {
        /*if(intval($this->overwrite)==0) {
            $content = $file->getOriginalResource()->getIdentifier();
            $file->getOriginalResource()->setIdentifier("tinified_" . $content);
            
        }*/
        return $file->getOriginalResource()->getIdentifier();
    }
    
    private function changeSize(&$file, $all = false) {
        if(($file->getTxDjImagetoolsWidth()==NULL && $file->getTxDjImagetoolsHeight()==NULL) || $all) {
            $file->setTxDjImagetoolsWidth(-1);
            $file->setTxDjImagetoolsHeight(-1);
            if($this->width==NULL || $this->width==-1) {
                $file->setTxDjImagetoolsHeight(intval($this->height));
            } else {
                $file->setTxDjImagetoolsWidth(intval($this->width));
            }
        } else if($file->getTxDjImagetoolsWidth()==NULL) {
            $file->setTxDjImagetoolsWidth(-1);
        } else {
          $file->setTxDjImagetoolsHeight(-1);  
        }
    }
}

