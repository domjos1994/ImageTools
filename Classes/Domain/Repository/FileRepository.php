<?php

namespace DominicJoas\Imgcompromizer\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;
use DominicJoas\Imgcompromizer\Domain\Model\FileArray;

class FileRepository extends Repository {

    public function getContentElementEntries($uid=0) {
        $query = $this->createQuery();
        if($uid==0) {
            $query->statement("SELECT * FROM sys_file WHERE (extension='png' or extension='jpg' or extension='JPG') AND tx_imgcompromizer_compressed!=1");
        } else {
            $query->statement("SELECT * FROM sys_file WHERE (extension='png' or extension='jpg' or extension='JPG') AND tx_imgcompromizer_compressed!=1 AND uid=$uid");
        }
        $result = $query->execute();
        return $result;
    }
    
    public function getAllEntries() {
        $query = $this->createQuery();
        $query->statement("SELECT * FROM sys_file WHERE (extension='png' or extension='jpg' or extension='JPG');");
        $result = $query->execute();
        return $result;
    }


    public function save($obj) {
        $this->update($obj);
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
        $referenced = new FileArray();
        $tmp = array();
        
        $files = $this->getAllEntries()->toArray();
        $i = 0;
        foreach($files as $file) {
            $tmp[$i][0] = $file->getUid();
            $tmp[$i][1] = $file->getOriginalResource()->getIdentifier();
            $tmp[$i][2] = true;
            $tmp[$i][3] = $file->getOriginalResource()->_getMetaData()['title'];
            $tmp[$i][4] = $file->getOriginalResource()->_getMetaData()['alternative'];
            $tmp[$i][5] = $file->getOriginalResource()->_getMetaData()['description'];
            $i++;

            foreach($this->getFileReferences($file->getUid())->toArray() as $referencedFile) {
                $repo = new \TYPO3\CMS\Core\Resource\FileRepository();
                $tmp[$i][0] = $referencedFile->getUid();
                $tmp[$i][1] = $referencedFile->getOriginalResource()->getIdentifier();
                $tmp[$i][2] = false;
                $tmp[$i][3] = $repo->findFileReferenceByUid($referencedFile->getUid())->getProperties()['title'];
                $tmp[$i][4] = $repo->findFileReferenceByUid($referencedFile->getUid())->getProperties()['alternative'];
                $tmp[$i][5] = $repo->findFileReferenceByUid($referencedFile->getUid())->getProperties()['description'];
                $i++;
            } 
        }
        $referenced->setFileArray($tmp);
        return $referenced;
    }
}
