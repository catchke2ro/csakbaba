<?

class CB_Controller_Frontend_Plugin_CB extends Zend_Controller_Plugin_Abstract {

	/**
	 * @var Zend_View
	 */
	private $view;

	private $nav;

	public function routeShutdown(CB_Resource_Request $request){
		if($request->getModuleName()!='frontend') {
			Zend_Controller_Front::getInstance()->unregisterPlugin($this);
			return;
		}
		$this->nav=Zend_Registry::get('nav');
		$this->view=Zend_Layout::getMvcInstance()->getView();
	}

	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request){
		$this->_initPlaceholders();
	}

	public function postDispatch(Zend_Controller_Request_Abstract $request){
		$view=Zend_Layout::getMvcInstance()->getView();
		//$this->getResponse()->setBody($this->getResponse()->getBody());

		$this->view->placeholder('content')->append($this->getResponse()->getBody());

		if($this->_request->isDispatched()==true){
			if(Zend_Registry::isRegistered('breadcrumb')) $this->view->placeholder('content')->prepend($this->view->partial('index/breadcrumb.phtml', array('bc'=>Zend_Registry::get('breadcrumb'))));
			$this->view->headTitle()->append('csakbaba.hu');
		}


		if($request->isDispatched()){
			if(!isJson($this->getResponse()->getBody()) && $this->getRequest()->isXmlHttpRequest()){
				$this->getResponse()->setBody($this->view->messenger().$this->getResponse()->getBody());
			}
			$this->_blocks();
		}

	}

	private function _initPlaceholders(){
		$sc=(!empty($_COOKIE['stick']) && $_COOKIE['stick']=='true') ? 'stick' : '';
		$this->view->placeholder('header')->setPrefix('<div class="header"><div class="headerInner">')->setPostfix('</div></div>')->append(' ');

		$this->view->placeholder('subheader')->setPrefix('<div class="subheader"><div class="subheaderInner">')->setPostfix('</div></div>');
		$this->view->placeholder('content')->setPrefix('<div class="content animated fadeInRight '.$sc.'"><div class="contentInner">')->setPostfix('</div></div>');
		$this->view->placeholder('footer')->setPrefix('<div class="footer '.$sc.'"><div class="footerInner">')->setPostfix('</div></div>')->append(' ');

	}

	private function _blocks(){
		$this->view->placeholder('sidebar')->append($this->view->partial('index/sidebar.phtml', $this->view->getVars()));
		$this->view->placeholder('header')->append($this->view->partial('index/header.phtml', $this->view->getVars()));
		$this->view->placeholder('footer')->append($this->view->partial('index/footer.phtml', $this->view->getVars()));
		if($activeNavPage=$this->nav->findBy('active', true)){
			if(($activePage=$this->nav->getFirstParent($activeNavPage)) && $activePage->hasPages()){
				//$this->view->placeholder('subheader')->append($this->view->partial('index/submenu.phtml', array('activePage'=>$activePage, 'nav'=>$this->nav)));
			}
		}

	}



}