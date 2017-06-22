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
		$this->fb=new \Facebook\Facebook(array('app_id'=>$this->_appId, 'app_secret'=>$this->_secret));
	}

	public function login(){
		$helper = $this->fb->getRedirectLoginHelper();
		
		try {
			$accessToken = $helper->getAccessToken();
		} catch(Exception $e) {
			$login = true;
		}
		
		if(!isset($accessToken)){
			$login = true;
		} else {
			$_SESSION['fb_access_token'] = (string) $accessToken;
			$response = $this->fb->get('/me?fields=id,name,email,gender', $accessToken);
			$user = $response->getGraphUser();
			$login = $user->asArray();
		}
		
        if($login === true){
            $loginUrl = $helper->getLoginUrl('https://'.$_SERVER['HTTP_HOST'].'/slogin?s=fb', $this->scopes);
            $redirector=Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $redirector->gotoUrl($loginUrl);
            return;
        }

        return $login;

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