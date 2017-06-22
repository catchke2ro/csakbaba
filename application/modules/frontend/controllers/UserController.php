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
		
		if(!empty($userArray['desc'])){
			$userArray['desc'] = strip_tags($userArray['desc']);
		}
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

        $redirectSession = new Zend_Session_Namespace('loginRedirect');

		if($this->getRequest()->isPost()){
			if($form->isValid($this->getRequest()->getPost())){
                if($form->getElement('popupurl')->getValue()){
                    $redirectSession->r = $form->getElement('popupurl')->getValue();
                }
                
				$authAdapter=new CB_Resource_Auth($form->getElement('email')->getValue(), $form->getElement('password')->getValue());
				$result=$authAdapter->authenticate();
				$cassaSession=new Zend_Session_Namespace('cassa');
				CB_Resource_Functions::logEvent('userLogin', array('authresult'=>$result));
				switch($result->getcode()){
					case $result::SUCCESS:
						$id=Zend_Auth::getInstance()->getIdentity();
						$user=$this->userModel->findOneById($id['id']);
						$user->date_last_login=date('Y-m-d H:i:s');
						$this->userModel->save($user);

						$url=(!empty($cassaSession->id) ? $this->url('vasarlas') : (!empty($redirectSession->r) ? $redirectSession->r : $this->url('adamodositas')));

                        $redirectSession->unsetAll();

						$this->redirect($url); break;
					case $result::FAILURE:
						$this->m('A felhasználó még nem aktivált.'); break;
					default:
						$this->m('A felhasználónév vagy a jelszó nem megfelelő', 'error'); break;
				}
			}
		} else {

            if($this->user){
                $this->redirect($this->url('adatmodositas'));
                return;
            }


            if($this->g('r')){
                $r = $this->g('r');
                $redirectSession->r = $r;
            } else {
                
            }

			if(!empty($_GET['r']) && $_GET['r'] == $this->url('felhasznalotermekek')){
				$this->m('Termékfeltöltéshez lépj be felhasználói profilodba, ha még nem regisztráltál, regisztrálj oldalunkra, hogy megnyithasd virtuális asztalodat és feltölthesd holmijaidat a borzére.');
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

        $this->view->minifyHeadScript()->appendFile('https://www.google.com/recaptcha/api.js');

		if($this->getRequest()->isPost()){
			CB_Resource_Functions::logEvent('userRegistrationStarted');
			if($form->isValid($this->getRequest()->getPost())){
				$data = $form->getValues();
				
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
				$rating->product=clone $order->product;
				$rating->product->user=null;
				$ratingModel->save($rating);

				$field=$rating->seller ? 'shop_user_rating' : 'user_rating';

				$order->$field=$rating;
				$orderModel->save($order);
				CB_Resource_Functions::logEvent('userRatingEnded', array('order'=>$order));

				$notifyUser=$this->user->id==$order->user->id ? $order->shop_user : $order->user;
				CB_Resource_Functions::addFeed('newRating', $notifyUser->get(), $rating->product);
				$this->getHelper('viewRenderer')->setNoRender(true);
			} else {
				$this->_response->setHttpResponseCode(400);
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


		$authAdapter=new CB_Resource_Auth();
 		$result=$authAdapter->slogin($s, $suser);
		if($result===false) $this->m('A fiókoddal nem lehet bejelentkezni (pl. nem megfelelő e-mail cím miatt). Ellenőrizd e-mail címed a fiókodban!', 'error');
		else $this->m('Sikeres bejelentkezés');
        
		$url=(!empty($sloginSession->r) ? $sloginSession->r : $this->url('adatmodositas'));
        
        $redirectSession = new Zend_Session_Namespace('loginRedirect');
        $cassaSession = new Zend_Session_Namespace('cassa');
        
        $url=(!empty($cassaSession->id) ? $this->url('vasarlas') : (!empty($redirectSession->r) ? $redirectSession->r : $this->url('adamodositas')));
        
        $redirectSession->unsetAll();
        
		$this->redirect($url);
	}



	public function paymentredirectAction(){
		$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
		l('redirect: '.print_r(array('get'=>$_GET, 'post'=>$_POST), true));
		if((empty($_GET['paymentId']))){
			header("HTTP/1.1 400 Bad Request");
			exit();
		}
		$payment=new CB_Resource_Payment($_GET['paymentId'], $this);
		$payment->redirect();
	}

	public function paymenthookAction(){
		$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
		l('hook: '.print_r(array('get'=>$_GET, 'post'=>$_POST), true));

		if((empty($_GET['paymentId']))){
			header("HTTP/1.1 400 Bad Request");
			exit();
		}
		$payment=new CB_Resource_Payment($_GET['paymentId'], $this);
		$payment->hook();
	}




	public function feedcountAction(){
		$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();
		if(!$this->user) return;

		$feedModel=new \CB\Model\Feed();
		$feeds=$feedModel->find(array('conditions'=>array('user_id'=>$this->user->id, 'read'=>false)));
		echo count($feeds);
	}


	public function feedsAction(){
		$this->getHelper('layout')->disableLayout();
		if(!$this->user) return;

		$feedModel=new \CB\Model\Feed();
		$unreadFeeds=$feedModel->find(array('conditions'=>array('user_id'=>$this->user->id, 'read'=>false)));
		$readFeeds=$feedModel->find(array('conditions'=>array('user_id'=>$this->user->id, 'read'=>true), 'limit'=>5));
		$feeds=array_merge($unreadFeeds, $readFeeds);
		usort($feeds, function($a, $b){
			return $a->date->getTimestamp() < $b->date->getTimestamp() ? 1 : -1;
		});
		$this->view->assign(array(
			'feeds'=>$feeds,
			'feedTypes'=>Zend_Registry::get('feedTypes')
		));
	}

	public function feedreadAction(){
		$feedModel=new \CB\Model\Feed();
		if(!($feed=$feedModel->findOneById($this->getParam('fid')))) exit();

		$feed->read=true;
		$feedModel->save($feed);
		$this->forward('feeds');
	}



}
