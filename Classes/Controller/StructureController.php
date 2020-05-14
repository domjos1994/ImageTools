<?php

namespace DominicJoas\DjImagetools\Controller;

use ImagickException;
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
        Helper::saveSettings("lastActionMenuItem", "Structure");


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

        if(!extension_loaded("imagick")) {
            Helper::addFlashMessageFromLang('error', 'noIMagick', $this);
        }
        $this->view->assign('files', $comparables);
        $this->view->assign('selected', $selectedfile);
        $this->view->assign('path', Helper::getFolIdent());
        $this->view->assign('typo3Version', explode(".", TYPO3_version)[0]);
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
                $result = $this->compareImages($file1, $file2);
                if(is_array($result)) {
                    return round(100*floatval($result[1]),2);
                } else {
                    Helper::addFlashMessage("error", $result, "Error", $this);
                    return 0;
                }
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    private function compareImages($file1, $file2) {
        try {
            $tmp1 = tempnam(sys_get_temp_dir(), "file1");
            $tmp2 = tempnam(sys_get_temp_dir(), "file2");
            $url1 = $this->base . $file1->getOriginalResource()->getPublicUrl();
            $url2 = $this->base . $file2->getOriginalResource()->getPublicUrl();
            file_put_contents($tmp1, file_get_contents($url1));
            file_put_contents($tmp2, file_get_contents($url2));

            $image1 = new Imagick($tmp1);
            $image2 = new Imagick($tmp2);
            return $image1->compareImages($image2, Imagick::METRIC_MEANABSOLUTEERROR);
        } catch (ImagickException $ex) {
            return $ex->getMessage();
        }
    }

    private function listExistingFiles() {
        $this->base = Helper::getBase($this->request);
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
