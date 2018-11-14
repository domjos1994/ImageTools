<?php

namespace DominicJoas\DjImagetools\Domain\Repository;

use DominicJoas\DjImagetools\Domain\Model\FileMeta;
use DominicJoas\DjImagetools\Utility\Helper;

use Tinify\Exception;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

class FileRepository extends Repository {
    
    public function getAllEntries($uid = 0, $unCompressed = false) {
        $extensions = array('png', 'jpg', 'JPG', 'PNG', 'jpeg');
        $folderIdentifier = $GLOBALS["_GET"]["id"];
        $files = array();
        $counter = 0;

        if ($uid != 0) {
            $files[0] = $this->findByUid($uid);
        } else {
            $resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
            $tmpFiles = $resourceFactory->getFolderObjectFromCombinedIdentifier($folderIdentifier)->getFiles();

            foreach ($tmpFiles as $tmp) {
                if(in_array($tmp->getExtension(), $extensions)) {
                    if($unCompressed) {
                        $tmpFile = $this->findByUid($tmp->getUid());
                        if($tmpFile->getTxDjImagetoolsCompressed()!=1) {
                            $files[$counter++] = $tmpFile;
                        }
                    }
                }
            }
        }
        return $files;
    }

    public function delete($uid) {
        foreach($this->getAllEntries($uid) as $file) {
            $this->remove($file);
        }
    }

    public function save($obj) {
        $this->update($obj);
        $this->persistenceManager->persistAll();
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
    
    public function getFilesAndReferences($request) {
        $tmp = array();

        $files = $this->getAllEntries();
        $i = 0;
        foreach($files as $file) {            
            $this->addFileMetaIfExists($tmp, $i, $file, $request);
        }
        return $tmp;
    }
    
    public function saveMeta(FileMeta $fileMeta) {
        $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
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

    
    
    private function addFileMetaIfExists(&$array, &$i, $file, $request) {
        $base = str_replace("typo3/", "", $request->getBaseUri());
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

    private function createFileMeta($file, $parent, $title, $alternative, $description, $identifier) {
        $fileMeta = new FileMeta();
        $fileMeta->setUid($file->getUid());
        $fileMeta->setIdentifier($identifier);
        $fileMeta->setParent($parent);
        $fileMeta->setTitle($title);
        $fileMeta->setAlternative($alternative);
        $fileMeta->setDescription($description);
        return $fileMeta;
    }

    private function execQuery($fileMeta) {
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

    private function setParentParam($file, $descr) {
        if($file->getOriginalResource()->_getMetaData()[$descr]!=null) {
            return $file->getOriginalResource()->_getMetaData()[$descr];
        } else {
            return "";
        }
    }
    
    private function setParams($descr, $repo, $parentUid) {
        try {
            $obj = $repo->findFileReferenceByUid($parentUid);
            if($obj!=null) {
                $properties = $obj->getProperties();
                if ($properties[$descr] == "") {
                    return "";
                } else {
                    return $properties[$descr];
                }
            }
        } catch (Exception $ex) {
            return $ex;
        }
    }
}
