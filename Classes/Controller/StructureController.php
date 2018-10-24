<?php

namespace DominicJoas\DjImagetools\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

use DominicJoas\DjImagetools\Domain\Repository\FileRepository;
use DominicJoas\DjImagetools\Domain\Model\FileMeta;
use DominicJoas\DjImagetools\Utility\Helper;
use Imagick;

class StructureController extends ActionController {
    private $fileRepository, $base;
    protected $configurationManager;

    public function injectFileRepository(FileRepository $fileRepository) {
        $this->fileRepository = $fileRepository;
    }

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
        parent::injectConfigurationManager($configurationManager);
        $this->configurationManager = $configurationManager;
        $tsSettings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, "imagetools_module1");
        
    }
    
    public function listAction() {
        $metaDatas = array();
        $i = 0;
        foreach($this->listExistingFiles() as $file) {
            $metaData = new FileMeta();
            $metaData->setIdentifier($file->getOriginalResource()->getIdentifier());
            $metaData->setParent(true);
            $metaDatas[$i++] = $metaData;
            
            foreach($this->listExistingFiles() as $comparedFile) {
                
                if($file->getUid()!=$comparedFile->getUid()) {
                    $image1 = new Imagick($this->base . $file->getOriginalResource()->getPublicUrl());
                    $image2 = new Imagick($this->base . $comparedFile->getOriginalResource()->getPublicUrl());
                    $result = $image1->compareImages($image2, Imagick::METRIC_MEANSQUAREERROR);
                    
                    $subMetaData = new FileMeta();
                    $subMetaData->setIdentifier($comparedFile->getOriginalResource()->getIdentifier());
                    $subMetaData->setParent(false);
                    $subMetaData->setTitle($result);
                    $metaDatas[$i++] = $subMetaData;
                }
            }
        }
        
        $this->view->assign('files', $metaDatas);
        return $this->view->render();
    }
    
    private function listExistingFiles() {
        $this->base = str_replace("typo3/", "", $this->request->getBaseUri());
        $files = $this->fileRepository->getAllEntries()->toArray();
        $existingFiles = array();
        $i = 0;
        foreach ($files as $file) {
            if(Helper::url_exists($this->base . $file->getOriginalResource()->getPublicUrl())) {
                $existingFiles[$i] = $file;
                $i++;
            }
        }
        return $existingFiles;
    }
}
