<?php
namespace DominicJoas\DjImagetools\Domain\Model;

class ComparableFile implements \TYPO3\CMS\Core\SingletonInterface {
    /**
     * Uid of file
     *
     * @var integer
     */
    protected $uid;
    
    /**
     * Identifier of file
     *
     * @var string
     */
    protected $identifier;
    
    /**
     * Compared Files
     * 
     * @var array
     */
    protected $comparableFiles;
    
    /**
     * 
     * @return integer
     */
    public function getUid() {
        return $this->uid;
    }

    /**
     * 
     * @param integer $uid
     */
    public function setUid($uid) {
        $this->uid = $uid;
    }
    
    /**
     * 
     * @return string
     */
    public function getIdentifier() {
        return $this->identifier;
    }

    /**
     * 
     * @param string $identifier
     */
    public function setIdentifier($identifier) {
        $this->identifier = $identifier;
    }
    
    /**
     * 
     * @return array
     */
    public function getComparableFiles() {
        return $this->comparableFiles;
    }

    /**
     * 
     * @param array comparableFiles
     */
    public function setComparableFiles($comparableFiles) {
        $this->comparableFiles = $comparableFiles;
    }
}
