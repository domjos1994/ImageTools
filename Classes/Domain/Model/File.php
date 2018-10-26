<?php
namespace DominicJoas\DjImagetools\Domain\Model;


class File extends \TYPO3\CMS\Extbase\Domain\Model\File {

    /**
     * Uid of file
     *
     * @var integer
     */
    protected $uid;
    
    /**
     * The compressed-state for the image
     *
     * @var int
     * */
    protected $txDjImagetoolsCompressed;

    /**
     * The width of the image
     *
     * @var int
     * */
    protected $txDjImagetoolsWidth;

    /**
     * The height of the image
     *
     * @var int
     * */
    protected $txDjImagetoolsHeight;

    public function __construct() {
        //parent::__construct();
        $this->txDjImagetoolsCompressed = 0;
        $this->txDjImagetoolsHeight = -1;
        $this->txDjImagetoolsWidth = -1;
    }

    public function getUid() {
        return $this->uid;
    }

    public function setUid($uid) {
        $this->uid = $uid;
    }
    
    public function setTxDjImagetoolsCompressed($txDjImagetoolsCompressed) {
        $this->txDjImagetoolsCompressed = $txDjImagetoolsCompressed;
    }

    public function getTxDjImagetoolsCompressed() {
        return $this->txDjImagetoolsCompressed;
    }

    public function setTxDjImagetoolsWidth($txDjImagetoolsWidth) {
        $this->txDjImagetoolsWidth = $txDjImagetoolsWidth;
    }

    public function getTxDjImagetoolsWidth() {
        return $this->txDjImagetoolsWidth;
    }

    public function setTxDjImagetoolsHeight($txDjImagetoolsHeight) {
        $this->txDjImagetoolsHeight = $txDjImagetoolsHeight;
    }

    public function getTxDjImagetoolsHeight() {
        return $this->txDjImagetoolsHeight;
    }
}

