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
    
    public function save($obj) {
        $this->update($obj);
    }
}
