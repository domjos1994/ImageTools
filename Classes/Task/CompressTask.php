<?php
namespace DominicJoas\DjImagetools\Task;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use DominicJoas\DjImagetools\Utility\Helper;

class CompressTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask {
    private $objectManager;
    private $array;


    public function execute() {
        try {
            $this->scheduler->log("Anfang", 0, 0);
            $this->objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
            $this->initTinify();

            $fileRepository = $this->objectManager->get(\DominicJoas\DjImagetools\Domain\Repository\FileRepository::class);
            $persistenceManager = $this->objectManager->get("TYPO3\\CMS\\Extbase\\Persistence\\Generic\\PersistenceManager");
            foreach($fileRepository->getAllEntries() as $file) {
                $file->setTxDjImagetoolsWidth($this->array['widthForAll']);
                $file->setTxDjImagetoolsHeight($this->array['heightForAll']);
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
        $this->array = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, "imagetools_module1")['settings'];
        
        Helper::includeLibTinify($this->array['tinifyKey']);
    }
    
    private function updateFile($file, $persistenceManager) {
        $absoluteFile = $file->getOriginalResource()->getContents();

        $source = \Tinify\fromBuffer($absoluteFile);
        
        $file->getOriginalResource()->setContents($source->toBuffer());
        $file->setTxDjImagetoolsCompressed(1);
        $this->scheduler->log("Speichern", 0, 0);
        $persistenceManager->update($file);
    }
}

