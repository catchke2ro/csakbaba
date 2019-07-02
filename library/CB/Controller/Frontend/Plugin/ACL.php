<?php
class CB_Controller_Frontend_Plugin_ACL extends Zend_Controller_Plugin_Abstract {

	/**
	 * @var CB_Resource_ACL()
	 */
	public $acl;

	/**
	 * @var Zend_Auth()
	 */
	public $auth;

	/**
	 * @var Zend_View();
	 */
	private $view;

	public function routeShutdown(Zend_Controller_Request_Abstract $request){
		if($request->getModuleName()!='frontend') {
			Zend_Controller_Front::getInstance()->unregisterPlugin($this);
			return;
		}

		if($request->isGet() &&$request->get('logout')==1) {
			$authAdapter=new CB_Resource_Auth();
			$authAdapter->logout();
			$this->_redirect('/');
		}

		$this->acl=new CB_Resource_ACL();
		$this->acl->initPermissions();
		$this->auth=Zend_Auth::getInstance();
		$this->view=Zend_Layout::getMvcInstance()->getView();

		if(Zend_Registry::isRegistered('nav')){
			/**
			 * @var $nav SRG_Resource_Navigation()
			 */
			$nav=Zend_Registry::get('nav');

			$role=Zend_Auth::getInstance()->getIdentity() ? 'user' : 'public';

			if(!$nav->findBy('active', true)) {
				$allowed=true;
			} else {
				$allowed=$this->acl->isAllowed($role, $nav->findBy('active', true)->get('resource'));
			}

			if($allowed===false){
				$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
				$redirector->goToUrl('/bejelentkezes?r='.urlencode($request->getRequestUri()));
			}

			$this->view->navigation($nav)->setAcl($this->acl)->setRole($role);
		}

	}

}

