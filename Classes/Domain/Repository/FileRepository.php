<?php

namespace DominicJoas\DjImagetools\Domain\Repository;

use DominicJoas\DjImagetools\Domain\Model\FileMeta;
use DominicJoas\DjImagetools\Utility\Helper;

use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

class FileRepository extends Repository {

    public function getContentElementEntries($uid=0) {
        $folderIdentifier = Helper::getFolIdent();
        $query = $this->createQuery();
        if($uid==0) {
            $query->statement("SELECT * FROM sys_file WHERE (extension='png' or extension='jpg' or extension='JPG') AND tx_dj_imagetools_compressed!=1 AND identifier like '$folderIdentifier%'");
        } else {
            $query->statement("SELECT * FROM sys_file WHERE (extension='png' or extension='jpg' or extension='JPG') AND tx_dj_imagetools_compressed!=1 AND uid=$uid AND identifier like '$folderIdentifier%'");
        }
        $result = $query->execute();
        return $result;
    }
    
    public function getAllEntries($uid = 0) {
        $folderIdentifier = Helper::getFolIdent();
        $query = $this->createQuery();
        if($uid==0) {
            $query->statement("SELECT * FROM sys_file WHERE (extension='png' or extension='jpg' or extension='JPG')  AND identifier like '$folderIdentifier%';");
        } else {
            $query->statement("SELECT * FROM sys_file WHERE (extension='png' or extension='jpg' or extension='JPG') AND uid=$uid AND identifier like '$folderIdentifier%';");
        }
        $result = $query->execute();
        return $result;
    }


    public function save($obj) {
        $this->update($obj);
        $this->persistenceManager->persistAll();
    }
    
    public function getFileReferences($fileUid=0) {
        $folderIdentifier = Helper::getFolIdent();
        $query = $this->createQuery();
        if($fileUid==0) {
            $query->statement("SELECT * FROM sys_file_reference");
        } else {
            $query->statement("SELECT * FROM sys_file_reference WHERE uid_local=$fileUid");
        }
        $result = $query->execute();
        return $result;
    }
    
    public function getFilesAndReferences($request) {
        $tmp = array();
        
        $files = $this->getAllEntries()->toArray();
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
            $parentParams[0] = $this->setParentParam($file, "title");
            $parentParams[1] = $this->setParentParam($file, "alternative");
            $parentParams[2] = $this->setParentParam($file, "description");
            $array[$i++] = $this->createFileMeta($file, true, $parentParams[0], $parentParams[1], $parentParams[2]);
            
            foreach($this->getFileReferences($file->getUid())->toArray() as $referencedFile) {
                $this->addReferenceFileMetaIfExists($array, $i, $referencedFile, $parentUid, $parentParams);
            }
        }
    }

    private function addReferenceFileMetaIfExists(&$array, &$i, $reference, $parentUid, $parentParams) {
        $repo = new \TYPO3\CMS\Core\Resource\FileRepository();

        $fileMeta = $this->createFileMeta($reference, false, $this->setParams("title", $repo, $reference), $this->setParams("alternative", $repo, $reference), $this->setParams("description", $repo, $reference));
        $fileMeta->setParentData($parentParams);
        $fileMeta->setParentUid($parentUid);

        $array[$i++] = $fileMeta;
    }

    private function createFileMeta($file, $parent, $title, $alternative, $description) {
        $fileMeta = new FileMeta();
        $fileMeta->setUid($file->getUid());
        $fileMeta->setIdentifier($file->getOriginalResource()->getIdentifier());
        $fileMeta->setParent($parent);
        $fileMeta->setTitle($title);
        $fileMeta->setAlternative($alternative);
        $fileMeta->setDescription($description);
        return $fileMeta;
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
    
    private function setParams($descr, $repo, $referencedFile) {
        $properties = $repo->findFileReferenceByUid($referencedFile->getUid())->getProperties();
        if ($properties[$descr] == "") {
            return "";
        } else {
            return $properties[$descr];
        }
    }
}
