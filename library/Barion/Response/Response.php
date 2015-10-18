<?php

namespace Barion\Response;

use Barion\Barion;

class Response {

	public $rawResponse;

	private $_errors;

	public function __construct($jsonResponse){

		if(!isJson($jsonResponse)){
			$this->_errors[]=[
				'ErrorCode'=>'SystemError',
				'Title'=>'System Error',
				'Description'=>$jsonResponse
			];
			return;
		}

		$this->rawResponse=json_decode($jsonResponse, true);

		if(!empty($this->rawResponse['Errors'])){
			$this->_errors=$this->rawResponse['Errors'];
		}

	}

	public function isOK(){
		return empty($this->_errors);
	}

	public function getErrors(){
		return $this->_errors;
	}

}