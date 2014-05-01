<?php

class UploadController extends CB_Controller_Action {

	public function uploadAction(){
		$this->getHelper('viewRenderer')->setNoRender(true);
		$this->getHelper('layout')->disableLayout();
		$files=$_FILES[$_POST['name']];
		CB_Resource_Functions::logEvent('uploadFile', array('files'=>$files));
		$imgClass=new CB_Resource_Image();
		$return=array('files'=>array());
		for($i=0;$i<count($files['name']);$i++){
			$file=array('tmp_name'=>$files['tmp_name'][$i], 'name'=>$files['name'][$i]);
			$return['files'][]=$imgClass->handleImage($file);
		}

		$this->getResponse()->setHeader('Content-type', 'text/plain');
		echo json_encode($return);
	}



}
