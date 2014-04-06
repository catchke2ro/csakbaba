<?php
/**
 * Class Ext_UploadController
 * @author CB Group
 * Handle Upload actions for ExtJS administration area
 */
class Ext_UploadController extends CB_Controller_Ext_Action {

	/**
	 * Handle upload
	 */
	public function indexAction(){
		$this->returnArray=array('success'=>true); //@TODO Ne minden esetben legyen sikeres a feltöltés. Ellnőrzések, ilyesmi

		$fileData=array(
			'name'=>isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : $_FILES['file']['name'],
			'size'=>isset($_SERVER['HTTP_X_FILE_SIZE']) ? $_SERVER['HTTP_X_FILE_SIZE'] : $_FILES['file']['size'],
			'type'=>isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : $_FILES['file']['type']
		);

		if(!empty($_FILES)){
			move_uploaded_file($_FILES['file']['tmp_name'], APPLICATION_PATH.'/../public/site/upload/'.$fileData['name']);
		} else {

			/**
			 * Read uploaded file data to contents variable
			 */
			$postData=fopen('php://input', 'r'); //Uploaded data
			$contents='';
			while (!feof($postData)) { $contents.=fread($postData, 1024); }
			fclose($postData);

			/**
			 * Write contents to target file
			 */
			$targetFile=fopen(APPLICATION_PATH.'/../public/site/upload/'.$fileData['name'], 'w');
			fwrite($targetFile, $contents);
			fclose($targetFile);

		}

		/**
		 * Return file data
		 */
		$this->returnArray['file']['url']='/site/upload/'.$fileData['name'];
		$this->returnArray['file']['name']=$fileData['name'];
		$this->returnArray['file']['size']=$fileData['size'];
		$this->returnArray['file']['type']=$fileData['type'];
	}

}

