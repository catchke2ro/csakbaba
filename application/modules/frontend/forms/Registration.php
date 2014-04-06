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
		$username->setLabel('Felhasználónév')->setRequired(true)->addValidators(array(array('NotEmpty',true),array('Alnum',true),array('StringLength',true,'options'=>array('min'=>5,'max'=>16)), array($usernameCb,true)))->setAttrib('autocomplete', 'off')->setAttrib('required', 'required');
		$email=new Zend_Form_Element_Email('email');
		$email->setLabel('E-mail cím')->setRequired(true)->addValidators(array(array('NotEmpty',true),array('EmailAddress',true), array($emailCb,true)))->setAttrib('autocomplete', 'off')->setAttrib('required', 'required');
		$gender=new Zend_Form_Element_Radio('gender');
		$gender->setLabel('Nem')->setRequired(true)->setMultiOptions(array('male'=>'Férfi', 'female'=>'Nő'))->setAttrib('required', 'required');
		$password=new Zend_Form_Element_Password('password');
		$password->setLabel('Jelszó')->setRequired(true)->addValidators(array(array('NotEmpty',true),array('Alnum',true),array('StringLength',true,'options'=>array('min'=>5,'max'=>16)),array($passwordCb,true)))->setAttrib('autocomplete', 'off')->setAttrib('required', 'required');
		$passwordConfirm=new Zend_Form_Element_Password('passwordconfirm');
		$passwordConfirm->setLabel('Jelszó megerősítése')->setRequired(true)->addValidators(array(array('NotEmpty',true),array('Alnum',true),array($passwordCb,true)))->setAttrib('required', 'required');
		$captchaOptions=Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('captchaOptions');
		$captcha=new Zend_Form_Element_Captcha('captcha', array('captcha'=>new CB_Form_ImageCaptcha($captchaOptions)));
		$captcha->setLabel('Írd be az ellenőrzőkódot')->setAttrib('required', 'required');
		$aszf=new Zend_Form_Element_Checkbox('aszf');
		$aszf->setLabel('Elfogadom az <a href="javascript:void(0)" class="sbLink" data-url="/index/aszf">ÁSZF</a>-et')->setRequired(true)->setUncheckedValue(null)->setAttrib('required', 'required');
		$newsletter=new Zend_Form_Element_Checkbox('newsletter');
		$newsletter->setLabel('Feliratkozom a hírlevélre');
		$submit=new Zend_Form_Element_Submit('Küldés');
		$submit->removeDecorator('label');

		$this->addElements(array($username, $email, $gender, $password, $passwordConfirm, $captcha, $aszf, $newsletter, $submit));
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