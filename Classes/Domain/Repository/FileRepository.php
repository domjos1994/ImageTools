<?php

namespace DominicJoas\DjImagetools\Domain\Repository;

use DominicJoas\DjImagetools\Domain\Model\FileMeta;

use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

class FileRepository extends Repository {

    public function getContentElementEntries($uid=0) {
        $query = $this->createQuery();
        if($uid==0) {
            $query->statement("SELECT * FROM sys_file WHERE (extension='png' or extension='jpg' or extension='JPG') AND tx_dj_imagetools_compressed!=1");
        } else {
            $query->statement("SELECT * FROM sys_file WHERE (extension='png' or extension='jpg' or extension='JPG') AND tx_dj_imagetools_compressed!=1 AND uid=$uid");
        }
        $result = $query->execute();
        return $result;
    }
    
    public function getAllEntries($uid = 0) {
        $query = $this->createQuery();
        if($uid==0) {
            $query->statement("SELECT * FROM sys_file WHERE (extension='png' or extension='jpg' or extension='JPG');");
        } else {
            $query->statement("SELECT * FROM sys_file WHERE (extension='png' or extension='jpg' or extension='JPG') AND uid=$uid;");
        }
        $result = $query->execute();
        return $result;
    }


    public function save($obj) {
        $this->update($obj);
        $this->persistenceManager->persistAll();
    }
    
    public function getFileReferences($fileUid=0) {
        $query = $this->createQuery();
        if($fileUid==0) {
            $query->statement("SELECT * FROM sys_file_reference");
        } else {
            $query->statement("SELECT * FROM sys_file_reference WHERE uid_local=$fileUid");
        }
        $result = $query->execute();
        return $result;
    }
    
    public function getFilesAndReferences() {
        $tmp = array();
        
        $files = $this->getAllEntries()->toArray();
        $i = 0;
        foreach($files as $file) {
            $fileMeta = new FileMeta();
            $fileMeta->setUid($file->getUid());
            $fileMeta->setIdentifier($file->getOriginalResource()->getIdentifier());
            $fileMeta->setParent(true);
            
            $parentParams = array();
            $parentParams[0] = $this->setParentParam($file, "title");
            $parentParams[1] = $this->setParentParam($file, "alternative");
            $parentParams[2] = $this->setParentParam($file, "description");
            
            $fileMeta->setTitle($parentParams[0]);
            $fileMeta->setAlternative($parentParams[1]);
            $fileMeta->setDescription($parentParams[2]);
            $tmp[$i] = $fileMeta;
            $i++;

            foreach($this->getFileReferences($file->getUid())->toArray() as $referencedFile) {
                $fileMeta = new FileMeta();

                $repo = new \TYPO3\CMS\Core\Resource\FileRepository();
                $fileMeta->setUid($referencedFile->getUid());
                $fileMeta->setParentUid($file->getUid());
                $fileMeta->setIdentifier($referencedFile->getOriginalResource()->getIdentifier());
                $fileMeta->setParent(false);

                $fileMeta->setTitle($this->setParams($parentParams[0], "title", $repo, $referencedFile, $fileMeta, 0));
                $fileMeta->setAlternative($this->setParams($parentParams[1], "alternative", $repo, $referencedFile, $fileMeta, 1));
                $fileMeta->setDescription($this->setParams($parentParams[2], "description", $repo, $referencedFile, $fileMeta, 2));
                $tmp[$i] = $fileMeta;
                $i++;
            } 
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
        } else {
            foreach ($this->getFileReferences($fileMeta->getParentUid())->toArray() as $referencedFile) {
                if($referencedFile->getUid()==$fileMeta->getUid()) {
                    $this->execQuery($fileMeta);
                }
            }
        }
    }

    private function execQuery($fileMeta) {
        try {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
            $queryBuilder->update('sys_file_reference')
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($fileMeta->getUid()))
                )
                ->set('title', $fileMeta->getTitle())
                ->set('description', $fileMeta->getDescription())
                ->set('alternative', $fileMeta->getAlternative())
                ->execute();
        } catch (Exception $ex) {
            
        }
    }

    private function setParentParam($file, $descr) {
        if($file->getOriginalResource()->_getMetaData()[$descr]!=null) {
            return $file->getOriginalResource()->_getMetaData()[$descr];
        } else {
            return "";
        }
    }
    
    private function setParams($parent, $descr, $repo, $referencedFile, &$fileMeta, $index) {
        $properties = $repo->findFileReferenceByUid($referencedFile->getUid())->getProperties();
        if ($properties[$descr] == "") {
            $tmp = $fileMeta->getParentData();
            $tmp[$index] = $parent;
            $fileMeta->setParentData($tmp);
            return "";
        } else {
            return $properties[$descr];
        }
    }
}
