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

	/**
	 * @var \CB\Model\Service()
	 */
	public $serviceModel;


	public function init(){
		$this->userModel=new \CB\Model\User();
		$this->productModel=new \CB\Model\Product();
		$this->serviceModel=new \CB\Model\Service();
		$this->view->assign(array('uploadPrice'=>Zend_Registry::get('uploadPrice')));
		parent::init();
	}

	public function userproductsAction(){
        if(isset($_GET['uj'])){
            $hash = 'uj';
            if($this->g('cid')){
                $hash.='__'.$this->g('cid');
            }
            $this->redirect($this->_request->getUri().'#'.$hash);
            return;
        }
		$products=$this->user->getProducts(false, 10, array(0,1,2,3));
		$activeProducts=array_filter($products, function($item){
			return $item->status==1;
		});

		$categoryTree=Zend_Registry::get('categories');

		$this->view->assign(array(
			'statusCodes'=>$this->statusCodes,
			'products'=>$products,
			'activeProducts'=>$activeProducts,
			'categoryTree'=>$categoryTree,
			'promoteOptions'=>Zend_Registry::get('promoteOptions'),
			'promoteAllOptions'=>Zend_Registry::get('promoteAllOptions'),
            'initNew'=>isset($_GET[''])
		));
	}
	
	public function userservicesAction(){
		if(isset($_GET['uj'])){
			$hash = 'uj';
			if($this->g('cid')){
				$hash.='__'.$this->g('cid');
			}
			$this->redirect($this->_request->getUri().'#'.$hash);
			return;
		}
		$services=$this->user->getServices(false, 10, array(0,1,2,3));
		$activeServices=array_filter($services, function($item){
			return $item->status==1;
		});
		
		$this->view->assign(array(
			'statusCodes'=>$this->statusCodes,
			'services'=>$services,
			'activeServices'=>$activeServices,
			'promoteOptions'=>Zend_Registry::get('promoteOptions'),
			'promoteAllOptions'=>Zend_Registry::get('promoteAllOptions'),
			'initNew'=>isset($_GET[''])
		));
	}



    public function userproducteditAction(){
        /**
         * @var $categoryTree CB_Array_Categories
         */
        $this->getHelper('layout')->setLayout('ajax');

        $categoryTree=Zend_Registry::get('categories');

        $category = false;
        if(!empty($_GET['cid'])) $category = $categoryTree->getById($_GET['cid']);
        if(!empty($_GET['category_id'])) $category = $categoryTree->getById($_GET['category_id']);

        $product = false;
        if($this->g('product_id')) $product = $this->productModel->findOneById(str_replace('COPY_', '', $this->g('product_id')));

        /**
         * @var $select Zend_Form_Element_Select
         */
        $selects = [];
        foreach($categoryTree->_multiArray as $key=>$value){
            $select = $categoryTree->getCombo([
                'rootChar'=>'',
                'levelChar'=>'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
                'parent_id'=>$key
            ]);
            if($category && $category->getMainCatId() == $key) $select->setValue($category->id);
            $select->setAttrib('class', $select->getAttrib('class') . ' select2 showSearch');
            $selects[$key] = $select;
        }



        $this->view->assign([
            'selects'=>$selects,
            'category'=>$category,
            'product'=>$product,
            'product_id'=>$this->g('product_id')
        ]);

    }
	
	public function userserviceeditAction(){
		/**
		 * @var $categoryTree CB_Array_Categories
		 */
		$this->getHelper('layout')->setLayout('ajax');
		
		$service = false;
		if($this->g('service_id')) $service = $this->serviceModel->findOneById(str_replace('COPY_', '', $this->g('service_id')));
		
		
		$this->view->assign([
			'service'=>$service,
			'service_id'=>$this->g('service_id')
		]);
		
	}

	public function userproducteditformAction(){
        /**
         * @var $categoryTree CB_Array_Categories
         */
		$this->getHelper('layout')->disableLayout();

        $categoryTree=Zend_Registry::get('categories');


		$category=$categoryTree->getById($this->_request->isPost() ? $this->_request->getPost('category_id') : $this->g('category_id'));

		$form=new Frontend_Form_ProductEdit();
		$form->initFields($category, $this->deliveryOptions);

		$productid=$this->g('product_id');
        $copy = false;

		if(strpos($productid, 'COPY_')!==false){
            $form->setProduct($this->productModel->findOneById(str_replace('COPY_', '', $productid)), true);
            $copy = true;
			$productid=false;
		}
        if($productid){
			$form->setProduct($this->productModel->findOneById($productid));
		}

		if(!$productid && !$this->_request->getPost('id')){
			$form->removeElement('id');
			$form->processDescription($this->user);
		}

		if($this->_request->isPost()){
			if($form->isValid($this->_request->getPost())){

                $values = $form->getValues();

                if(!empty($values['id'])){
                    $product=$this->productModel->findOneById($values['id']);
                }

                $product = isset($product) ? $product : new \CB\Product();
                list($promoted) = $product->fromEditForm($form, $this->user);


                if($this->user->balance < 0){
                    $this->m('Nincs elég pénz az egyenlegeden!', 'error');
                    $this->_response->setHttpResponseCode(400);
                } else {
                    $email=false;
                    if(empty($values['id'])){
                        $email=true;
                        $this->m('Sikeresen feltöltötted a terméked a csakbaba.hu oldalon! A terméked hamarosan megjelenik a többi termék között és láthatod a főoldalon a legfrissebben feltöltött termékeknél! Köszönjük a feltöltést! További jó börzézést!', 'message');
                    } else {
                        $this->m('Sikeres szerkesztés', 'message');
                    }

                    if($promoted === true){
                        $this->m('Sikeresen kiemelted a terméket 1 hétre!', 'message');
                    }

                    CB_Resource_Functions::logEvent('userProductEditAddEnded', array('product'=>$product));
                    $this->productModel->save($product);
                    $this->userModel->save($this->user);
                    if($email) $this->emails->productAdd(array('product'=>$product));

                    die();
                }

			} else {
				$this->_response->setHttpResponseCode(400);
			}
		} else {
			$form->populate(array('category_id'=>$category->id, 'user_id'=>$this->user->id));
		}
		$this->view->assign(array(
			'form'=>$form,
            'copy'=>$copy,
            'categoryTree'=>$categoryTree
		));
	}

	public function userproductdeleteAction(){
		$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender(true);
		if(($product=$this->productModel->findOneById($this->g('id')))){
			CB_Resource_Functions::logEvent('userProductDelete', array('product'=>$product));
			$product->deleted=true;
			$this->productModel->save($product);
			$this->m('Sikeresen törölted a terméket!');
		}
		return true;
	}

	public function userrenewAction(){
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);
        if(($product=$this->productModel->findOneById($this->g('id')))){
            CB_Resource_Functions::logEvent('userProductRenew', array('product'=>$product));

            if($this->user->getActiveProductsCount() >= Zend_Registry::get('freeUploadLimit')){
                $this->user->modifyBalance(-Zend_Registry::get('uploadPrice'));
            }
            $this->userModel->save($this->user);

            $product->status=1;
            $product->date_period=date('Y-m-d H:i:s');
            $this->productModel->save($product);

            $this->m('Sikeresen megújítottad a terméket!');
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


	public function autocompleteAction(){
		$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender(true);

		$categoryTree=Zend_Registry::get('categories');

		$q=!empty($_GET['term']) ? $_GET['term'] : '';
		$results=$this->productModel->search($q);
		$json=array();
		foreach($results as $result){
			$product=$result['product'];
			$uri=$this->url('piac').$categoryTree->getUri($product->category).'/'.$product->id.'/'.$this->functions->slug($product->name);
			$img=(is_array($product->images) && !empty($product->images)) ? $img=reset($product->images) : array('small'=>'/img/elements/defaultproduct.png');
			$json[]=array('label'=>$product->name, 'value'=>$uri, 'image'=>$img['small'], 'price'=>$product->price.' Ft');
		}
		echo json_encode($json);
		die();
	}



}
