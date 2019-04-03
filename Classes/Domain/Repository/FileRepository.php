<?php

namespace DominicJoas\DjImagetools\Domain\Repository;

use DominicJoas\DjImagetools\Domain\Model\FileMeta;
use DominicJoas\DjImagetools\Utility\Helper;
use DominicJoas\DjImagetools\Domain\Model\File;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

class FileRepository extends Repository {
    
    public function getAllEntries($uid = 0, $unCompressed = false) {
        $extensions = array('png', 'jpg', 'JPG', 'PNG', 'jpeg');
        $folderIdentifier = $GLOBALS["_GET"]["id"];
        $files = array();
        $counter = 0;

        try {
            if ($uid != 0) {
                $files[0] = $this->findByUid($uid);
            } else {
                $resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();

                if ($folderIdentifier == null) {
                    $folderIdentifier = $resourceFactory->getDefaultStorage()->getFolder('/')->getCombinedIdentifier();
                }
                $tmpFiles = $resourceFactory->getFolderObjectFromCombinedIdentifier($folderIdentifier)->getFiles();

                foreach ($tmpFiles as $tmp) {
                    if (in_array($tmp->getExtension(), $extensions)) {
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
        } catch (InsufficientFolderAccessPermissionsException $e) {
            return $e;
        } catch (\Exception $e) {
            return $e;
        }
        return $files;
    }

    public function updateReference($fileUid, $parentUid, $referenceUid = 0) {
        $fileRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\FileRepository::class);
        $references = $this->getFileReferences($fileUid);

        $somethingWentWrong = false;
        foreach($references as $reference) {
            if ($referenceUid !== 0) {
                if ($referenceUid !== $reference->getUid()) {
                    continue;
                }
            }
            $foundFileReference = $fileRepository->findFileReferenceByUid($reference->getUid());
            $foreign_id = $foundFileReference->getProperty('uid_foreign');
            if (!$this->addFileReference($parentUid, $foreign_id)) {
                $somethingWentWrong = true;
                break;
            }
        }

        if(!$somethingWentWrong) {
            $resourceFactory = ResourceFactory::getInstance();
            $storage = $resourceFactory->getDefaultStorage();
            $repo = new \TYPO3\CMS\Core\Resource\FileRepository();
            $file = $repo->findByUid($fileUid);
            $storage->deleteFile($file);
        }
    }

    private function addFileReference($file, $content) {
        $resourceFactory = ResourceFactory::getInstance();
        try {
            $fileObject = $resourceFactory->getFileObject((int)$file);
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

            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
            $dataHandler->start($data, array());
            $dataHandler->process_datamap();
            return count($dataHandler->errorLog) === 0;
        } catch (FileDoesNotExistException $e) {
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
        $result = $query->execute();
        return $result;
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
        $objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        $metadata = $objectManager->get('TYPO3\CMS\Core\Resource\Index\MetaDataRepository');

        if($fileMeta->getParent()) {
            $files = $this->getAllEntries($fileMeta->getUid());
            if($files[0]!=null) {
                $file = $files[0];
                $array = $file->getOriginalResource()->_getMetaData();
                $array['title'] = $fileMeta->getTitle();
                $array['alternative'] = $fileMeta->getAlternative();
                $array['description'] = $fileMeta->getDescription();
                $metadata->update($fileMeta->getUid(), $array);
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

            $obj = $this->getFileReferences($file->getUid());
            if($obj!=null) {
                foreach($obj->toArray() as $referencedFile) {
                    $this->addReferenceFileMetaIfExists($array, $i, $referencedFile, $parentUid, $parentParams, $identifier);
                }
            }
        }
    }

    private function addReferenceFileMetaIfExists(&$array, &$i, $reference, $parentUid, $parentParams, $identifier) {
        $repo = new \TYPO3\CMS\Core\Resource\FileRepository();

        $fileMeta = $this->createFileMeta($reference, false, $this->setParams("title", $repo, $reference->getUid()), $this->setParams("alternative", $repo, $reference->getUid()), $this->setParams("description", $repo, $reference->getUid()), $identifier);
        $fileMeta->setParentData($parentParams);
        $fileMeta->setParentUid($parentUid);

        $array[$i++] = $fileMeta;
    }

    private function createFileMeta(File $file, $parent, $title, $alternative, $description, $identifier) {
        $fileMeta = new FileMeta();
        $fileMeta->setUid($file->getUid());
        $fileMeta->setIdentifier($identifier);
        $fileMeta->setParent($parent);
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

    private function setParentParam(File $file, $descr) {
        if($file->getOriginalResource()->_getMetaData()[$descr]!=null) {
            return $file->getOriginalResource()->_getMetaData()[$descr];
        } else {
            return "";
        }
    }
    
    private function setParams($descr, \TYPO3\CMS\Core\Resource\FileRepository $repo, $parentUid) {
        $obj = $repo->findFileReferenceByUid($parentUid);
        if($obj!=null) {
            $properties = $obj->getProperties();
            if ($properties[$descr] == "") {
                return "";
            } else {
                return $properties[$descr];
            }
        }
        return null;
    }
}
