<?php
include(APPLICATION_PATH.'/../library/Facebook/facebook.php');

class CB_Resource_Facebook {

	public $fb;
	private $_appId;
	private $_secret;
	private $_cookie=true;
	public $scopes;

	public function __construct(){
		$this->_appId='267991083361773';
		$this->_secret='f42b31b0d1ff1ed7da2504d21b11be3f';
		$this->scopes=array('email');
		$this->fb=new Facebook(array('appId'=>$this->_appId, 'secret'=>$this->_secret, 'cookie'=>$this->_cookie));
	}

	public function login(){
		$user=$this->fb->getUser();
		if($user){
			$userProfile = $this->fb->api('/me');
			return $userProfile;
		} else {
			$loginUrl = $this->fb->getLoginUrl(array(
				'scope'=>implode(', ', $this->scopes),
				'redirectUri'=>'https://'.$_SERVER['HTTP_HOST'].'/slogin?s=fb'
			));
		}

		$redirector=Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
		$redirector->gotoUrl($loginUrl);
	}

	public function post($message, $url=false){
		$url=$url==false ? $_SERVER['HTTP_HOST'] : $url;
		$user=$this->fb->getUser();
		if($user){
			$return_obj=$this->fb->api('/me/feed', 'POST', array('link'=>$url, 'message'=>$message));
			if($return_obj['id']) return true;
		}
		return false;
	}

}