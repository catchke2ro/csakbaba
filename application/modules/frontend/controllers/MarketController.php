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

		if(!empty($extraParams) && new \MongoId($extraParams[1]) && !is_numeric($extraParams[0])){
			$this->forward('product', 'market', 'index', $extraParams);
			return;
		}

		if($category){
			$this->view->headTitle()->prepend($category->name);
		}

		$products=$category ? $this->productModel->findByCategory($category, $categoryTree) : array();

		$productTeaser=array();
		if(empty($products) && $category){
			$this->productModel->initQb();
			$this->productModel->qb->field('status')->equals(1);
			$this->productModel->qb->field('category')->equals(false);
			$this->productModel->qb->field('category')->equals(new MongoRegex('/^'.$category->id.'\-.*/i'));
			$productTeaser=$this->productModel->runQuery();
			shuffle($productTeaser);
			$productTeaser=array_slice($productTeaser, 0, 15);
		}

		$this->view->placeholder('subheader')->append($this->view->partial('market/menu.phtml', array(
			'catMultiArray'=>$catMultiArray,
			'category'=>$category,
			'categoryPath'=>$categoryPath,
			'categoryOptions'=>$categoryOptions,
			'products'=>$products,
			'hasChildren'=>(is_array($children) && !empty($children))
		)));

		$this->view->assign(array(
			'category'=>$category,
			'categoryPath'=>$categoryPath,
			'categoryOptions'=>$categoryOptions,
			'children'=>is_array($children) ? $children : $catMultiArray,
			'products'=>$products,
			'categoryTree'=>$categoryTree,
			'productTeaser'=>$productTeaser
		));
	}


	public function filterAction(){
		$this->getHelper('layout')->disableLayout();
		$categoryTree=Zend_Registry::get('categories');
		$catMultiArray=$categoryTree->_multiArray;

		if(!(!empty($_POST['category_id']) && (isset($categoryTree->_singleArray[$_POST['category_id']])))){
			$this->getHelper('viewRenderer')->setNoRender(true);
			return false;
		}
		$category=$categoryTree->_singleArray[$_POST['category_id']];

		list($category, $categoryPath, $categoryOptions, $children, $extraParams)=$categoryTree->fetchCategory($category);

		$products=$category ? $this->productModel->findByCategory($category, $categoryTree) : array();

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

	public function commentAction(){
		$this->getHelper('layout')->disableLayout();
		if(empty($_POST['product_id'])) die('OK');
		$productModel=new \CB\Model\Product();
		if(!($product=$productModel->findOneById($this->_request->getPost('product_id'))) || !$this->user) die();
		$commentModel=new \CB\Model\Comment();
		$comment=new \CB\Comment();
		$comment->saveAll(array(
			'date'=>date('Y-m-d H:i:s'), 'user'=>$this->user, 'product_id'=>$_POST['product_id'], 'text'=>$_POST['text']
		));
		$comment=$commentModel->save($comment);
		CB_Resource_Functions::logEvent('commentAdded', array('comment'=>$comment));
		$comment->date=new DateTime($comment->date);
		$this->emails->commentProductUser(array('comment'=>$comment, 'user'=>$product->user->get(), 'product'=>$product));
		$this->view->assign(array('comment'=>$comment, 'added'=>true, 'product'=>$product));
		$this->_helper->viewRenderer('comment');
	}


	public function searchAction(){
		$categoryTree=Zend_Registry::get('categories');
		$searchSession=new Zend_Session_Namespace('search');
		$products=array();
		if(!empty($searchSession->q)){
			$q=$searchSession->q;
			$this->productModel->initQb();
			if($searchSession->category_id) $this->productModel->qb->field('category')->equals($searchSession->category_id);
			foreach(explode(' ', $q) as $word){
				$word=trim($word);
				if(empty($word)) continue;
				$this->productModel->qb->field('name')->equals(new MongoRegex('/.*'.$word.'.*/iu'));
			}
			$this->productModel->qb->field('status')->equals(1);
			$resultName=$this->productModel->runQuery();
			$this->productModel->initQb();
			foreach(explode(' ', $q) as $word){
				$word=trim($word);
				if(empty($word)) continue;
				$this->productModel->qb->field('desc')->equals(new MongoRegex('/.*'.htmlentities($word, ENT_COMPAT | 'ENT_HTML401', 'UTF-8').'.*/iu'));
			}
			$this->productModel->qb->field('status')->equals(1);
			$resultDesc=$this->productModel->runQuery();

			$results=array();
			foreach($resultName as $rn){
				$results[$rn->id]=array('point'=>5, 'product'=>$rn);
			}
			foreach($resultDesc as $rd){
				if(!array_key_exists($rd->id, $results)){
					$results[$rd->id]=array('point'=>1, 'product'=>$rd);
				} else {
					$results[$rd->id]['point']++;
				}
			}

			usort($results, function($a,$b){
				return $a<$b ? 1 : -1;
			});
		}
		$this->view->assign(array(
			'q'=>$q,
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
		CB_Resource_Functions::logEvent('orderingEnded', array('order'=>$order));
		return true;
	}

}
