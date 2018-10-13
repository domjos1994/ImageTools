<?php
namespace DominicJoas\Imgcompromizer\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

use DominicJoas\Imgcompromizer\Domain\Repository\FileRepository;
use DominicJoas\Imgcompromizer\Domain\Model\File;

class FileController extends ActionController {
    private $tinifyKey = '';
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
        
        $extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath("imgcompromizer");
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Exception.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/ResultMeta.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Result.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Source.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Client.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify.php');
    }
    
    public function listAction() {
        $uid = $this->configurationManager->getContentObject()->data['uid'];
        $files = $this->fileRepository->getContentElementEntries();
        
        $this->view->assign('files', $files->toArray());
        $this->view->assign('uid', $uid);
        return $this->view->render();
    }
    
    public function editAction(File $file) {
        $file->setOriginalResource($this->fileRepository->getContentElementEntries($file->getUid())->toArray()[0]->getOriginalResource());
        
        $absoluteFile = $file->getOriginalResource()->getContents();

        \Tinify\setKey($this->tinifyKey);
        $source = \Tinify\fromBuffer($absoluteFile);
        
        if($file->getTxImgcompromizerWidth()!=0 && $file->getTxImgcompromizerWidth()!=-1) {
            $source = $source->resize(array("method" => "scale","width" => $file->getTxImgcompromizerWidth()));
        } else {
            if($file->getTxImgcompromizerHeight()!=0 && $file->getTxImgcompromizerHeight()!=-1) {
                $source = $source->resize(array("method" => "scale","height" => $file->getTxImgcompromizerHeight()));
            }
        }
        
        $file->getOriginalResource()->setContents($source->toBuffer());
        $file->setTxImgcompromizerCompressed(1);
        $this->fileRepository->save($file);
        
        $this->redirect("list");
    }
    
    public function updateAction() {
        
    }
}

