<?php

class UserController extends CB_Controller_Action {

	/**
	 * @var \CB\Model\User()
	 */
	private $userModel;

	public function init(){
		$this->userModel=new \CB\Model\User();
		parent::init();
	}

	public function indexAction(){
		$this->redirect($this->url('felhasznalotermekek'));
	}

	public function editAction(){
		$identity=Zend_Auth::getInstance()->getIdentity();
		$user=$this->userModel->findOneById($identity['id']);
		$userArray=get_object_vars($user);
		$form=new Frontend_Form_UserEdit();
		$form->populate($userArray);
		$form->getElement('old_email')->setValue($user->email);

		if($this->getRequest()->isPost()){
			CB_Resource_Functions::logEvent('userEditStarted');
			if($form->isValid($this->getRequest()->getPost())){
				$values=$form->getValues();
				$values=$form->processData($values, $this);
				$user->saveAll($values);
				$this->userModel->save($user);
				$this->m('Sikeres módosítás');
				CB_Resource_Functions::logEvent('userEditEnded');
				$this->redirect($this->url('adatmodositas'));
			} else {
				$this->m('Nem töltöttél ki megfelelően minden mezőt', 'error');
			}
		}

		$this->view->assign(array(
			'form'=>$form
		));
	}

	public function loginAction(){
		$form=new Frontend_Form_Login();
		$this->view->assign(array(
			'form'=>$form
		));



		if($this->getRequest()->isPost()){
			if($form->isValid($this->getRequest()->getPost())){
				if(!empty($_GET['r'])){
					$rElement=new Zend_Form_Element_Hidden('r');
					$rElement->setValue(urldecode($_GET['r']))->removeDecorator('label');
					$form->addElement($rElement);
				}
				$authAdapter=new CB_Resource_Auth($form->getElement('email')->getValue(), $form->getElement('password')->getValue());
				$result=$authAdapter->authenticate();
				$r=($form->getElement('r') ? $form->getElement('r')->getValue() : false);
				if($r==$this->url('bejelentkezes')) $r=$this->url('adatmodositas');
				$cassaSession=new Zend_Session_Namespace('cassa');
				CB_Resource_Functions::logEvent('userLogin', array('authresult'=>$result));
				switch($result->getcode()){
					case $result::SUCCESS:
						$id=Zend_Auth::getInstance()->getIdentity();
						$user=$this->userModel->findOneById($id['id']);
						$user->date_last_login=date('Y-m-d H:i:s');
						$this->userModel->save($user);
						$url=(!empty($cassaSession->id) ? $this->url('vasarlas') : ($r ? $r : $this->url('adamodositas')));
						$this->redirect($url); break;
					case $result::FAILURE:
						$this->m('A felhasználó még nem aktivált.'); break;
					default:
						$this->m('A felhasználónév vagy a jelszó nem megfelelő', 'error'); break;
				}
			}
		}
	}

	public function logoutAction(){
		CB_Resource_Functions::logEvent('userLogout');
		Zend_Auth::getInstance()->clearIdentity();
		$this->redirect('/');
	}

	public function registrationAction(){
		$form=new Frontend_Form_Registration();
		$this->view->assign(array(
			'form'=>$form
		));

		if($this->getRequest()->isPost()){
			CB_Resource_Functions::logEvent('userRegistrationStarted');
			if($form->isValid($this->getRequest()->getPost())){
				$data=$this->getRequest()->getPost();
				if($data['newsletter']){
					$mc=new CB_Resource_Mailchimp();
					$mc->subscribe($data['email'], array('NAME'=>$data['username']));
				}
				$data['activation_code']=md5($data['email'].time());
				$data['password']=md5($data['password']);
				$data['role']='user';
				$data['date_reg']=date('Y-m-d H:i:s');
				$row=$this->userModel->save($data);
				$this->emails->activation(array('user'=>$row), $this->url('aktivacio'));

				CB_Resource_Functions::logEvent('userRegistrationEnded', array('user'=>$row));
				$this->m('Sikeres regisztráció! Az aktiváláshoz szükséges linket elküldtük e-mailben a megadott címre.');
				$this->redirect($this->url('bejelentkezes').'?reg=1');
			} else {
				$this->m('Nem töltöttél ki megfelelően minden mezőt', 'error');
			}
		}
	}

	public function forgottenAction(){
		$form=new Frontend_Form_Forgotten();
		$this->view->assign(array(
			'form'=>$form
		));

		if($this->getRequest()->isPost()){
			if($form->isValid($this->getRequest()->getPost())){
				$data=$this->getRequest()->getPost();
				$user=$this->userModel->findOneByEmail($data['email']);
				if($user){
					$newPswd=substr(md5(uniqid().$data['email']), 0, 6);
					$user->password=md5($newPswd);
					$this->userModel->save($user);
					CB_Resource_Functions::logEvent('userForgottenPassword');
					$this->emails->forgotten(array('newPswd'=>$newPswd, 'user'=>$user));
				}
				$this->m('Amennyiben az e-mail címmel létezik regisztráció, elküldtünk egy ideiglenes jelszót az e-mail címedre. Az új jelszóval be tudsz lépni, ezután változtasd meg a jelszavad!', 'message');
				$this->redirect($this->url('bejelentkezes'));
			} else {
				$this->m('Nem töltöttél ki megfelelően minden mezőt', 'error');
			}
		}
	}

	public function activationAction(){
		if(($code=$this->getRequest()->getParam(0)) && ($user=$this->userModel->findOneBy('activation_code', $code))){
			CB_Resource_Functions::logEvent('userActivation');
			if($this->getRequest()->getParam(1) && $this->getRequest()->getParam(2) && $this->getRequest()->getParam(1)==1){
				$user->email=$this->getRequest()->getParam(2);
				$this->userModel->save($user);
				$this->m('Sikeresen megváltoztattad az e-mail címedet');
				$this->redirect($this->url('adatmodositas'));
			} else {
				$user->active=1;
				$this->userModel->save($user);
				$this->m('Sikeres aktiváció, jelentkezz be!', 'message');
			}
		} else {
			$this->m('Hibás aktivációs kód', 'error');
		}
		$this->redirect($this->url('bejelentkezes'));
	}


	public function chargeAction(){
		$paymentModel=new \CB\Model\Payment();
		if(!empty($_GET['i']) && $_GET['i']==1 && !empty($_GET['pid'])){
			$payment=$paymentModel->findOneBy('pid', $_GET['pid']);
			if($payment && $payment->user->id==$this->user->id && !empty($payment->invoice_data['invoice_number']) && file_exists(APPLICATION_PATH.'/../tmp/invoices/'.str_replace('/', '_', $payment->invoice_data['invoice_number']).'.pdf')){
				header('Content-type: application/pdf');
				header('Content-Disposition: attachment; filename='.str_replace('/', '_', $payment->invoice_data['invoice_number']).'.pdf');
				echo file_get_contents(APPLICATION_PATH.'/../tmp/invoices/'.str_replace('/', '_', $payment->invoice_data['invoice_number']).'.pdf');
				die();
			}
		}
		$form=new Frontend_Form_Charge();
		$form->initFields();

		$payments=$paymentModel->find(array('conditions'=>array('user._id'=>new \MongoId($this->user->id)), 'sort'=>'date DESC'));
		if($this->_request->isPost()){
			if($form->isValid($this->_request->getPost())){
				$payment=new CB_Resource_Payment(null, $this);
				$payment->newPayment(array('amount'=>$form->getElement('amount')->getValue()));
				$payment->doPayment();
				$this->m('A fizetés sikertelen volt. Kérlek próbáld meg újra, vagy keress meg minket', 'error');
			}
		}
		$this->view->assign(array(
			'user'=>$this->user,
			'form'=>$form,
			'payments'=>$payments
		));
	}


	public function ordersAction(){
		$orderModel=new \CB\Model\Order();

		$identity=Zend_Auth::getInstance()->getIdentity();
		$user=$this->userModel->findOneById($identity['id']);

		$userOrdersBuy=$orderModel->find(array('conditions'=>array('user._id'=>new \MongoId($user->id))));

		$userOrdersSell=$orderModel->find(array('conditions'=>array('shop_user._id'=>new \MongoId($user->id))));
		$categoryTree=Zend_Registry::get('categories');
		$this->view->assign(array(
			'userOrdersBuy'=>$userOrdersBuy,
			'userOrdersSell'=>$userOrdersSell,
			'categoryTree'=>$categoryTree
		));
	}


	public function profileAction(){
		$ep=$this->getRequest()->getExtraParams();
		$userModel=new \CB\Model\User();
		if(empty($ep) || !($user=$userModel->findOneByUsername(urldecode($ep[0])))) $this->redirect('/');

		$userProducts=$user->getProducts(false, 10, array(1,2));
		$categoryTree=Zend_Registry::get('categories');
		$this->view->assign(array('userProducts'=>$userProducts, 'categoryTree'=>$categoryTree));

		$this->view->assign(array(
			'profilUser'=>$user
		));
	}

	public function favouritesAction(){

		$identity=Zend_Auth::getInstance()->getIdentity();
		$user=$this->userModel->findOneById($identity['id']);
		if(!empty($_GET['id'])){
			CB_Resource_Functions::logEvent('userFavourite');
			$productModel=new \CB\Model\Product();
			if(($product=$productModel->findOneById($_GET['id']))){
				(!empty($_GET['remove']) ? $user->favourites->removeElement($product) : (!$user->favourites->contains($product) ? $user->favourites->add($product) : ''));
				$this->userModel->save($user);
				$this->redirect($this->url('kedvencek'));
			}
		}

		$categoryTree=Zend_Registry::get('categories');
		$this->view->assign(array(
			'categoryTree'=>$categoryTree,
			'favourites'=>$user->favourites->toArray()
		));
	}


	public function ratingformAction(){
		$this->getHelper('layout')->disableLayout();
		if(empty($_GET['oid']) || !isset($_GET['seller'])) return false;
		$ratingForm=new Frontend_Form_Rating();
		$ratingForm->oid=$_GET['oid'];
		$ratingForm->initFields();
		$this->view->assign(array('ratingForm'=>$ratingForm));

		if($this->getRequest()->isPost()){
			if($ratingForm->isValid($this->getRequest()->getPost())){
				$orderModel=new \CB\Model\Order();
				$order=$orderModel->findOneById($_GET['oid']);
				CB_Resource_Functions::logEvent('userRatingStarted', array('order'=>$order));
				$ratingModel=new \CB\Model\Rating();
				$rating=new \CB\Rating();
				$rating->saveAll($ratingForm->getValues());
				$rating->date=date('Y-m-d H:i:s');
				$rating->seller=(bool) $_GET['seller'];
				$rating->product=$order->product;
				$rating->product->user=null;
				$ratingModel->save($rating);

				$field=$rating->seller ? 'shop_user_rating' : 'user_rating';

				$order->$field=$rating;
				$orderModel->save($order);
				CB_Resource_Functions::logEvent('userRatingEnded', array('order'=>$order));
				$this->getHelper('viewRenderer')->setNoRender(true);
			}
		}
	}



	public function sloginAction(){
		$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
		if(empty($_GET['s']) || !in_array($_GET['s'], array('fb','gp'))) $this->redirect('/');
		$s=$_GET['s'];

		CB_Resource_Functions::logEvent('userSocialLogin');
		$sloginSession=new Zend_Session_Namespace('slogin');
		if(!empty($_GET['r'])){
			$sloginSession->r=$_GET['r'];
		}
		switch($s){
			case 'fb':
				$fbr=new CB_Resource_Facebook();
				$suser=$fbr->login();
				break;
			case 'gp':
				$gpr=new CB_Resource_Google();
				$suser=$gpr->login();
				break;
			default: $this->redirect('/'); break;
		}

		$this->m('Sikeres bejelentkezés');
		$authAdapter=new CB_Resource_Auth();
		$authAdapter->slogin($s, $suser);
		$url=(!empty($sloginSession->r) ? $sloginSession->r : $this->url('adatmodositas'));
		$this->redirect($url);
	}



	public function paymentredirectAction(){
		$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
		l('redirect: '.print_r(array('get'=>$_GET, 'post'=>$_POST), true));
		if((empty($_GET['p']) || empty($_GET['ref']) || empty($_GET['status']))){
			header("HTTP/1.1 400 Bad Request");
			exit();
		}
		$payment=new CB_Resource_Payment($_GET['ref'], $this);
		$payment->redirect();
	}

	public function paymenthookAction(){
		$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
		l('hook: '.print_r(array('get'=>$_GET, 'post'=>$_POST), true));
		if((empty($_GET['p']) || empty($_POST['payment_id']) || empty($_POST))){
			header("HTTP/1.1 400 Bad Request");
			exit();
		}
		$payment=new CB_Resource_Payment($_POST['payment_id'], $this);
		$payment->hook();
	}



}
