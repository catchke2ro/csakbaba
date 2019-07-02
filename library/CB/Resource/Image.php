<?php

class CB_Resource_Image {

	public $targetDir;

	public function __construct($targetDir=null){
		$this->targetDir=$targetDir;
		if(!empty($_POST['targetdir'])) $this->targetDir=$_POST['targetdir'];
	}

	public function handleImage(array $file){
		$returnFile=array();
		$image=new CB_Image_Transform($file['tmp_name'], new CB_Resource_Imagick());
		$image->getDriver()->watermark();

		$newPath=APPLICATION_PATH.'/../public'.$this->targetDir.'/';
		list($fnroot, $ext) = $this->_fileExplode($file['name']);
		$newFilenameroot=(time().'_'.uniqid());

		$image->getDriver()->setJPG();
		$image->save($newPath.$newFilenameroot.'.jpg');
		$image->fitIn(600, 600);
		$image->save($newPath.$newFilenameroot.'_mid'.'.jpg');
		$image->fitIn(200, 200);
		$image->save($newPath.$newFilenameroot.'_small'.'.jpg');

		$returnFile['name']=$newFilenameroot.'.jpg';
		$returnFile['url']=$this->targetDir.'/'.$newFilenameroot.'.jpg';
		$returnFile['mid']=$this->targetDir.'/'.$newFilenameroot.'_mid.jpg';
		$returnFile['small']=$this->targetDir.'/'.$newFilenameroot.'_small.jpg';

		return $returnFile;
	}

	private function _fileExplode($filename){
		$dotPosition=strrpos($filename, '.');
		$extension=substr($filename, $dotPosition+1);
		$filenameroot=substr($filename, 0, $dotPosition);
		return array($filenameroot, '.'.$extension);
	}

	private function _watermark($image){
		$wm=APPLICATION_PATH.'/../public/img/elements/watermark.png';

		$watermark=new Imagick();
		$watermark->readimage($wm);
		$imageWidth=$image->getimagewidth();
		$imageHeight=$image->getimageheight();
		$wmratio=$watermark->getimagewidth()/$watermark->getimageheight();
		$watermark->scaleimage($imageWidth/4, ($imageWidth/4)/$wmratio);

		$image->compositeImage($watermark, imagick::COMPOSITE_OVER, $imageWidth*(3/4), ($imageHeight-($imageWidth/4)/$wmratio));
		return $image;
	}

}