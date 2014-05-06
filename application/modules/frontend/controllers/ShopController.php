<?php

class ShopController extends CB_Controller_Action {

	/**
	 * @var \CB\Model\User()
	 */
	public $userModel;

	/**
	 * @var \CB\Model\Product()
	 */
	public $productModel;


	public function init(){
		$this->userModel=new \CB\Model\User();
		$this->productModel=new \CB\Model\Product();
		$this->view->assign(array('uploadPrice'=>Zend_Registry::get('uploadPrice')));
		parent::init();
	}

	public function userproductsAction(){
		if(!$this->user->isValid()){
			$this->m('Termékek feltöltéséhez minden szükséges adatot meg kell adnod!', 'error');
			$this->redirect($this->url('adatmodositas'));
		}
		$products=$this->user->getProducts(false, 10, array(0,1,2,3));

		$categoryTree=Zend_Registry::get('categories');
		$categoryOptions=$categoryTree->getComboList();
		$disabledOptions=array();
		foreach($categoryOptions as $id=>$option){ if(strpos($id, 'x')!==false) $disabledOptions[]=$id; }
		$categorySelect=new Zend_Form_Element_Select('category_id');
		$categorySelect->setMultioptions(array(''=>'Válassz kategóriát!')+$categoryOptions)->setAttrib('disable', $disabledOptions);

		$this->view->assign(array(
			'statusCodes'=>$this->statusCodes,
			'products'=>$products,
			'categorySelect'=>$categorySelect,
			'categoryTree'=>$categoryTree,
			'promoteOptions'=>Zend_Registry::get('promoteOptions'),
			'promoteAllOptions'=>Zend_Registry::get('promoteAllOptions')
		));
	}

	public function userproducteditformAction(){
		if($this->getRequest()->isXmlHttpRequest())	$this->getHelper('layout')->disableLayout();
		$category_id=$this->_request->isPost() ? $this->_request->getPost('category_id') : $this->_request->get('category_id');
		$categoryTree=Zend_Registry::get('categories');
		$category=$categoryTree->_singleArray[$category_id];
		$options=$category->props;

		$form=new Frontend_Form_ProductEdit();
		$form->category=$category;
		$form->options=$options;
		$form->deliveryOptions=$this->deliveryOptions;
		$form->initFields();

		$productid=$this->getRequest()->get('product_id');
		if($productid=='false') $productid=false;
		if(strpos($productid, 'COPY_')!==false){
			$product=$this->productModel->findOneById(str_replace('COPY_', '', $productid));
			if($product) $form->populate(get_object_vars($product));
			$form->getElement('id')->setValue('');
			$form->getElement('images')->setValue('');
			$productid=false;
		}
		if($productid){
			$product=$this->productModel->findOneById($productid);
			if($product) $form->populate(get_object_vars($product));
		}

		if(!$productid && !$this->_request->getPost('id')){
			$form->removeElement('id');
			$form->setDescription('A termék feltöltésének díja '.Zend_Registry::get('uploadPrice').' Ft, amit az egyenlegedből vonunk le.');

		}

		if($this->_request->isPost()){
			if($form->isValid($this->_request->getPost())){

				$values=$form->processData($form->getValues(), $this);

				$email=false;
				if(!empty($values['id'])){
					CB_Resource_Functions::logEvent('userProductEditStarted');
					$product=$this->productModel->findOneById($values['id']);
					$product->saveAll($values);
				} else {
					CB_Resource_Functions::logEvent('userProductAddStarted');
					$values['code']=uniqid('CSB');
					$email=true;
					$this->user->balance=intval($this->user->balance)-Zend_Registry::get('uploadPrice');
					if($this->user->balance <= (2*Zend_Registry::get('uploadPrice'))) $this->emails->balanceLow(array('user'=>$this->user));
					$this->userModel->save($this->user);
					$this->m('Sikeresen feltöltötted a terméked a csakbaba.hu oldalon! A terméked hamarosan megjelenik a többi termék között és láthatod a főoldalon a legfrissebben feltöltött termékeknél! Köszönjük a feltöltést! További jó börzézést!', 'message');
				}
				$product=$this->productModel->save(isset($product) ? $product : $values);
				CB_Resource_Functions::logEvent('userProductEditAddEnded', array('product'=>$product));
				$this->view->assign(array(
					'categoryTree'=>Zend_Registry::get('categories'),
					'product'=>$product, 'back'=>false, 'fix'=>false, 'userfunctions'=>true, 'statusCodes'=>$this->statusCodes, 'extraClass'=>'status'.$product->status
				));
				$this->getHelper('viewRenderer')->setNoController(true);
				$this->_helper->viewRenderer('market/product-partial');

				if($email) $this->emails->productAdd(array('product'=>$product));
			} else {
				$this->_response->setHttpResponseCode(400);
			}
		} else {
			$form->populate(array('category_id'=>$category->id, 'user_id'=>$this->user->id));
		}
		$this->view->assign(array(
			'form'=>$form
		));
	}


	public function userproductpreviewAction(){
		$categoryTree=Zend_Registry::get('categories');

		if(!empty($_GET['images'])) $_GET['images']=json_decode($_GET['images'], true);
		$form=new Frontend_Form_ProductEdit();
		$values=$form->processData($_GET, $this);

		$product=new \CB\Product();
		$product->saveAll($values);
		$product->date_added=new DateTime();
		$product->name=$product->name ? $product->name : 'Termék neve';
		$product->category=$_GET['category_id'];
		$product->type=$product->type ? $product->type : 'egyeb';
		$product->price=$product->price ? $product->price : 0;
		$product->desc=$product->desc ? $product->desc : 'Leírás';



		$this->view->assign(array(
						'product'=>$product,
						'categoryTree'=>$categoryTree,
						'deliveryOptions'=>$this->deliveryOptions,
		));
		$this->getHelper('layout')->setLayout('ajax');
		$this->getHelper('viewRenderer')->setNoController(true);
		$this->_helper->viewRenderer('market/productpreview');
	}


	public function userproductdeleteAction(){
		$this->getHelper('layout')->disableLayout();
		$id=$this->getRequest()->getParam('productid');
		$product=$this->productModel->findOneById($id);
		if($product){
			CB_Resource_Functions::logEvent('userProductDelete', array('product'=>$product));
			$product->deleted=true;
			$this->productModel->save($product);
			$this->m('Sikeresen törölted a terméket!');
		}
		return true;
	}

	public function userrenewAction(){
		if($this->getRequest()->isXmlHttpRequest())	$this->getHelper('layout')->disableLayout();
		if(empty($_GET['product_id'])) die();
		$product=$this->productModel->findOneById($_GET['product_id']);
		if(!($product && $product->user->get()->id==$this->user->id)) $this->m('Nincs ilyen termék', 'error');
		else {
			$this->view->assign(array('product'=>$product));

			if(!empty($_GET['renew']) && $_GET['renew']=='true'){
				CB_Resource_Functions::logEvent('userProductRenew', array('product'=>$product));
				$this->user->balance=intval($this->user->balance)-Zend_Registry::get('uploadPrice');
				if($this->user->balance <= (2*Zend_Registry::get('uploadPrice'))) $this->emails->balanceLow(array('user'=>$this->user));
				$this->userModel->save($this->user);

				$product->status=1;
				$product->date_period=date('Y-m-d H:i:s');
				$this->productModel->save($product);
			}
		}
	}

	public function userpromoteAction(){
		if($this->getRequest()->isXmlHttpRequest())	$this->getHelper('layout')->disableLayout();
		if(empty($_GET['product_id'])) $this->redirect($this->url('felhasznalotermekek'));
		$form=new Frontend_Form_Promote();

		$product=$this->productModel->findOneById($_GET['product_id']);

		if(!($product && $product->user->get()->id==$this->user->id) && $_GET['product_id']!='all') $this->m('Nincs ilyen termék', 'error');
		else {
			$all=$_GET['product_id']=='all';
			if($all){
				$form->options=Zend_Registry::get('promoteAllOptions');
				$form->initFields();
				$form->setProduct($this->user);
			} else {
				$this->view->assign(array('product'=>$product));
				$form->options=Zend_Registry::get('promoteOptions');
				$form->initFields();
				$form->setProduct($product);
			}
			if($this->_request->isPost()){
				CB_Resource_Functions::logEvent('userProductPromoteStarted', array('product'=>$product));
				$this->getHelper('viewRenderer')->setNoRender(true);
				if(!empty($_POST['types'])){
					if($all) $promote=is_array($this->user->promotes) ? $this->user->promotes : array();
					else $promote=is_array($product->promotes) ? $product->promotes : array(); $price=0;
					$price=0;
					foreach($_POST['types'] as $type){
						if(!empty($promote[$type]) && $promote[$type]>time()) continue;
						$price+=$form->prices[$type];
						$promote[$type]=strtotime('+1 week');
					}
					$this->user->balance-=$price;
					if($this->user->balance <= (2*Zend_Registry::get('uploadPrice'))) $this->emails->balanceLow(array('user'=>$this->user));
					($all) ? $this->user->promotes=$promote : $product->promotes=$promote;

					$this->userModel->save($this->user);
					if(!$all) $this->productModel->save($product);

					CB_Resource_Functions::logEvent('userProductPromoteEnded', array('product'=>$product));

					$this->m('Sikeresen kiemelted a terméket 1 hétre!');
					if(!$all){
						$this->view->assign(array(
										'categoryTree'=>Zend_Registry::get('categories'),
										'product'=>$product, 'back'=>false, 'fix'=>false, 'userfunctions'=>true, 'statusCodes'=>$this->statusCodes, 'extraClass'=>'status'.$product->status
						));
					}
					$this->getHelper('viewRenderer')->setNoRender(false);
					$this->getHelper('viewRenderer')->setNoController(true);
					$this->_helper->viewRenderer('market/product-partial');

				}
			}
		}


		$this->view->assign(array(
			'form'=>$form
		));
	}

	public function categoryselectlistAction(){
		$this->getHelper('layout')->disableLayout();
		$parent=$this->getRequest()->getParam('parent');
		$categories=Zend_Registry::get('categories');
		$selects=array();
		$values=array();

		foreach($categories->_multiArray as $id=>$cat){
			$selects['selcat0'][$id]=$cat->name;
		}
		$main=$categories;
		if(!empty($parent)){
			foreach(explode(';', $parent) as $i=>$id){
				$values['selcat'.$i]=$id;
				$main=property_exists($main, 'children') ? $main->children[$id] : $main->_multiArray[$id];
				if(empty($main->children)) die('LOAD');
				foreach($main->children as $id=>$cat){
					$selects['selcat'.($i+1)][$id]=$cat->name;
				}
			}
		}

		$this->view->assign(array(
			'selects'=>$selects,
			'values'=>$values
		));
	}



}
