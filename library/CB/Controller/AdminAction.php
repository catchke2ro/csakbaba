<?

abstract class CB_Controller_AdminAction extends Zend_Controller_Action {

	/**
	 * @var CB_Resource_Mail
	 */
	public $mail;

	/**
	 * @var Zend_Controller_Action_Helper_FlashMessenger
	 */
	public $fm;

	function init(){
		$this->mail=new CB_Resource_Mail('UTF-8');
		$this->mail->init();

		$this->_helper = new \Zend_Controller_Action_HelperBroker($this);
		$this->fm=$this->_helper->getHelper('Messenger');

		$this->getHelper('layout')->setLayout('admin');
		$this->initAuth();

		$this->_head();

	}

	public function m(){
		$args=func_get_args();
		call_user_func_array(array($this->fm, 'messenger'), $args);
	}

	public function initAuth(){
		$path=APPLICATION_PATH.'/configs/password.txt';
		$pathbasic=APPLICATION_PATH.'/configs/password.basic.txt';
		$config=array('accept_schemes'=>'basic', 'realm'=>'Admin', 'digest_domains'=>'/admin', 'nonce_timeout'=>86400);
		$adapter=new Zend_Auth_Adapter_Http($config);
		$adapter->setBasicResolver(new Zend_Auth_Adapter_Http_Resolver_File($pathbasic));
		//$adapter->setDigestResolver(new Zend_Auth_Adapter_Http_Resolver_File($path));
		$adapter->setRequest($this->_request);
		$adapter->setResponse($this->_response);
		$result=$adapter->authenticate();
		if(!$result->isValid()){
			echo "<h1>Hozzáférés megtagadva</h1>";
			$this->getResponse()->setHttpResponseCode(401);
			$this->_helper->getHelper('viewRenderer')->setNoRender(true);
            $this->getHelper('layout')->disableLayout();
            return $this->getResponse();
		}
		return true;
	}

	private function _head(){

		$this->view->headLink()
			->appendStylesheet('/stylesheets/css/ext/ext-theme-classic-all.css')
			->appendStylesheet('/stylesheets/css/admin.css');
		$this->view->headScript()
			->appendFile('/js/ext/ext-all-dev.js')
			->appendFile('/js/ck/ckeditor.js')
			->appendFile('/js/ext/ckext.js')
			->appendFile('/js/admin_scripts.js')
			->appendFile('/js/admin_ext.js');
	}

}