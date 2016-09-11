<?php

class Frontend_Form_Registration extends CB_Form_Form {

	private $validators;

	public function init(){
		$this->initFields();
	}

	public function initFields(){
		$usernameCb=new Zend_Validate_Callback(array($this, 'uniqueUsername')); $usernameCb->setMessage('A felhasználónév már foglalt');
		$emailCb=new Zend_Validate_Callback(array($this, 'uniqueEmail')); $emailCb->setMessage('Az e-mail cím már foglalt');
		$passwordCb=new Zend_Validate_Callback(array($this, 'passwordConfirm')); $passwordCb->setMessage('A két jelszó nem egyezik');

		$username=new Zend_Form_Element_Text('username');
		$username->setLabel('Felhasználónév')->setRequired(true)->addValidators(array(array('NotEmpty',true),array('Regex',true,'options'=>array('pattern'=>\CB\User::$usernameRegex)),array('StringLength',true,'options'=>array('min'=>5,'max'=>16)), array($usernameCb,true)))->setAttrib('autocomplete', 'off')->setAttrib('required', 'required');
        $username->setDescription(self::infoDescription('Csak számok, betűk, és a .- karakterek megadása lehetséges'));

        $email=new Zend_Form_Element_Email('email');
		$email->setLabel('E-mail cím')->setRequired(true)->addValidators(array(array('NotEmpty',true),array('EmailAddress',true), array($emailCb,true)))->setAttrib('autocomplete', 'off')->setAttrib('required', 'required');
		$password=new Zend_Form_Element_Password('password');
		$password->setLabel('Jelszó')->setRequired(true)->addValidators(array(array('NotEmpty',true),array('Alnum',true),array('StringLength',true,'options'=>array('min'=>5,'max'=>16)),array($passwordCb,true)))->setAttrib('autocomplete', 'off')->setAttrib('required', 'required');
		$passwordConfirm=new Zend_Form_Element_Password('passwordconfirm');
		$passwordConfirm->setLabel('Jelszó megerősítése')->setRequired(true)->addValidators(array(array('NotEmpty',true),array('Alnum',true),array($passwordCb,true)))->setAttrib('required', 'required');
		$captcha=new CB_Form_Element_ReCaptcha('captcha', []);
        $captcha->setRequired(true)->setValidators(array(new CB_Form_Validator_ReCaptcha()));
		$aszf=new Zend_Form_Element_Checkbox('aszf');
		$aszf->setLabel('Elfogadom az <a href="javascript:void(0)" class="sbLink" data-url="/aszf?sb=1">ÁSZF</a>-et')->setRequired(true)->setUncheckedValue(null)->setAttrib('required', 'required');
		$newsletter=new Zend_Form_Element_Checkbox('newsletter');
		$newsletter->setLabel('Feliratkozom a hírlevélre');
		$submit=new Zend_Form_Element_Submit('Regisztráció');
		$submit->removeDecorator('label');

		$this->addElements(array($username, $email, $password, $passwordConfirm, $captcha, $aszf, $newsletter, $submit));
	}

	public function passwordConfirm($value, $values){
		return $values['password']==$values['passwordconfirm'];
	}

	public function uniqueEmail($value, $values){
		$userModel=new \CB\Model\User();
		return !$userModel->findOneByEmail($value);
	}

	public function uniqueUsername($value, $values){
		$userModel=new \CB\Model\User();
		return !$userModel->findOneByUsername($value);
	}

}