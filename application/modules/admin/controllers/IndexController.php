<?php

class Admin_IndexController extends CB_Controller_AdminAction {

	public function indexAction(){
	}

	public function usersAction(){
	}

	public function productsAction(){
	}

	public function ordersAction(){
	}

	public function ratingsAction(){
	}

	public function chargeAction(){
		$this->_helper->layout()->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
		if(empty($_POST['uid'])) return false;
		$userModel=new \CB\Model\User();
		$user=$userModel->findOneById($_POST['uid']);
		if($user){
			$payment=new CB_Resource_Payment();
			$payment->newPayment(array(
							'status'=>2,
							'user'=>$user,
							'type'=>$_POST['type'],
							'amount'=>$_POST['amount']
			));
			$payment->_invoice();
			$payment->_userBalance();
		}
		echo json_encode(array('success'=>true));
	}

	public function uploadAction(){
		$targetdir=!empty($_GET['td']) ? $_GET['td'].'/' : '';
		$this->_helper->layout()->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
		$returnArray=array('success'=>true); //@TODO Ne minden esetben legyen sikeres a feltöltés. Ellnőrzések, ilyesmi

		$fileData=array(
			'name'=>isset($_SERVER['HTTP_X_FILE_NAME']) ? $_SERVER['HTTP_X_FILE_NAME'] : $_FILES['file']['name'],
			'type'=>isset($_SERVER['HTTP_X_FILE_TYPE']) ? $_SERVER['HTTP_X_FILE_TYPE'] : $_FILES['file']['type']
		);

		if(!empty($_FILES)){
			move_uploaded_file($_FILES['file']['tmp_name'], APPLICATION_PATH.'/../public/upload/'.$fileData['name']);
		} else {

			$postData=fopen('php://input', 'r'); //Uploaded data
			$contents='';
			while (!feof($postData)) { $contents.=fread($postData, 1024); }
			fclose($postData);

			$targetFile=fopen(APPLICATION_PATH.'/../public/upload/'.$fileData['name'], 'w');
			fwrite($targetFile, $contents);
			fclose($targetFile);
		}



		$image=new Zend_Image_Transform(APPLICATION_PATH.'/../public/upload/'.$fileData['name'], new Zend_Image_Driver_Gd());

		$newPath=APPLICATION_PATH.'/../public/upload/'.$targetdir;
		$publicPath='/upload/'.$targetdir;
		list($fnroot, $ext) = $this->_fileExplode($fileData['name']);
		$newFilenameroot=(time().'_'.$fnroot);
		$image->save($newPath.$newFilenameroot.$ext);

		$image->fitIn(600, 600);
		$image->save($newPath.$newFilenameroot.'_mid'.$ext);
		$image->fitIn(200, 200);
		$image->save($newPath.$newFilenameroot.'_small'.$ext);

		$returnArray['file']['name']=$newFilenameroot.$ext;
		$returnArray['file']['url']=$publicPath.$newFilenameroot.$ext;
		$returnArray['file']['mid']=$publicPath.$newFilenameroot.'_mid'.$ext;
		$returnArray['file']['small']=$publicPath.$newFilenameroot.'_small'.$ext;
		$returnArray['file']['type']=$fileData['type'];

		echo Zend_Json::encode($returnArray);
	}

	private function _fileExplode($filename){
		$dotPosition=strrpos($filename, '.');
		$extension=substr($filename, $dotPosition+1);
		$filenameroot=substr($filename, 0, $dotPosition);
		return array($filenameroot, '.'.$extension);
	}

}
