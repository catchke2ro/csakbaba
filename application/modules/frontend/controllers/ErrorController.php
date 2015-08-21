<?php

class ErrorController extends CB_Controller_Action {

	public function errorAction(){
	}

	public function notfoundAction(){
		$this->getResponse()->setHttpResponseCode(404);
	}

}