<?php
namespace DominicJoas\DjImagetools\Domain\Model;

class FileMeta implements \TYPO3\CMS\Core\SingletonInterface {

    /**
     * Uid of file
     *
     * @var integer
     */
    protected $uid;
    
    /**
     * Parent-Uid of file
     *
     * @var integer
     */
    protected $parentUid;

    /**
     * Identifier of file
     *
     * @var string
     */
    protected $identifier;
    
    /**
     * File is parent
     *
     * @var boolean
     */
    protected $parent;

    /**
     * Title of meta
     *
     * @var string
     */
    protected $title;
    
    /**
     * Alt of meta
     *
     * @var string
     */
    protected $alternative;
    
    /**
     * Description of meta
     *
     * @var string
     */
    protected $description;
    
    /**
     * ParentData of meta
     *
     * @var array
     */
    protected $parentData;
    
    public function __construct() {
        $this->parentData[0] = "";
        $this->parentData[1] = "";
        $this->parentData[2] = "";
    }

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
     * @return integer
     */
    public function getParentUid() {
        return $this->parentUid;
    }

    /**
     * 
     * @param integer $parentUid
     */
    public function setParentUid($parentUid) {
        $this->parentUid = $parentUid;
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
     * @return boolean
     */
    public function getParent() {
        return $this->parent;
    }

    /**
     * 
     * @param boolean $parent
     */
    public function setParent($parent) {
        $this->parent = $parent;
    }

    /**
     * 
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }
    
    /**
     * 
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * 
     * @return string
     */
    public function getAlternative() {
        return $this->alternative;
    }

    /**
     * 
     * @param string $alternative
     */
    public function setAlternative($alternative) {
        $this->alternative = $alternative;
    }

    /**
     * 
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * 
     * @param string $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }
    
    /**
     * 
     * @return array
     */
    public function getParentData() {
        return $this->parentData;
    }

    /**
     * 
     * @param array $parentData
     */
    public function setParentData($parentData) {
        $this->parentData = $parentData;
    }
}

