<?php

class CB_Resource_Mailjet extends Zend_Mail_Transport_Smtp {

	public function __construct($host = '127.0.0.1', Array $config = array()){
		$config = array(
						//'ssl'=>'tls',
						//'port'=>'587',
						'port'=>'587',
						'auth'=>'login',
						'username' => '8cb16e82a67f4cfd61da37372cb1e40f',
						//'username' => 'catchke2ro@miheztarto.hu',
						'password' => '66d4f946bfa76ed5ba95d679cf956583');
						//'password' => 'c2Sc-9Bam');
		/*$config = array(
			'smtp_host'=>'ssl://in.mailjet.com',
			'smtp_port'=>465,
			'smtp_user'=>'d06cb8e360ee26230b0112ac63b270ba',
			'smpt_pass'=>'7b479096a8d11909d20123a10d4ab782');*/
		parent::__construct('in-v3.mailjet.com', $config);
		//parent::__construct('smtp.gmail.com', $config);
	}

}
