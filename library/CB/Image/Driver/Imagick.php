<?php

/**
 * @see CB_Image_Driver_Exception
 */
require_once 'CB/Image/Driver/Exception.php';


/**
 * @see CB_Image_Driver_Abstract
 */
require_once 'CB/Image/Driver/Abstract.php';

/**
 * Imagick driver
 *
 * @category    Zend
 * @package     CB_Image_Image
 * @subpackage  Zend_Image_Driver
 * @author      Domas Trijonis <leonid@shagabutdinov.com>
 */
class CB_Image_Driver_Imagick extends CB_Image_Driver_Abstract {

	protected $_imagick;
	public function __construct() {
		$this->_imagick = new Imagick();
	}
	public function load($fileName) {
		parent::load($fileName);
		if(!file_exists($fileName))
			throw new CB_Image_Driver_Exception("Image not found,".$fileName);
		$this->_imagick->readimage($fileName);
		$this->_imageLoaded = true;
	}

	public function resize($width, $height) {
		parent::resize($width, $height);
		$this->_imagick->scaleimage($width, $height);
	}
	public function crop($left, $top, $targetWidth, $targetHeight) {
		parent::crop($left, $top, $targetWidth, $targetHeight);
		$this->_imagick->cropimage($targetWidth, $targetHeight, $left, $top);		
	}
	public function getBinary() {
		return $this->_imagick->getimageblob();
	}

	public function getSize() {
		return array($this->_imagick->getimagewidth(),$this->_imagick->getimageheight());
	}

	public function save($filename, $type = 'auto') {
		$this->_imagick->writeimage($filename);
	}
	
}