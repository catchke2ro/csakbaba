<?php

class CB_Resource_Imagick extends Zend_Image_Driver_Imagick{

	public function watermark(){
		$wm=APPLICATION_PATH.'/../public/img/elements/watermark.png';

		$watermark=new Imagick();
		$watermark->readimage($wm);
		$imageWidth=$this->_imagick->getimagewidth();
		$imageHeight=$this->_imagick->getimageheight();
		$wmratio=$watermark->getimagewidth()/$watermark->getimageheight();
		$watermark->scaleimage($imageWidth/4, ($imageWidth/4)/$wmratio);

		$this->_imagick->compositeImage($watermark, imagick::COMPOSITE_OVER, $imageWidth*(3/4), ($imageHeight-($imageWidth/4)/$wmratio));
	}

	public function setJPG($quality=80){
		$this->_imagick->setImageFormat('jpeg');
		$this->_imagick->setImageCompression(Imagick::COMPRESSION_LOSSLESSJPEG);
		$this->_imagick->stripImage();
		$this->_imagick->setImageCompressionQuality($quality);
	}

}