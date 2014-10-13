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

	public function commentsAction(){
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
			$payment->_invoice('INVOICE');
			$payment->_userBalance();
		}
		echo json_encode(array('success'=>true));
	}


	public function moderateAction(){
		$this->_helper->layout()->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
		$commentId=$_POST['record'];
		$checked=$_POST['checked'];
		$commentModel=new \CB\Model\Comment();
		$productModel=new \CB\Model\Product();
		if(($comment=$commentModel->findOneById($commentId)) && ($product=$productModel->findOneById($comment->product_id))){
			if($checked=='true'){
				$emails=new CB_Resource_Emails(false);
				$emails->commentModerated(array('product'=>$product, 'user'=>$comment->user, 'comment'=>$comment));
			}
			echo json_encode(array('success'=>true));
		} else {
			echo json_encode(array('success'=>false));
		}
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


	public function batchuploadAction(){
		$userModel=new \CB\Model\User();
		$productModel=new \CB\Model\Product();
		$file=fopen(APPLICATION_PATH.'/../tmp/batch3.csv', 'r');
		$user=$userModel->findOneById('52a4bf170f435f574f8b4567');

		setlocale(LC_ALL, 'hu_HU.UTF-8');
		$this->_helper->layout()->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
		$h=array('cat'=>0, 'name'=>1, 'desc'=>2, 'price'=>3, 'age'=>4, 'size'=>5, 'type'=>6, 'new'=>7, 'pic'=>8, 'catid'=>9, 'typeid'=>10, 'ageid'=>11 );
		$dataArray=array();
		while (($data = fgetcsv($file, 100000, ",")) !== FALSE) {
			$dataArray[]=$data;
		}
		fclose($file);
		unset($dataArray[0]);
		$categories=new CB_Array_Categories();
		$images=scandir(APPLICATION_PATH.'/../tmp/batchimg');
		
		foreach($dataArray as $row){
			$ok=$this->_validate($row, $categories, $h);
			if(!$ok){
				continue;
			}
			$product=$this->_createProduct($row, $categories, $h, $user, $images);
			//$productModel->save($product);
		}
	}

	private function _validate($row, $categories, $h){
		$ok=true;
		$ok=$ok && array_key_exists($row[$h['catid']], $categories->_singleArray);
		$ok=$ok && !empty($row[$h['name']]);
		$ok=$ok && !empty($row[$h['desc']]);
		$ok=$ok && $this->_fetchPrice($row[$h['price']]);
		$ok=$ok && array_key_exists($row[$h['typeid']], Zend_Registry::get('genreTypes'));
		$ok=$ok && !empty($row[$h['ageid']]);
		$ok=$ok && in_array($row[$h['new']], array('új', 'használt'));
		return $ok;
	}

	private function _fetchPrice($str){
		$str=preg_replace('/[^0-9]/i', '', $str);
		$int=intval($str);
		return (is_int($int) && $int>0) ? $int : false;
	}

	private function _createProduct($row, $categories, $h, $user, $images){
		$product=new \CB\Product();
		$product->name=trim($row[$h['name']]);
		$product->search_name=strtolower($product->name);
		$product->desc=trim($row[$h['desc']]);
		$product->category=$row[$h['catid']];
		$product->user=$user;
		$product->price=$this->_fetchPrice($row[$h['price']]);
		$product->date_added=date('Y-m-d H:i:s');
		$product->date_period=date('Y-m-d H:i:s');
		$product->status=1;
		$product->type=$row[$h['typeid']];
		$product->new=$row[$h['new']]=='új' ? true : false;
		$product->deliveries=array('personal', 'post');
		$product->autorenew='never';
		$product->code=uniqid('CSB');

		$product->images=$this->_findImgs($row[$h['pic']], $images);

		$category=$categories->_singleArray[$product->category];
		$product->options=$this->_fetchOptions($row, $category, $h, $categories->_props);
		return $product;
	}

	private function _findImgs($imgRoot, $images){
		$imgClass=new CB_Resource_Image('/upload/product');
		if(empty($imgRoot)) return array();
		$imgs=array();
		foreach($images as $img){
			if(strpos($img, $imgRoot)===0){
				$file=array('tmp_name'=>APPLICATION_PATH.'/../tmp/batchimg/'.$img, 'name'=>$img);
				$imgs[]=$imgClass->handleImage($file);
			}
		}
		return $imgs;
	}

	private function _fetchOptions($row, $category, $h, $props){
		$options=array();
		foreach($category->props as $propKey){
			$value=false;
			$prop=$props[$propKey];
			if(strpos($propKey, 'meret')){
				$value=$row[$h['size']];
				$value=$this->_closest(range($prop['min'], $prop['max'], (isset($prop['step']) ? $prop['step'] : 1)), $value);
			}
			if(strpos($propKey, 'kor')){
				$value=substr($row[$h['ageid']], 1);
			}
			$options[$propKey]=$value;
		}
		return $options;
	}

	private function _closest($array, $number){
		sort($array);
		foreach ($array as $a) {
			if ($a >= $number) return $a;
		}
		return end($array);
	}

	public function exportAction(){

	}


	public function csvAction(){
		$this->getHelper('viewRenderer')->setNoRender(true);
		$this->getHelper('layout')->disableLayout();
		$modelName='\CB\Model\\'.$_GET['model'];
		$model=new $modelName();
		$docs=$model->find();
		$csvData=array();
		$props=array();
		foreach($docs as $doc){
			$row=array();
			$docArray=object_to_array($doc, -1, true);
			foreach($docArray as $prop=>$val){
				if(is_object($val) && get_class($val)=='DateTime') $val=$val->format('Y-m-d H:i:s');
				elseif(is_object($val) && method_exists($val, '__toString')) $val=$val->__toString();
				elseif(is_array($val)){
					$val=object_to_array($val, -1);
					$val=implode(' ', $val);
				}
				$props[]=$prop;
				$row[$prop]=strval($val);
			}
			$csvData[]=$row;
		}
		$props=array_unique($props);
		$newCsvData=array();
		$newCsvData[0]=$props;
		foreach($csvData as $key=>$csvItem){
			foreach($props as $prop){
				$newCsvData[$key+1][]=isset($csvItem[$prop]) ? $csvItem[$prop] : '';
			}
		}

		//pr($newCsvData);

		$pe=new PHPExcel();
		$pe->getProperties()->setCreator('csakbaba')->setTitle('csakbaba.hu '.$_GET['model'].' export');
		$pe->setActiveSheetIndex(0);
		$pe->getActiveSheet()->setTitle('Felhasználók')->fromArray($newCsvData);

		$time=time();
		$objWriter = PHPExcel_IOFactory::createWriter($pe, 'Excel2007');
		$objWriter->save('php://output');


		header('Content-Type: application/vnd.ms-excel; charset=utf-8');
		header('Content-Disposition: attachment; filename=csakbaba_export_'.strtolower($_GET['model']).'_'.date('YmdHis').'.xls');  //File name extension was wrong
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private', false);
		die();
	}
}
