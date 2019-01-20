<?php

namespace DominicJoas\DjImagetools\Controller;

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

use DominicJoas\DjImagetools\Domain\Repository\FileRepository;
use DominicJoas\DjImagetools\Domain\Model\ComparableFile;
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
    
    public function listAction(ComparableFile $selectedfile = NULL) {

        $i = 0;
        foreach($this->listExistingFiles() as $file) {
            $comparableFile = new ComparableFile();
            $comparableFile->setUid($file->getUid());
            $comparableFile->setIdentifier($file->getOriginalResource()->getIdentifier());
            
            $j = 0;
            foreach($this->listExistingFiles() as $comparedFile) {
                if($file->getUid()!=$comparedFile->getUid()) {
                    $images[$j][0] = $comparedFile->getUid();
                    $images[$j][1] = $comparedFile->getOriginalResource()->getIdentifier();
                    $images[$j++][2] = $this->compare($selectedfile, $file, $comparedFile);
                }
                
            }
            $comparableFile->setComparableFiles($images);
            $comparables[$i++] = $comparableFile;
        }
        
        $this->view->assign("imagick", extension_loaded("imagick"));
        $this->view->assign('files', $comparables);
        $this->view->assign('selected', $selectedfile);
        $this->view->assign('path', Helper::getFolIdent());
        return $this->view->render();
    }

    /**
     * @param int $uid
     * @param int $parent
     * @param string $identifier
     * @throws
     */
    public function deleteAction($uid, $parent, $identifier) {
        $comparableFile = new ComparableFile();
        $comparableFile->setUid($parent);
        $comparableFile->setIdentifier($identifier);
        $this->fileRepository->updateReference($uid, $parent);

        $this->redirect('list');
    }

    private function compare($selectedfile, $file1, $file2) {
        if($selectedfile!=NULL) {
            if($selectedfile->getUid()==$file1->getUid()) {
                return round(100*floatval($this->compareImages($file1, $file2)[1]),2);
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    private function compareImages($file1, $file2) {
        try {
            $image1 = new Imagick($this->base . $file1->getOriginalResource()->getPublicUrl());
            $image2 = new Imagick($this->base . $file2->getOriginalResource()->getPublicUrl());
            $result = $image1->compareImages($image2, Imagick::METRIC_MEANABSOLUTEERROR);

            return $result;
        } catch (ImagickException $ex) {
            return $ex->getMessage();
        }
    }

    private function listExistingFiles() {
        $this->base = str_replace("typo3/", "", $this->request->getBaseUri());
        $files = $this->fileRepository->getAllEntries();
        $existingFiles = array();
        $i = 0;
        foreach ($files as $file) {
            if(Helper::url_exists($this->base . $file->getOriginalResource()->getPublicUrl())) {
                $existingFiles[$i++] = $file;
            }
        }
        return $existingFiles;
    }
}
