<?php

class CB_Resource_Emails {

	/**
	 * @var CB_Resource_Mail
	 */
	public $mail;
	public $adminEmail;
	public $user;
	public $functions;

	public function __construct($user){
		$this->mail=new CB_Resource_Mail('UTF-8');
		$this->adminEmail=array('info@csakbaba.hu');
		$this->user=$user;
		$this->functions=new CB_Resource_Functions();
	}

	public function test(){
		$this->mail->s(array(
			'to'=>'catchke2ro@miheztarto.hu',
			'template'=>'test',
			'subject'=>'csakbaba.hu - Test'
		));
	}

	public function contactForm($values){
		$this->mail->s(array(
			'to'=>$this->adminEmail,
			'template'=>'contact',
			'subject'=>'csakbaba.hu - Contact form',
			'data'=>$values
		));
	}

	public function productAdd($data){
		$categories=Zend_Registry::get('categories');
		$data['productlink']=$categories->getUri($data['product']->category).'/'.$data['product']->id.'/'.$this->functions->slug($data['product']->name);
		$data['user']=$this->user;
		
		$user = $this->user->get();
		$productCount = 0;
		if($user){
			$productCount = $user->getActiveProductsCount();
		}
		$data['free'] = ($productCount <= Zend_Registry::get('freeUploadLimit'));
		
		$this->mail->s(array(
			'to'=>array($this->user->get()->username=>$this->user->get()->email),
			'template'=>'productadd',
			'subject'=>'csakbaba.hu - Termék hozzáadás',
			'data'=>$data
		));
	}

	public function productDeactivated($data){
		$categories=Zend_Registry::get('categories');
		$data['productlink']=$categories->getUri($data['product']->category).'/'.$data['product']->id.'/'.$this->functions->slug($data['product']->name);
		$this->mail->s(array(
			'to'=>array($data['user']->get()->username=>$data['user']->get()->email),
			'template'=>'productdeactivated',
			'subject'=>'csakbaba.hu - Lejárt termék',
			'data'=>$data
		));
	}

	public function buyUser($data){
		$categories=Zend_Registry::get('categories');
		$data['productlink']=$categories->getUri($data['product']->category).'/'.$data['product']->id.'/'.$this->functions->slug($data['product']->name);
		$to = array($data['user']->get()->username=>$data['user']->get()->email);
		//$to = array('adam.balint@srg.hu');
		$this->mail->s(array(
			'to'=>$to,
			'template'=>'buyuser',
			'subject'=>'csakbaba.hu - Vásárlás visszaigazolás',
			'data'=>$data
		));
	}

	public function buyShopUser($data){
		$categories=Zend_Registry::get('categories');
		$data['productlink']=$categories->getUri($data['product']->category).'/'.$data['product']->id.'/'.$this->functions->slug($data['product']->name);
		$to = array($data['shop_user']->get()->username=>$data['shop_user']->get()->email);
		//$to = array('adam.balint@srg.hu');
		$this->mail->s(array(
			'to'=>$to,
			'template'=>'buyshopuser',
			'subject'=>'csakbaba.hu - Új vásárlás',
			'data'=>$data
		));
	}

	public function ratingNotifyUser($data){
		$categories=Zend_Registry::get('categories');
		$data['productlink']=$categories->getUri($data['product']->category).'/'.$data['product']->id.'/'.$this->functions->slug($data['product']->name);
		$this->mail->s(array(
			'to'=>array($data['user']->get()->username=>$data['user']->get()->email),
			'template'=>'ratingnotifyuser',
			'subject'=>'csakbaba.hu - Értékelés emlékeztető',
			'data'=>$data
		));
	}

	public function ratingNotifyShopUser($data){
		$categories=Zend_Registry::get('categories');
		$data['productlink']=$categories->getUri($data['product']->category).'/'.$data['product']->id.'/'.$this->functions->slug($data['product']->name);
		$this->mail->s(array(
			'to'=>array($data['shop_user']->get()->username=>$data['shop_user']->get()->email),
			'template'=>'ratingnotifyshopuser',
			'subject'=>'csakbaba.hu - Értékelés emlékeztető',
			'data'=>$data
		));
	}

	public function balanceLow($data){
		$this->mail->s(array(
			'to'=>array($data['user']->get()->username=>$data['user']->get()->email),
			'template'=>'balancelow',
			'subject'=>'csakbaba.hu - Egyenlegfeltöltés',
			'data'=>$data
		));
	}

	public function charged($data){
		$attUrl=!empty($data['payment']->invoice_data['invoice_number']) ? APPLICATION_PATH.'/../tmp/invoices/'.str_replace('/', '_', $data['payment']->invoice_data['invoice_number']).'.pdf' : '';
		$this->mail->s(array(
			'to'=>array($data['user']->get()->username=>$data['user']->get()->email),
			'template'=>'charged',
			'subject'=>'csakbaba.hu - Egyenlegfeltöltés sikeres',
			'data'=>$data,
			'attachment'=>(!empty($attUrl) && file_exists($attUrl)) ? $attUrl : ''
		));
	}

	public function activation($data, $aktUrl){
		$data['activation_link']='https://'.$_SERVER['HTTP_HOST'].$aktUrl.'/'.$data['user']->activation_code;
		$to = array($data['user']->get()->username=>$data['user']->get()->email);
		//$to = 'adam.balint@srg.hu';
		$this->mail->s(array(
			'to'=>$to,
			'template'=>'activation',
			'subject'=>'csakbaba.hu - Regisztráció aktiválása',
			'data'=>$data
		));
	}
	
	public function profiledelete($data){
		$to = array('catchke2ro@miheztarto.hu', 'info@csakbaba.hu');
		$this->mail->s(array(
			'to'=>$to,
			'template'=>'profiledelete',
			'subject'=>'csakbaba.hu - Profil törlés',
			'data'=>$data
		));
	}
	
	public function adatvedelem($user){
		$to = array($user->email);
		$this->mail->s(array(
				'to'=>$to,
				'template'=>'adatvedelem',
				'subject'=>'csakbaba.hu - Adatvédelmi tájékoztató'
		));
	}

	public function activationreminder($data, $aktUrl){
		$data['activation_link']='https://'.$_SERVER['HTTP_HOST'].$aktUrl.'/'.$data['user']->activation_code;
		$this->mail->s(array(
			'to'=>array($data['user']->get()->username=>$data['user']->get()->email),
			'template'=>'activationreminder',
			'subject'=>'csakbaba.hu - Aktiváld fiókodat',
			'data'=>$data
		));
	}

	public function reactivation($data, $aktUrl){
		$data['activation_link']='https://'.$_SERVER['HTTP_HOST'].$aktUrl.'/'.$data['user']['activation_code'].'/1/'.$data['user']['email'];
		$this->mail->s(array(
			'to'=>array($data['user']['username']=>$data['user']['email']),
			'template'=>'reactivation',
			'subject'=>'csakbaba.hu - E-mail cím aktiválása',
			'data'=>$data
		));
	}

	public function forgotten($data){
		$this->mail->s(array(
			'to'=>array($data['user']->get()->username=>$data['user']->get()->email),
			'template'=>'forgotten',
			'subject'=>'csakbaba.hu - Elfelejtett jelszó',
			'data'=>$data
		));
	}

	public function commentProductUser($data){
		$categories=Zend_Registry::get('categories');
		$data['productlink']=$categories->getUri($data['product']->category).'/'.$data['product']->id.'/'.$this->functions->slug($data['product']->name);
		$this->mail->s(array(
			'to'=>array($data['user']->get()->username=>$data['user']->get()->email),
			'template'=>'commentproductuser',
			'subject'=>'csakbaba.hu - Üzeneted érkezett',
			'data'=>$data
		));
	}

	public function commentSubscribedNotification($data){
		$categories=Zend_Registry::get('categories');
		$data['productlink']=$categories->getUri($data['product']->category).'/'.$data['product']->id.'/'.$this->functions->slug($data['product']->name);

		$loggedInUser=$this->user;

		$productId=$data['product']->id;
		$userModel=new \CB\Model\User();
		$userModel->initQb();
		$userModel->qb->field('subscribed')->elemMatch($userModel->qb->expr()->in(array($productId)));
		$subscribedUsers=$userModel->runQuery();
		foreach($subscribedUsers as $user){
			if($user->get()->id==$data['user']->get()->id || ($loggedInUser && $loggedInUser->get()->id==$user->get()->id)) continue;

			$data['subscribedUser']=$user;

			$this->mail->s(array(
				'to'=>array($user->username=>$user->email),
				'template'=>'commentsubscribeduser',
				'subject'=>'csakbaba.hu - Új hozzászólás',
				'data'=>$data
			));
		}



	}

	public function blogCommentNotification($data){
        $emails = [];
        foreach($data['users'] as $user){
            if($user->username == 'catchke2ro') continue;
            $emails[$user->username] = $user->email;
        }

        $this->mail->s(array(
            'to'=>$emails,
            'template'=>'blognotification',
            'subject'=>'csakbaba.hu - Blog - Új komment',
            'data'=>$data
        ));



	}

	public function commentModerated($data){
		$categories=Zend_Registry::get('categories');
		$data['productlink']=$categories->getUri($data['product']->category).'/'.$data['product']->id.'/'.$this->functions->slug($data['product']->name);
		$this->mail->s(array(
			'to'=>array($data['user']->get()->username=>$data['user']->get()->email),
			'template'=>'commentmoderated',
			'subject'=>'csakbaba.hu - Üzeneted moderálva lett',
			'data'=>$data
		));
	}


}