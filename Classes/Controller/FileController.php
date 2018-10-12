<?php
namespace DominicJoas\Imgcompromizer\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use DominicJoas\Imgcompromizer\Domain\Repository\FileRepository;
use DominicJoas\Imgcompromizer\Domain\Model\File;

class FileController extends ActionController {
    private $fileRepository;
    protected $configurationManager;

    public function injectFileRepository(FileRepository $fileRepository) {
        $this->fileRepository = $fileRepository;
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
        $extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath("imgcompromizer");

        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Exception.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/ResultMeta.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Result.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Source.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Client.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify.php');
        
        $absoluteFile = $file->getOriginalResource()->getContents();

        \Tinify\setKey("zGyQL3uZYNNQFgx5rqugyaOkZmlbrOFI");
        $source = \Tinify\fromBuffer($absoluteFile);

        $file->getOriginalResource()->setContents($source->toBuffer());
        $file->setTxImgcompromizerCompressed(1);
        $this->fileRepository->save($file);
        
        $this->redirect("list");
    }

    public function updateAction() {
        
    }

    public function deleteAction() {
        
    }

}

