<?php
/**
 * Class Ext_UserController
 * @author CB Group
 * User login and logout for ExtJS administration area
 * @TODO Egyesíteni kéne a ModelController-rel
 */
class Ext_UserController extends CB_Controller_Ext_Action {

	/**
	 * Handle login
	 */
	public function loginAction(){
		if($this->_request->isPost()){
			$post=$this->_request->getPost();

			$authAdapter=new CB_Resource_Auth($post['username'], $post['password']);
			$result=$authAdapter->authenticate();

			switch($result->getCode()){
				case $result::SUCCESS:
					$this->returnArray['success']=true;
					$this->returnArray['msg']='Sikeres bejelentkezés';
					break;
				default:
					$this->returnArray['success']=false;
					$this->returnArray['msg']='Sikertelen bejelentkezés';
					break;
			}
		}
	}

	/**
	 * Handle logout
	 */
	public function logoutAction(){
		/**
		 * Clear all stored user data and session
		 */
		$auth=Zend_Auth::getInstance();
		$authStorage=$auth->getStorage();
		$authStorage->clear();
		$this->returnArray['success']=true;
		$this->returnArray['msg']='Sikeres kijelentkezés';
	}

}

