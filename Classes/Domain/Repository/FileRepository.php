<?php

namespace DominicJoas\Imgcompromizer\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

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
        $referenced = array();
        
        $files = $this->getAllEntries()->toArray();
        $i = 0;
        foreach($files as $file) {
            $repo = new \TYPO3\CMS\Core\Resource\FileRepository();
            
            $fileReferences = $this->getFileReferences($file->getUid())->toArray();
            $referenced[$i][0] = $file;
            $referenced[$i][1] = true;
            $referenced[$i][2] = $file->getOriginalResource()->_getMetaData();
            $i++;

            foreach($fileReferences as $referencedFile) {
                $referenced[$i][0] = $referencedFile;
                $referenced[$i][1] = false;
                $referenced[$i][2] = $repo->findFileReferenceByUid($referencedFile->getUid())->getProperties();
                $i++;
            }
            
        }
        return $referenced;
    }
}
