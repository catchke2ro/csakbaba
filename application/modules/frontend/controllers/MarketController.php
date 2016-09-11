<?php

class MarketController extends CB_Controller_Action {

	/**
	 * @var \CB\Model\User()
	 */
	private $userModel;

	/**
	 * @var \CB\Model\Product()
	 */
	private $productModel;

	private $productParams=array();

	/**
	 * @var Zend_Session_Namespace
	 */
	private $cassaSession;

	public function init(){
		$this->userModel=new \CB\Model\User();
		$this->productModel=new \CB\Model\Product();

		parent::init();
	}

	public function indexAction(){
		$categoryTree=Zend_Registry::get('categories');
		$catMultiArray=$categoryTree->_multiArray;

		$extraParams=$this->_request->getExtraParams();
		list($category, $categoryPath, $categoryOptions, $children, $extraParams)=$categoryTree->fetchCategory($extraParams);

		if($category){
			$this->view->searchForm->getElement('category_id')->setValue($category->id);
		}

		$bc=Zend_Registry::get('breadcrumb');
		if($categoryPath) $bc=array_merge($bc, array_reverse($categoryPath));
		Zend_Registry::set('breadcrumb', $bc);


		$this->_meta($category);



		if(!empty($extraParams) && new \MongoId($extraParams[1]) && !is_numeric($extraParams[0])){
			$this->forward('product', 'market', 'index', $extraParams);
			return;
		}

		if($category){
			$this->view->headTitle()->prepend($category->title ?: $category->name);
		}

		$products=$this->productModel->findByCategory($category, $categoryTree);

		/*if(empty($products) && $category){
			$this->productModel->initQb();
			$this->productModel->qb->field('status')->equals(1);
			$this->productModel->qb->field('category')->equals(false);
			$this->productModel->qb->field('category')->equals(new MongoRegex('/^'.$category->id.'\-.*'));
			$productTeaser=$this->productModel->runQuery();
			shuffle($productTeaser);
			$productTeaser=array_slice($productTeaser, 0, 15);
		}*/

		$subheader=$this->view->partial('market/menu.phtml', array(
			'catMultiArray'=>$catMultiArray,
			'category'=>$category,
			'categoryPath'=>$categoryPath,
			'categoryOptions'=>$categoryOptions,
			'products'=>$products,
			'hasChildren'=>(is_array($children) && !empty($children))
		));
		//if(trim($subheader)=='') $this->view->placeholder('subheader')->setPrefix('<div class="subheader hidden"><div class="subheaderInner">');
		$this->view->placeholder('subheader')->append($subheader);

		$this->view->assign(array(
			'category'=>$category,
			'categoryPath'=>$categoryPath,
			'categoryOptions'=>$categoryOptions,
			'children'=>is_array($children) ? $children : $catMultiArray,
			'products'=>$products,
			'categoryTree'=>$categoryTree
		));
	}


	public function productsajaxAction(){
        $this->getHelper('layout')->disableLayout();
		$categoryTree=Zend_Registry::get('categories');
		$catMultiArray=$categoryTree->_multiArray;

		$categoryId='';
		if(!empty($_GET['category_id'])) $categoryId=$_GET['category_id'];
		if(!empty($_POST['category_id'])) $categoryId=$_POST['category_id'];
		if(!(!empty($categoryId) && (isset($categoryTree->_singleArray[$categoryId])))){
			//$this->getHelper('viewRenderer')->setNoRender(true);
			//return false;
			$category=false;
		} else {
			$category=$categoryTree->_singleArray[$categoryId];
		}

		list($category, $categoryPath, $categoryOptions, $children, $extraParams)=$categoryTree->fetchCategory($category);

		$products=$this->productModel->findByCategory($category, $categoryTree);

		$this->view->assign(array(
						'category'=>$category,
						'categoryPath'=>$categoryPath,
						'categoryOptions'=>$categoryOptions,
						'children'=>is_array($children) ? $children : $catMultiArray,
						'products'=>$products,
						'categoryTree'=>$categoryTree
		));
		$this->_helper->viewRenderer('product_list_partial');
	}

	public function productAction(){
		$categoryTree=Zend_Registry::get('categories');
		$product=$this->productModel->findOneById($this->getParam(1));
		if($product===false) $this->redirect('/');
		$product->visitors++;
		$this->productModel->save($product);
		$bc=Zend_Registry::get('breadcrumb');
		array_push($bc, $product);
		Zend_Registry::set('breadcrumb', $bc);
		$this->view->pageResourceSlug.=' details';
		$this->view->headTitle()->prepend($product->name);

		$commentForm=new Frontend_Form_Comment();
		$commentForm->getElement('product_id')->setValue($product->id);

		$userProducts=$product->user->getProducts(true, 10, array(1));

		$orderModel=new \CB\Model\Order();
		$userOrdersBuy=$orderModel->find(array('conditions'=>array('user._id'=>new \MongoId($product->user->get()->id), 'user_rating.$id'=>array('exists'=>true)), 'limit'=>2, 'order'=>'date DESC'));
		$userOrdersSell=$orderModel->find(array('conditions'=>array('shop_user._id'=>new \MongoId($product->user->get()->id), 'shop_user_rating.$id'=>array('exists'=>true)), 'limit'=>2, 'order'=>'date DESC'));

		$this->view->headMeta()->setName('description', $product->name.' a csakbaba.hu börzén. '.substr(strip_tags($product->desc), 0, 100).'...');
		if(!empty($product->images)){
			$firstImg=reset($product->images);
			$this->view->headMeta()->setName('og:image', 'https://'.$_SERVER['HTTP_HOST'].$firstImg['url']);
		}
		$this->view->assign(array(
			'product'=>$product,
			'categoryTree'=>$categoryTree,
			'deliveryOptions'=>$this->deliveryOptions,
			'comments'=>$product->getComments(),
			'commentForm'=>$commentForm,
			'userProducts'=>$userProducts,
			'userOrdersSell'=>$userOrdersSell,
			'userOrdersBuy'=>$userOrdersBuy
		));
	}


	public function promotedAction(){
		$categoryTree=Zend_Registry::get('categories');
		$catMultiArray=$categoryTree->_multiArray;

		$key=reset($this->_request->getExtraParams());
		$key=(array_key_exists($key, $catMultiArray)) ? $key : '';
		$this->productModel->initQb();
		$this->productModel->qb->field('promotes.first')->gte(time());
		$this->productModel->qb->field('status')->equals(1);
		if(!empty($key)) $this->productModel->qb->field('category')->equals(new \MongoRegex('/^'.$key.'-.*/iu'));
		$products=$this->productModel->runQuery();

		$this->view->assign(array(
			'products'=>$products,
			'categoryTree'=>$categoryTree,
			'category'=>$catMultiArray[$key]
		));
	}

	


	public function searchAction(){
		$categoryTree=Zend_Registry::get('categories');
		$searchSession=new Zend_Session_Namespace('search');
		$results=array();

		if($searchSession->q) $results=$this->productModel->search($searchSession->q, ($searchSession->category_id ? $searchSession->category_id : false));

		$this->view->assign(array(
			'q'=>$searchSession->q ? $searchSession->q : '',
			'results'=>$results,
			'categoryTree'=>$categoryTree
		));
	}


	public function cassaAction(){
		if($this->getRequest()->isPost() && ($pid=$this->getRequest()->getPost('pid')) && ($user=Zend_Auth::getInstance()->getIdentity())){
			CB_Resource_Functions::logEvent('orderingStarted');
			if($this->_order($pid, $user)){
				$this->cassaSession=new Zend_Session_Namespace('cassa');
				$this->cassaSession->unsetAll();
				$this->redirect($this->url('vasarlaskoszono'));
			}
		}
		$id=!empty($_GET['id']) ? $_GET['id'] : $this->cassaSession->id;
		if(!$id) $this->redirect($this->url('piac'));
		$product=$this->productModel->findOneById($id);
		if(!$product || $product->status!=1){
			$this->m('A termék már nem elérhető, vagy nem létezik', 'error');
			$this->redirect($this->url('piac'));
		}
		$this->cassaSession->id=$id;
		if(!Zend_Auth::getInstance()->getIdentity()) {
			$this->m('Jelentkezz be a vásárláshoz');
			$this->redirect($this->url('bejelentkezes'));
		}

		$this->view->assign(array(
			'product'=>$product
		));
	}

	public function thanksAction(){
		$this->view->assign(array(
			'promoted'=>$this->productModel->getPromoted('first'),
			'categoryTree'=>Zend_Registry::get('categories')
		));
	}

	private function _order($pid, $user){
		$orderModel=new \CB\Model\Order();
		$productModel=new \CB\Model\Product();
		$product=$productModel->findOneById($pid);
		$order=$orderModel->save(array('date'=>date('Y-m-d H:i:s'), 'user'=>$this->userModel->findOneById($user['id']), 'product'=>$product, 'shop_user'=>$product->user->get(), 'code'=>uniqid('CSB')));

		$this->emails->buyUser(array('product'=>$product, 'user'=>$order->user, 'shop_user'=>$order->shop_user, 'order'=>$order));
		$this->emails->buyShopUser(array('product'=>$product, 'user'=>$order->user, 'shop_user'=>$order->shop_user, 'order'=>$order));
		$product->status=2;
		$productModel->save($product);
		//CB_Resource_Functions::addFeed('newOrder', $order->shop_user, $product);
		CB_Resource_Functions::logEvent('orderingEnded', array('order'=>$order));
		return true;
	}


	private function _meta($category){
		if($category){
			if(strpos($category->id, 'baba')===0) $rootCat='baba';
			if(strpos($category->id, 'gyerek')===0) $rootCat='gyerek';
			if(strpos($category->id, 'kismama')===0) $rootCat='kismama';
			switch($category->id){
				case 'baba':
					$meta='Minden, amire babádnak szüksége van, új és használt ruhák, játékok'; break;
				case 'gyerek':
					$meta='Minden, amire gyerekednek szüksége van, új és használt ruhák, játékok, egyebek'; break;
				case 'kismama':
					$meta='Minden, amire kezdő anyaként szükséges lehet, új és használt ruhák, kellékek'; break;
				default: $meta=''; break;
			}
			if(!empty($meta) && $category->parent_id=='') $this->view->headMeta()->setName('description', $meta);
			else $this->view->headMeta()->setName('description', 'Használt és új '.$category->name.' széles választéka a csakbaba.hu online bababörzén! Válogass a csakbaba eladóinak asztaláról kedvező áron!');
		} else {
			$this->view->headMeta()->setName('description', 'Közösségi vásártér, börze, ahol már kismamaként megtalálhatod babádnak, gyerekednek, vagy magadnak, amit keresel.');
		}
	}






	public function oldproductlejarAction(){
		$this->getHelper('layout')->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();

		$products=$this->productModel->find(array('conditions'=>array(
			'date_added'=>array('lte'=>new \DateTime('2014-12-31 23:59:59'))
		)));
		foreach($products as $p){
			$p->status=3;
			$this->productModel->save($p, true);
		}
	}



    public function manualemailAction(){
        //die();
        //$this->emails->commentSubscribedNotification(array('comment'=>$comment, 'user'=>$product->user->get(), 'product'=>$product));

        $data['user'] = $this->userModel->findOneByUsername('csilla74');
        $user = $data['user'];
        $data['comment'] = (new \CB\Model\Comment())->findOneById('57bbfee44a7959e03c8b4567');
        $data['product'] = (new \CB\Model\Product())->findOneById('5556f5341cab67245af07fd1');

        $categories=Zend_Registry::get('categories');
        $data['productlink']=$categories->getUri($data['product']->category).'/'.$data['product']->id.'/'.$this->functions->slug($data['product']->name);

        $data['subscribedUser'] = $user;

        $mail = new CB_Resource_Mail('UTF-8');
        $mail->s(array(
            'to'=>array($user->username=>$user->email),
            'template'=>'commentsubscribeduser',
            'subject'=>'csakbaba.hu - Új hozzászólás',
            'data'=>$data
        ));
        die();
    }

}
