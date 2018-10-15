<?php
namespace DominicJoas\Imgcompromizer\Domain\Model;

class FileArray {
    
    /**
     * Array of FileData
     *
     * @var array
     * */
    protected $fileArray;
    
    public function setFileArray($fileArray) {
        $this->fileArray = $fileArray;
    }

    public function getFileArray() {
        return $this->fileArray;
    }
}

