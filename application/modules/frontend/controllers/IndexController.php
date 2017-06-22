<?php

class IndexController extends CB_Controller_Action {
    
    /**
     * @var \CB\Model\User()
     */
    private $userModel;
    
    /**
     * @var \CB\Model\Product()
     */
    private $productModel;
    
    public function init(){
        $this->userModel=new \CB\Model\User();
        $this->productModel=new \CB\Model\Product();
        
        parent::init();
    }

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
	
	public function cookieAction(){
		$this->view->headMeta()->setName('description', 'A csakbaba.hu cookie-szabályzata');
	}

	public function blogAction(){
		$postModel=new \CB\Model\BlogPost();
		$productModel = new \CB\Model\Product();

		$ep=$this->_request->getExtraParams();
		$this->view->headMeta()->setName('description', 'Hasznos cikkek információk a csakbaba.hu oldalról, és minden kismamának, szülőnek szóló témáról');
		if(!empty($ep) && ($post=$postModel->findOneBy('slug', reset($ep)))){
			$random=$productModel->getRandom(5);
            $relatedPosts = $postModel->getRelated(3, [$post->id]);
			$this->view->headMeta()->setName('description', substr(strip_tags($post->teaser), 0, 160).'...');
			$categoryTree=Zend_Registry::get('categories');
            
            $commentForm=new Frontend_Form_Comment();
            $commentForm->getElement('post_id')->setValue($post->id);
            
			$this->view->assign(array(
			    'post'=>$post,
                'random'=>$random,
                'categoryTree'=>$categoryTree,
                'commentForm'=>$commentForm,
                'comments'=>$post->getComments(),
                'user'=>$this->user,
                'relatedPosts'=>$relatedPosts
            ));
			$this->_helper->viewRenderer('blogpost');
			$this->view->headTitle()->prepend($post->title);
			$bc=Zend_Registry::get('breadcrumb');
			array_push($bc, $post);
			Zend_Registry::set('breadcrumb', $bc);
			return;
		}
		$posts=$postModel->find(array('order'=>'date desc'));
		$this->view->assign(array(
			'posts'=>$posts ? $posts : array(),
            'user'=>$this->user
		));
	}
	
	
	public function blogeditAction(){
	    if(!($this->user && $this->user->blogadmin)) die();
        
        $blogModel = new \CB\Model\BlogPost();
        
        $form = new Frontend_Form_BlogEdit();
        
        $post = $blogModel->findOneById($this->g('id'));
        
        if($this->getRequest()->isPost()){
            if($form->isValid($this->getRequest()->getPost())){
                if(!$post) $post = new \CB\BlogPost();
                
                $values=$form->getValues();
                $values['date'] = date('Y-m-d H:i:s');
                if(empty($values['id'])) unset($values['id']);
                $post->saveAll($values);
                $blogModel->save($post);
                $this->m('Sikeres módosítás');
                $this->redirect($this->url('blog').'/'.$post->slug);
            } else {
                $this->m('Nem töltöttél ki megfelelően minden mezőt', 'error');
            }
        } else if($post) {
            $form->populate(get_object_vars($post));
        }
        
        $this->view->assign([
            'form'=>$form
        ]);
        
    }

	public function contactAction(){
		$form=new Frontend_Form_Contact();
		
		$this->view->minifyHeadScript()->appendFile('https://www.google.com/recaptcha/api.js');
		
		
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
    
    
    
    
    
    
    public function commentAction(){
        $this->getHelper('layout')->disableLayout();
        if(empty($_POST['product_id']) && empty($_POST['post_id'])) die('OK');
        
        $type = $this->_request->getPost('post_id') ? 'post' : 'product';
        $productId = $postId = null;
        switch($type){
            case 'post':
                $model = new \CB\Model\BlogPost();
                $id = $this->_request->getPost('post_id');
                $postId = $id;
                break;
            case 'product':
                $model = new \CB\Model\Product();
                $id = $this->_request->getPost('product_id');
                $productId = $id;
                break;
        }
        
        if(!($item=$model->findOneById($id)) || !$this->user) die();
        
        $commentModel=new \CB\Model\Comment();
        $comment=new \CB\Comment();
        $comment->saveAll(array(
            'date'=>date('Y-m-d H:i:s'), 'user'=>$this->user, 'product_id'=>$productId, 'post_id'=>$postId, 'text'=>$_POST['text']
        ));
        $comment=$commentModel->save($comment);
        
        CB_Resource_Functions::logEvent('commentAdded', array('comment'=>$comment));
        $comment->date=new DateTime($comment->date);
        
        if($type == 'product'){
            if($this->user->get()->id != $item->user->get()->id) {
                $this->emails->commentProductUser(array('comment'=>$comment, 'user'=>$item->user->get(), 'product'=>$item));
        
                $subscribed=$this->user->subscribed ? $this->user->subscribed : array();
                $subscribed[]=$item->id;
                $this->user->subscribed=array_values(array_unique($subscribed));
                $this->userModel->save($this->user);
            }
    
            $this->emails->commentSubscribedNotification(array('comment'=>$comment, 'user'=>$item->user->get(), 'product'=>$item));
        } else {
            $users = $this->userModel->find(['conditions'=>['blogadmin'=>true]]);
            $this->emails->blogCommentNotification(array('comment'=>$comment, 'users'=>$users, 'post'=>$item));
        }
        
        $this->view->assign(array('comment'=>$comment, 'added'=>true, 'product'=>$item));
        $this->_helper->viewRenderer('comment');
    }
    
    public function commentunsubscribeAction(){
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);
        if(!(
            !empty($_GET['uid']) &&
            !empty($_GET['token']) &&
            !empty($_GET['pid']) &&
            ($user = $this->userModel->findOneById($_GET['uid'])) &&
            $user->getToken() == $_GET['token'] &&
            ($product = $this->productModel->findOneById($_GET['pid']))
        )){
            $this->redirect('/');
            return;
        }
        
        $subscribed=$user->subscribed ? $user->subscribed : array();
        $flipped=array_flip($subscribed);
        unset($flipped[$product->id]);
        $subscribed=array_keys($flipped);
        
        $user->subscribed=$subscribed;
        $this->userModel->save($user);
        
        $categories=Zend_Registry::get('categories');
        $productLink=$this->url('piac').$categories->getUri($product->category).'/'.$product->id.'/'.$this->functions->slug($product->name);
        
        $this->m('Sikeresen leiratkoztál a termékről, a továbbiakban nem kapsz értesítést a hozzászólásokról.');
        $this->redirect($productLink);
        return;
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
