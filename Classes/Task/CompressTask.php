<?php
namespace DominicJoas\Imgcompromizer\Task;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class CompressTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
    private $objectManager;
    private $array;


    public function execute() {
        try {
            $this->scheduler->log("Anfang", 0, 0);
            $this->objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
            $this->initTinify();

            $fileRepository = $this->objectManager->get(\DominicJoas\Imgcompromizer\Domain\Repository\FileRepository::class);
            $persistenceManager = $this->objectManager->get("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");
            foreach($fileRepository->getAllEntries() as $file) {
                $file->setTxImgcompromizerWidth($this->array['widthForAll']);
                $file->setTxImgcompromizerHeight($this->array['heightForAll']);
                $this->updateFile($file, $persistenceManager);
            }
            
            
            $persistenceManager->persistAll();
        } catch (Exception $ex) {
            $this->scheduler->log("Fehler", 0, 0);
            return false;
        }
        
        return true;
    }
    
    private function initTinify() {
        $configurationManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class);
        $this->array = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, "imgcompromizer_module1")['settings'];
        
        $extManUtility = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::class);
        $extPath = $extManUtility::extPath("imgcompromizer");
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Exception.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/ResultMeta.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Result.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Source.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify/Client.php');
        require_once($extPath . 'Resources/Private/PHP/lib/Tinify.php');
        \Tinify\setKey($this->array['tinifyKey']);
    }
    
    private function updateFile($file, $persistenceManager) {
        $absoluteFile = $file->getOriginalResource()->getContents();

        $source = \Tinify\fromBuffer($absoluteFile);
        
        $file->getOriginalResource()->setContents($source->toBuffer());
        $file->setTxImgcompromizerCompressed(1);
        $this->scheduler->log("Speichern", 0, 0);
        $persistenceManager->update($file);
    }
}

