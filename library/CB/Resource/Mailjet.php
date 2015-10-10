<?php

class CB_Resource_Mailjet extends Zend_Mail_Transport_Smtp {

	public function __construct($host = '127.0.0.1', Array $config = array()){
		$config = array(
						//'ssl'=>'tls',
						//'port'=>'587',
						'port'=>'25',
						'auth'=>'login',
						'username' => 'd06cb8e360ee26230b0112ac63b270ba',
						//'username' => 'catchke2ro@miheztarto.hu',
						'password' => '3f1cfc2311249f5b07efe2d6889d6512');
						//'password' => 'c2Sc-9Bam');
		/*$config = array(
			'smtp_host'=>'ssl://in.mailjet.com',
			'smtp_port'=>465,
			'smtp_user'=>'d06cb8e360ee26230b0112ac63b270ba',
			'smpt_pass'=>'7b479096a8d11909d20123a10d4ab782');*/
		parent::__construct('in.mailjet.com', $config);
		//parent::__construct('smtp.gmail.com', $config);
	}

}
