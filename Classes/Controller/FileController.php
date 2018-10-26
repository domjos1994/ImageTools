<?php
namespace DominicJoas\DjImagetools\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

use DominicJoas\DjImagetools\Domain\Repository\FileRepository;
use DominicJoas\DjImagetools\Domain\Model\File;
use DominicJoas\DjImagetools\Utility\Helper;

class FileController extends ActionController {
    protected $settings;
    private $fileRepository;
    protected $configurationManager;

    public function injectFileRepository(FileRepository $fileRepository) {
        $this->fileRepository = $fileRepository;
    }

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
        parent::injectConfigurationManager($configurationManager);
        $this->configurationManager = $configurationManager;
        
        // load user-settings from static template
        $this->settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, "imagetools_module1")['settings'];

        // include tinify-library
        Helper::includeLibTinify($this->settings['tinifyKey']);
    }
    
    public function listAction() {
        if ($this->settings['tinifyKey'] == NULL || $this->settings['tinifyKey'] == "key") {
            $this->addFlashMessage("No Tinify-Key was found in the configuration!", "No Key", Helper::getMessageType("error"), false);
        }

        $files = $this->fileRepository->getContentElementEntries(0)->toArray();
        $base = str_replace("typo3/", "", $this->request->getBaseUri());
        $existingFiles = array();
        $i = 0;
        foreach ($files as $file) {
            if(Helper::url_exists($base . $file->getOriginalResource()->getPublicUrl())) {
                $existingFiles[$i] = $file;
                $i++;
            }
        }
        
        $this->view->assign('path', Helper::getFolIdent());
        $this->view->assign('files', $existingFiles);
        $this->view->assign('width', $this->settings['widthForAll']);
        $this->view->assign('height', $this->settings['heightForAll']);
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
        Helper::saveFile($file, $source, $this->settings, $this->fileRepository);
       
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
                Helper::saveFile($file, $source, $this->settings, $this->fileRepository);
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
    
    private function changeSize(&$file, $all = false) {
        if(($file->getTxDjImagetoolsWidth()==NULL && $file->getTxDjImagetoolsHeight()==NULL) || $all) {
            $file->setTxDjImagetoolsWidth(-1);
            $file->setTxDjImagetoolsHeight(-1);
            if($this->settings['widthForAll']==NULL || $this->settings['widthForAll']==-1) {
                $file->setTxDjImagetoolsHeight(intval($this->settings['heightForAll']));
            } else {
                $file->setTxDjImagetoolsWidth(intval($this->settings['widthForAll']));
            }
        } else if($file->getTxDjImagetoolsWidth()==NULL) {
            $file->setTxDjImagetoolsWidth(-1);
        } else {
          $file->setTxDjImagetoolsHeight(-1);  
        }
    }
}

