<?php
namespace DominicJoas\DjImagetools\Domain\Model;

class Files implements \TYPO3\CMS\Core\SingletonInterface {
    /**
     * Array of file
     *
     * @var array
     */
    protected $files;
    
    /**
     * 
     * @param array $files
     */
    public function setFiles($files) {
        $this->files = $files;
    }
    
    /**
     * 
     * @return array
     */
    public function getFiles() {
        return $this->files;
    }
}

