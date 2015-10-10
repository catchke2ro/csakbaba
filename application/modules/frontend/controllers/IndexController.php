<?php

class IndexController extends CB_Controller_Action {

	public function indexAction(){
		$productModel=new \CB\Model\Product();
		$visited=$productModel->getMostVisited();
		$fresh=$productModel->getFresh();
		//$favourites=$productModel->getFavouriteLists();
		$categoryTree=Zend_Registry::get('categories');

		$this->view->headMeta()->setName('description', 'A csakbaba.hu egy online bababörze, ahol nem csak eladhatod használt vagy új baba, kismama és gyerekholmijaidat, hanem be is szerezheted mindazokat az eladók asztaláról.');
		//$this->view->headMeta()->setName('keywords', 'csakbaba, új, használt, baba, gyerek, kismama, ruha, börze');


		$promoteFirst=array();
		foreach($categoryTree->_multiArray as $key=>$mainCat){
			$promoteFirst[$key]=$productModel->getPromoted('first', true, $key);
		}

		$orderModel=new \CB\Model\Order();
		$latestOrders=$orderModel->find(array('conditions'=>array('user._id'=>array('notEqual'=>new \MongoId('528a82320f435fd2028b4568')), 'shop_user._id'=>array('notEqual'=>new \MongoId('528a82320f435fd2028b4568'))), 'order'=>'date DESC', 'limit'=>10));
		$latestUsers=array();
		foreach(($latestOrders ? $latestOrders : array()) as $latestOrder){
			$latestUsers[$latestOrder->shop_user->id]=$latestOrder->shop_user;
		}

		$blogModel=new \CB\Model\BlogPost();
		$latestPost=$blogModel->findOne(array('order'=>'date DESC'));

		$userModel=new \CB\Model\User();
		$promotedUsers=$userModel->getPromoted('allfirst');

		$this->view->assign(array(
			'visited'=>$visited,
			'fresh'=>$fresh,
			'latestUsers'=>$latestUsers,
			'latestPost'=>$latestPost,
			'categoryTree'=>$categoryTree,
			'promoteFirst'=>$promoteFirst,
			'promotedUsers'=>$promotedUsers
		));


	}

	public function aboutAction(){
		$this->view->headMeta()->setName('description', 'Az oldal azért jött létre, hogy itt tényleg azt kapd, amit keresel, sőt, hogy itt csupán azt vedd meg, amiért eredetileg a gép elé ültél.');
	}

	public function blogAction(){
		$postModel=new \CB\Model\BlogPost();

		$ep=$this->_request->getExtraParams();
		$this->view->headMeta()->setName('description', 'Hasznos cikkek információk a csakbaba.hu oldalról, és minden kismamának, szülőnek szóló témáról');
		if(!empty($ep) && ($post=$postModel->findOneBy('slug', reset($ep)))){
			$this->view->headMeta()->setName('description', substr(strip_tags($post->teaser), 0, 160).'...');
			$this->view->assign(array('post'=>$post));
			$this->_helper->viewRenderer('blogpost');
			$this->view->headTitle()->prepend($post->title);
			$bc=Zend_Registry::get('breadcrumb');
			array_push($bc, $post);
			Zend_Registry::set('breadcrumb', $bc);
			return;
		}
		$posts=$postModel->find(array('order'=>'date desc'));
		$this->view->assign(array(
			'posts'=>$posts ? $posts : array()
		));
	}

	public function contactAction(){
		$form=new Frontend_Form_Contact();

		if($this->_request->isPost()){
			CB_Resource_Functions::logEvent('contactSend');
			if($form->isValid($this->_request->getPost())){
				$this->emails->contactForm($this->_request->getPost());
				$this->view->assign(array('ok'=>true));

			}
		}
		$this->view->assign(array('contactForm'=>$form));
		$this->getHelper('layout')->setLayout('default');
	}

	public function categoryselectorAction(){
		$this->getHelper('layout')->disableLayout();
	}

	public function aszfAction(){
		if(!empty($_GET['sb'])) $this->getHelper('layout')->setLayout('ajax');
	}

	public function impresszumAction(){
	}

	public function adatvedelemAction(){
	}

	public function feliratkozvaAction(){
	}


	public function sitemapxmlAction(){
		$this->getHelper('layout')->disableLayout();

		$sites=array();
		$this->_resourceWalk($sites, $this->nav->getPages());
		$this->view->assign(array('sites'=>$sites));
		$this->getResponse()->setHeader('Content-type', 'application/xml');
	}

	private function _resourceWalk(&$sites, $pages=array()){
		foreach($pages as $page){
			if($page->get('noindex')) continue;
			$sites[]=array('url'=>$page->getUri(), 'freq'=>'daily');
			if($page->get('resource')=='piac') $this->_piacWalk($sites, $page->getUri());
			if($page->get('resource')=='kiemelt') $this->_kiemeltWalk($sites, $page->getUri());
			if($page->get('resource')=='blog') $this->_blogWalk($sites, $page->getUri());
			if($page->get('resource')=='profil') $this->_profilWalk($sites, $page->getUri());
		}
	}

	private function _piacWalk(&$sites, $url=''){
		$categories=Zend_Registry::get('categories');
		foreach($categories->_singleArray as $category){
			$sites[]=array('url'=>$url.$category->url, 'freq'=>'hourly');
		}
		$productModel=new \CB\Model\Product();
		$products=$productModel->find(array('conditions'=>array('status'=>1)));
		foreach($products as $product){
			if(empty($categories->_singleArray[$product->category])) continue;
			$categoryUrl=$categories->_singleArray[$product->category]->url;
			$sites[]=array('url'=>$url.$categoryUrl.'/'.$product->id.'/'.$this->functions->slug($product->name), 'freq'=>'hourly');
		}
	}

	private function _kiemeltWalk(&$sites, $url=''){
		$categories=Zend_Registry::get('categories');
		foreach($categories->_multiArray as $category){
			$sites[]=array('url'=>$url.'/'.$category->slug, 'freq'=>'daily');
		}
	}

	private function _blogWalk(&$sites, $url=''){
		$postModel=new \CB\Model\BlogPost();
		foreach($postModel->find() as $post){
			$sites[]=array('url'=>$url.'/'.$post->slug, 'freq'=>'daily');
		}
	}

	private function _profilWalk(&$sites, $url=''){
		$userModel=new \CB\Model\User();
		foreach($userModel->find() as $user){
			$sites[]=array('url'=>$url.'/'.urlencode($user->username), 'freq'=>'daily');
		}
	}



	public function emailtestAction(){
		$this->emails->test();
		die();
	}





}
