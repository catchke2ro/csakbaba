<?

class CB_Resource_Route extends  Zend_Controller_Router_Route_Module {

	public $navigation;
	private $extraParams=array();
	private $breadcrumb=array();

	public function __construct(array $defaults = array(),
	                            Zend_Controller_Dispatcher_Interface $dispatcher = null,
	                            Zend_Controller_Request_Abstract $request = null){
		$this->navigation=new CB_Resource_Navigation();
		parent::__construct();
		//call_user_func_array(array(parent, '__construct'), func_get_args());
	}

	public function match($path, $partial=false){
		if(defined('CRON') && CRON==true){
			return array(
				'controller'=>'cron',
				'action'=>'cron',
				'module'=>'frontend'
			);
		}

		$page=$this->_findPage($path);
		$this->extraParams=array_reverse($this->extraParams);
		if(!$page){
			$defaultMatch=parent::match($path, $partial);
			$front=Zend_Controller_Front::getInstance();
			$request=$front->getRequest();
			foreach($defaultMatch as $param=>$value){
				$request->setParam($param, $value);
				if($param === $request->getModuleKey()) $request->setModuleName($value);
				if($param === $request->getControllerKey()) $request->setControllerName($value);
				if($param === $request->getActionKey()) $request->setActionName($value);
			}
			if(!$front->getDispatcher()->isDispatchable($front->getRequest())){
				return(array('controller'=>'error', 'action'=>'notfound'));
			}
			$hasResource=$this->navigation->findOneBy('mvc', $defaultMatch);
			if($hasResource){
				$redirector=Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
				$redirector->goToUrl($hasResource->getUri());
			}
			return $defaultMatch;
		}
		$view=Zend_Layout::getMvcInstance()->getView();
		$view->headTitle()->append($page->getTitle() ? $page->getTitle() : $page->getLabel());
		Zend_Registry::set('breadcrumb', $this->breadcrumb);
		Zend_Layout::getMvcInstance()->getView()->assign(array('pageResourceSlug'=>$page->get('resource')));
		$mvc=$page->get('mvc');
		$mvc[]='frontend';
		return array_merge(array_combine(array('controller', 'action', 'module'), $mvc), $this->extraParams);
	}

	public function assemble($data = array(), $reset = false, $encode = false, $partial = false){
		$page=$this->navigation->findOneBy('resource', $data);
		if(!$page){
			return '/';
		}
		return $page->get('uri');
	}

	private function _findPage($path){
		$page=$this->navigation->findOneBy('uri', $path);
		if($page){
			$this->breadcrumb[]=$page;
			$page->setActive(true);
			return $page;
		}
		$this->extraParams[]=str_replace('/', '', substr($path, strrpos($path, '/')));
		$path=substr($path, 0, strrpos($path, '/'));
		if($path) return $this->_findPage($path);
		return false;
	}




}