<?php
include(APPLICATION_PATH.'/../library/Google/Google_Client.php');
include(APPLICATION_PATH.'/../library/Google/contrib/Google_PlusService.php');
include(APPLICATION_PATH.'/../library/Google/contrib/Google_Oauth2Service.php');

class CB_Resource_Google {

	private $_appName;
	private $_clientId;
	private $_clientSecret;
	private $_redirectUri;
	private $_devKey;

	/**
	 * @var Google_Client()
	 */
	public $client;

	/**
	 * @var Google_PlusService()
	 */
	public $plus;

	/**
	 * @var Google_Oauth2Service()
	 */
	public $oauth;

	public function __construct(){
		$this->_appName='csakbaba';
		$this->_clientId='431753252279-drhq9ru6eqmo6v9qod6schtf1b6q8t9c.apps.googleusercontent.com';
		$this->_clientSecret='HmKAvFKPops7mrAKwttXvEWN';
		$this->_devKey='AIzaSyDDOLL7ZTZzYJo6M9VE15ZBr6cbnhZC3xU';
		$this->_redirectUri='https://'.$_SERVER['HTTP_HOST'].'/slogin?s=gp';
		$this->_initClient();
	}


	public function login(){
		$this->_initOAuth();

		if (isset($_GET['code'])) {
			$this->client->authenticate();
			$_SESSION['token'] = $this->client->getAccessToken();
			//$redirector=Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
			//$redirector->gotoUrl('/');
		}

		if (isset($_SESSION['token'])) {
			$this->client->setAccessToken($_SESSION['token']);
		}

		if ($this->client->getAccessToken()) {
			$profile=$this->oauth->userinfo->get();
			return $profile;
		} else {
			$this->client->setScopes(array(
				'https://www.googleapis.com/auth/plus.me',
				'https://www.googleapis.com/auth/userinfo.email',
				'https://www.googleapis.com/auth/userinfo.profile'
			));
			$authUrl = $this->client->createAuthUrl();
			$redirector=Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
			$redirector->gotoUrl($authUrl);
		}
	}


	private function _initClient(){
		$this->client=new Google_Client();
		$this->client->setApplicationName($this->_appName);
		$this->client->setClientId($this->_clientId);
		$this->client->setClientSecret($this->_clientSecret);
		$this->client->setRedirectUri($this->_redirectUri);
		$this->client->setDeveloperKey($this->_devKey);
	}

	private function _initOAuth(){
		$this->oauth=new Google_Oauth2Service($this->client);

	}

	private function _initPlus(){
		$this->plus=new Google_PlusService($this->client);
	}

}