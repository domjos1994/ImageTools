<?php
namespace DominicJoas\Imgcompromizer\Domain\Model;


class File extends \TYPO3\CMS\Extbase\Domain\Model\File {

    /**
     * The compressed-state for the image
     *
     * @var int
     * */
    protected $txImgcompromizerCompressed;

    /**
     * The width of the image
     *
     * @var int
     * */
    protected $txImgcompromizerWidth;

    /**
     * The height of the image
     *
     * @var int
     * */
    protected $txImgcompromizerHeight;

    public function setTxImgcompromizerCompressed($txImgcompromizerCompressed) {
        $this->txImgcompromizerCompressed = $txImgcompromizerCompressed;
    }

    public function getTxImgcompromizerCompressed() {
        return $this->txImgcompromizerCompressed;
    }

    public function setTxImgcompromizerWidth($txImgcompromizerWidth) {
        $this->txImgcompromizerWidth = $txImgcompromizerWidth;
    }

    public function getTxImgcompromizerWidth() {
        return $this->txImgcompromizerWidth;
    }

    public function setTxImgcompromizerHeight($txImgcompromizerHeight) {
        $this->txImgcompromizerHeight = $txImgcompromizerHeight;
    }

    public function getTxImgcompromizerHeight() {
        return $this->txImgcompromizerHeight;
    }

}

