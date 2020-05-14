<?php

namespace DominicJoas\DjImagetools\Domain\Repository;

use DominicJoas\DjImagetools\Domain\Model\FileMeta;
use DominicJoas\DjImagetools\Utility\Helper;
use DominicJoas\DjImagetools\Domain\Model\File;

use Exception;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Resource\Index\MetaDataRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

class FileRepository extends Repository {
    /**
     * @var ResourceFactory
     */
    private $resourceFactory;

    /**
     * @var \TYPO3\CMS\Core\Resource\FileRepository
     */
    private $fileRepository;

    /**
     * @var MetaDataRepository
     */
    private $metadataRepository;

    /**
     * @var DataHandler
     */
    private $dataHandler;

    public function getAllEntries($uid = 0, $unCompressed = false) {

        $folderIdentifier = $GLOBALS["_GET"]["id"];
        $files = array();
        $counter = 0;

        try {
            if ($uid != 0) {
                $files[0] = $this->findByUid($uid);
            } else {
                $this->initResourceFactory();

                if ($folderIdentifier == null) {
                    $folderIdentifier = $this->resourceFactory->getDefaultStorage()->getFolder('/')->getCombinedIdentifier();
                }
                $tmpFiles = $this->resourceFactory->getFolderObjectFromCombinedIdentifier($folderIdentifier)->getFiles();

                foreach ($tmpFiles as $tmp) {
                    if (in_array($tmp->getExtension(), Helper::EXTENSIONS)) {
                        if ($unCompressed) {
                            $tmpFile = $this->findByUid($tmp->getUid());
                            if ($tmpFile->getTxDjImagetoolsCompressed() != 1) {
                                $files[$counter++] = $tmpFile;
                            }
                        } else {
                            $files[$counter++] = $this->findByUid($tmp->getUid());
                        }
                    }
                }
            }
        } catch (Exception $e) {
            return $e;
        }
        return $files;
    }

    public function findByUid($uid) {
        $query = $this->createQuery();
        $query->statement("SELECT * FROM sys_file WHERE uid=$uid");
        return $query->execute()->toArray()[0];
    }

    public function updateReference($fileUid, $parentUid, $referenceUid = 0) {
        $this->initFileRepository();
        $references = $this->getFileReferences($fileUid);

        $somethingWentWrong = false;
        foreach($references as $reference) {
            if ($referenceUid !== 0) {
                if ($referenceUid !== $reference->getUid()) {
                    continue;
                }
            }
            $foundFileReference = $this->fileRepository->findFileReferenceByUid($reference->getUid());
            $foreign_id = $foundFileReference->getProperty('uid_foreign');
            if (!$this->addFileReference($parentUid, $foreign_id)) {
                $somethingWentWrong = true;
                break;
            }
        }

        if(!$somethingWentWrong) {
            $this->initResourceFactory();
            $storage = $this->resourceFactory->getDefaultStorage();
            $file = $this->fileRepository->findFileReferenceByUid($fileUid);
            try {
                $storage->deleteFile($file);
            } catch (Exception $e) {}
        }
    }

    private function addFileReference($file, $content) {
        $this->initResourceFactory();
        try {
            $fileObject = $this->resourceFactory->getFileObject((int)$file);
            $contentElement = BackendUtility::getRecord('tt_content', (int)$content);
            $newId = 'NEW1234';
            $data = array();
            $data['sys_file_reference'][$newId] = array(
                'table_local' => 'sys_file',
                'uid_local' => $fileObject->getUid(),
                'tablenames' => 'tt_content',
                'uid_foreign' => $contentElement['uid'],
                'fieldname' => 'image',
                'pid' => $contentElement['pid']
            );
            $data['tt_content'][$contentElement['uid']] = array(
                'image' => $newId
            );

            $this->initDataHandler();
            $this->dataHandler->start($data, array());
            $this->dataHandler->process_datamap();
            return count($this->dataHandler->errorLog) === 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function delete($uid) {
        foreach($this->getAllEntries($uid) as $file) {
            try {
                $this->remove($file);
            } catch (IllegalObjectTypeException $e) {
                continue;
            }
        }
    }

    public function save($obj) {
        try {
            $this->update($obj);
            $this->persistenceManager->persistAll();
        } catch (IllegalObjectTypeException $e) {
            return $e;
        } catch (UnknownObjectException $e) {
            return $e;
        }
        return null;
    }
    
    public function getFileReferences($fileUid=0) {
        $query = $this->createQuery();
        if($fileUid==0) {
            $query->statement("SELECT * FROM sys_file_reference WHERE deleted=0");
        } else {
            $query->statement("SELECT * FROM sys_file_reference WHERE deleted=0 AND uid_local=$fileUid");
        }
        return $query->execute();
    }
    
    public function getFilesAndReferences(Request $request) {
        $tmp = array();

        $files = $this->getAllEntries();
        $i = 0;
        foreach($files as $file) {
            $this->addFileMetaIfExists($tmp, $i, $file, $request);
        }
        return $tmp;
    }
    
    public function saveMeta(FileMeta $fileMeta) {
        $this->initMetadataRepository();

        if($fileMeta->getParent()) {
            $files = $this->getAllEntries($fileMeta->getUid());
            if($files[0]!=null) {
                $file = $files[0];
                $array = $file->getOriginalResource()->_getMetaData();
                $array['title'] = $fileMeta->getTitle();
                $array['alternative'] = $fileMeta->getAlternative();
                $array['description'] = $fileMeta->getDescription();
                $this->metadataRepository->update($fileMeta->getUid(), $array);
            }

            if($fileMeta->getChildren()) {
                foreach ($this->getFileReferences($fileMeta->getUid())->toArray() as $referencedFile) {
                    $fileMeta->setUid($referencedFile->getUid());
                    $this->execQuery($fileMeta);
                }
            }
        } else {
            foreach ($this->getFileReferences($fileMeta->getParentUid())->toArray() as $referencedFile) {
                if($referencedFile->getUid()==$fileMeta->getUid()) {
                    $this->execQuery($fileMeta);
                }
            }
        }
    }

    
    
    private function addFileMetaIfExists(&$array, &$i, File $file, Request $request) {
        $base = substr($request->getBaseUri(), 0, strrpos($request->getBaseUri(), "typo3/"));
        if(Helper::url_exists($base . $file->getOriginalResource()->getPublicUrl())) {
            $parentParams = array();
            $parentUid = $file->getUid();
            $identifier = $file->getOriginalResource()->getIdentifier();
            $parentParams[0] = $this->setParentParam($file, "title");
            $parentParams[1] = $this->setParentParam($file, "alternative");
            $parentParams[2] = $this->setParentParam($file, "description");
            $array[$i++] = $this->createFileMeta($file, true, $parentParams[0], $parentParams[1], $parentParams[2], $identifier);

            try {
                $repo = new \TYPO3\CMS\Core\Resource\FileRepository();
                $obj = $repo->findFileReferenceByUid($file->getOriginalResource()->getUid());
                if($obj!=null) {
                    $this->addReferenceFileMetaIfExists($array, $i, $obj, $parentUid, $parentParams, $identifier);
                }
            } catch (Exception $ex) {}

        }
    }

    private function addReferenceFileMetaIfExists(&$array, &$i, $reference, $parentUid, $parentParams, $identifier) {
        $repo = new \TYPO3\CMS\Core\Resource\FileRepository();
        $fileMeta = $this->createFileMeta($reference, false, $this->setParams("title", $repo, $reference->getUid()), $this->setParams("alternative", $repo, $reference->getUid()), $this->setParams("description", $repo, $reference->getUid()), $identifier);
        $fileMeta->setParentData($parentParams);
        $fileMeta->setParentUid($parentUid);

        $array[$i++] = $fileMeta;
    }

    private function createFileMeta($file, $parent, $title, $alternative, $description, $identifier) {
        $fileMeta = new FileMeta();
        $fileMeta->setUid($file->getUid());
        $fileMeta->setIdentifier($identifier);
        $fileMeta->setParent($parent);
        $fileMeta->setForeignUid($file);
        $fileMeta->setTitle($title);
        $fileMeta->setAlternative($alternative);
        $fileMeta->setDescription($description);
        return $fileMeta;
    }

    private function execQuery(FileMeta $fileMeta) {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->update('sys_file_reference')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($fileMeta->getUid()))
            )
            ->set('title', $fileMeta->getTitle())
            ->set('description', $fileMeta->getDescription())
            ->set('alternative', $fileMeta->getAlternative())
            ->execute();
    }

    private function setParentParam(File $file, $description) {
        if (method_exists($file->getOriginalResource(), "getMetaData")) {
            $array = $file->getOriginalResource()->getMetaData()->get();
            if ($array[$description] != null) {
                return $array[$description];
            } else {
                return "";
            }
        } else {
            $array = $file->getOriginalResource()->_getMetaData();
            if ($array[$description] != null) {
                return $array[$description];
            } else {
                return "";
            }
        }
    }
    
    private function setParams($description, \TYPO3\CMS\Core\Resource\FileRepository $repo, $parentUid) {
        $obj = $repo->findFileReferenceByUid($parentUid);
        if($obj!=null) {
            $properties = $obj->getProperties();
            if ($properties[$description] == "") {
                return "";
            } else {
                return $properties[$description];
            }
        }
        return null;
    }

    private function initResourceFactory() {
        if(!isset($this->resourceFactory)) {
            $this->resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
        } else {
            if(is_null($this->resourceFactory)) {
                $this->resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
            }
        }
    }

    private function initFileRepository() {
        if(!isset($this->fileRepository)) {
            $this->fileRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\FileRepository::class);
        } else {
            if(is_null($this->fileRepository)) {
                $this->fileRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\FileRepository::class);
            }
        }
    }

    private function initMetadataRepository() {
        if(!isset($this->metadataRepository)) {
            $this->metadataRepository = GeneralUtility::makeInstance(MetaDataRepository::class);
        } else {
            if(is_null($this->metadataRepository)) {
                $this->metadataRepository = GeneralUtility::makeInstance(MetaDataRepository::class);
            }
        }
    }

    private function initDataHandler() {
        if(!isset($this->dataHandler)) {
            $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        } else {
            if(is_null($this->dataHandler)) {
                $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
            }
        }
    }
}
