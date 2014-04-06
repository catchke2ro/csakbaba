<?php

class UploadController extends CB_Controller_Action {

	public function uploadAction(){
		$this->getHelper('viewRenderer')->setNoRender(true);
		$this->getHelper('layout')->disableLayout();
		$files=$_FILES[$_POST['name']];
		CB_Resource_Functions::logEvent('uploadFile', array('files'=>$files));
		$return=array('files'=>array());
		for($i=0;$i<count($files['name']);$i++){
			$file=array('tmp_name'=>$files['tmp_name'][$i], 'name'=>$files['name'][$i]);
			$return['files'][]=$this->handleImage($file);
		}

		$this->getResponse()->setHeader('Content-type', 'text/plain');
		echo json_encode($return);
	}

	public function handleImage(array $file){
		$returnFile=array();
		$image=new Zend_Image_Transform($file['tmp_name'], new CB_Resource_Imagick());
		$image->getDriver()->watermark();

		$newPath=APPLICATION_PATH.'/../public'.$_POST['targetdir'].'/';
		list($fnroot, $ext) = $this->_fileExplode($file['name']);
		$newFilenameroot=(time().'_'.uniqid());

		$image->getDriver()->setJPG();
		$image->save($newPath.$newFilenameroot.'.jpg');
		$image->fitIn(600, 600);
		$image->save($newPath.$newFilenameroot.'_mid'.'.jpg');
		$image->fitIn(200, 200);
		$image->save($newPath.$newFilenameroot.'_small'.'.jpg');

		$returnFile['name']=$newFilenameroot.'.jpg';
		$returnFile['url']=$_POST['targetdir'].'/'.$newFilenameroot.'.jpg';
		$returnFile['mid']=$_POST['targetdir'].'/'.$newFilenameroot.'_mid.jpg';
		$returnFile['small']=$_POST['targetdir'].'/'.$newFilenameroot.'_small.jpg';

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
