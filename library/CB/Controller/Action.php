<?

abstract class CB_Controller_Action extends Zend_Controller_Action {

	/**
	 * @var CB_Resource_Mail
	 */
	public $mail;

	/**
	 * @var CB_Resource_Emails
	 */
	public $emails;

	/**
	 * @var Zend_Controller_Action_Helper_FlashMessenger
	 */
	public $fm;

	/**
	 * @var CB_Resource_Navigation
	 */
	public $nav;

	/**
	 * @var CB_Resource_Functions
	 */
	public $functions;

	/**
	 * @var \CB\User
	 */
	public $user;

	public $statusCodes;

	public $types=array(
		'fiu'=>'Fiú',
		'lany'=>'Lány',
		'felnott'=>'felnott',
		'egyeb'=>'Egyéb',
		''=>'Egyéb'
	);

	public $deliveryOptions;

	function init(){
		$this->mail=new CB_Resource_Mail('UTF-8');
		$this->mail->init();


		$this->_helper = new \Zend_Controller_Action_HelperBroker($this);
		$this->fm=$this->_helper->getHelper('Messenger');

		$this->functions=new CB_Resource_Functions();
		$this->nav=Zend_Registry::get('nav');

		$this->statusCodes=Zend_Registry::get('statusCodes');
		$this->deliveryOptions=Zend_Registry::get('deliveryOptions');

		//$contactForm=new Frontend_Form_Contact();
		//$this->view->assign(array('contactForm'=>$contactForm));


		if($identity=Zend_Auth::getInstance()->getIdentity()){
			$userModel=new \CB\Model\User();
			$this->user=$userModel->findOneById($identity['id']);
			Zend_Registry::set('user', $this->user);
		} else {
			$this->user=false;
		}
		$this->emails=new CB_Resource_Emails($this->user);

		$this->_navProcess();

		$this->_initVars();
		$this->_head();
		$this->_search();

	}

	private function _search(){
		if($this->_request->isPost() && $q=$this->_request->getPost('q')){
			$searchSession=new Zend_Session_Namespace('search');
			$searchSession->setExpirationSeconds(1800);
			$searchSession->q=$q;
			$searchSession->category_id=$this->_request->getPost('category_id');
			$this->redirect($this->url('kereses'));
		}
	}

    public function g($var = null){
        return !empty($_GET[$var]) ? $_GET[$var] : null;
    }

	public function m(){
		$args=func_get_args();
		call_user_func_array(array($this->fm, 'messenger'), $args);
	}

	public function url($id){
		$nav=Zend_Registry::get('nav');
		$page=$nav->findBy('resource', $id);
		return $page ? $page->get('uri') : '/';
	}

	private function _head(){
		$color=!empty($_COOKIE['color']) ? $_COOKIE['color'] : 'brown';
		if($color=='green') $color='brown';
		$this->view->assign(array('color'=>$color));

		$this->view->minifyHeadLink()
						->appendStylesheet('/stylesheets/css/jquery/jquery.fileupload-ui.css')
						->appendStylesheet('/stylesheets/css/jquery/jquery.magnific.css')
						->appendStylesheet('/stylesheets/css/jquery/slick.css')
						->appendStylesheet('/stylesheets/css/jquery/slick-theme.css')
						->appendStylesheet('/stylesheets/css/jquery/select2.min.css')
						->appendStylesheet('/stylesheets/css/jquery/jquery-ui.css')
						->appendStylesheet('/stylesheets/css/animate.css')
						->appendStylesheet('/stylesheets/css/global_'.$color.'.css');
		$this->view->minifyHeadScript()
						->appendFile('/js/jquery/jquery.min.js')
						->appendFile('/js/raf.js')
						->appendFile('/js/jquery/jquery-ui.min.js')
						->appendFile('/js/jquery/jquery-ui.touch.min.js')
						->appendFile('/js/jquery/jquery.cookie.js')
						->appendFile('/js/jquery/jquery.touchSwipe.min.js')
						->appendFile('/js/jquery/jquery.magnific.min.js')
						->appendFile('/js/jquery/fileupload/jquery.fileupload.js')
						->appendFile('/js/jquery/fileupload/jquery.fileupload-ui.js')
						->appendFile('/js/jquery/fileupload/jquery.fileupload-process.js')
						->appendFile('/js/jquery/fileupload/jquery.fileupload-validate.js')
						->appendFile('/js/jquery/clamp.min.js')
						->appendFile('/js/jquery/slick.min.js')
						->appendFile('/js/jquery/jquery.maskedinput.min.js')
						->appendFile('/js/jquery/jquery.imgpreview.js')
						->appendFile('/js/ckfrontend/ckeditor.min.js')
						->appendFile('/js/ckfrontend/adapters/jquery.min.js')
						->appendFile('/js/select2/select2.full.min.js')
                        ->appendFile('/js/functions.js')
                        ->appendFile('/js/scripts.js')
						->appendFile('/js/home.js')
						->appendFile('/js/products.js')
						->appendFile('/js/usershop.js')
						->appendFile('/js/uploader.js')
		;
		$this->view->headTitle()->setSeparator(' | ');
		$this->view->headMeta()
						->setName('description', 'A csakbaba.hu egy virtuális börze, vásár, amely összehozza az eladókat a vásárlókkal, és online „asztalt” biztosít a gondtalan adás-vételhez.')
						->appendName('viewport', 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no');
	}



	private function _initVars(){
		if(!$this->user){
			$loginform=new Frontend_Form_Login();
            $loginform->getElement('popupurl')->setValue($this->_request->getUri());
			$this->view->assign(array('loginform'=>$loginform));
		}

		$mobileDetect=new CB_Resource_MobileDetect();
		$htmlClass=($mobileDetect->isMobile() || $mobileDetect->isTablet()) ? 'mobile' : 'desktop';

		$bootstrap = $this->getInvokeArg('bootstrap');
		$userAgent = $bootstrap->getResource('useragent');
		$device=$userAgent->getDevice();
		$htmlClass.=' '.str_replace(' ', '', strtolower($device->getBrowser().reset(explode('.', $device->getBrowserVersion()))));
		if(strpos($device->getUserAgent(), 'Trident/')!==false) $htmlClass.=' internetexplorer11';



		$categoryTree=Zend_Registry::get('categories');
		$catMultiArray=$categoryTree->_multiArray;

		$searchForm=new Frontend_Form_Search();

		$this->view->assign(array(
			'user'=>$this->user,
			'searchForm'=>$searchForm,
			'request'=>$this->_request,
			'types'=>$this->types,
			'catMultiArray'=>$catMultiArray,
			'htmlClass'=>$htmlClass,
			'nav'=>Zend_Registry::get('nav')
		));
	}

	private function _navProcess(){
		$nav=Zend_Registry::get('nav');
		if($this->user && !$this->user->isValid()){
			$pages=array();
			$pages[]=$nav->findOneBy('resource', 'felhasznalotermekek');
			$pages[]=$nav->findOneBy('resource', 'felhasznalo');
			foreach($pages as $page){
				$notValid=$page->get('notValid');
				if(!empty($notValid['label'])) $page->setLabel($notValid['label']);
				if(!empty($notValid['url'])) $page->setUri($notValid['url']);
			}
		}
	}

}