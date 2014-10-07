<?php

class Frontend_Form_Contact extends CB_Form_Form {

	public function init(){
		$this->initFields();
	}

	public function initFields(){
		$name=new Zend_Form_Element_Text('name');
		$name->setLabel('név')->setAttribs(array('placeholder'=>$name->getLabel(), 'required'=>'required'))->setRequired(true);
		$name->addValidator(new Zend_Validate_NotEmpty());
		$email=new Zend_Form_Element_Email('email');
		$email->setLabel('e-mail cím')->setAttribs(array('placeholder'=>$email->getLabel(), 'required'=>'required'))->setRequired(true);
		$email->addValidators(array(new Zend_Validate_NotEmpty(), new Zend_Validate_EmailAddress()));
		$text=new Zend_Form_Element_Textarea('text');
		$text->setLabel('szöveg')->setAttribs(array('placeholder'=>$text->getLabel(), 'required'=>'required'))->setRequired(true);
		$text->addValidator(new Zend_Validate_NotEmpty());
		$captchaOptions=Zend_Controller_Front::getInstance()->getParam('bootstrap')->getOption('captchaOptions');
		$captchaOptions['width']=100;
		$captcha=new Zend_Form_Element_Captcha('captcha', array('captcha'=>new CB_Form_ImageCaptcha($captchaOptions)));
		$captcha->setLabel('Írd be az ellenőrzőkódot')->setAttrib('required', 'required');
		$submit=new Zend_Form_Element_Submit('Küldés');
		$submit->removeDecorator('label');

		$this->addElements(array($name, $email, $text, $captcha, $submit));
		$this->setAction($this->getView()->url('contact'));
	}

	public function isValid($data){
		if(!empty($data['w'])){
			$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
			$redirector->goToUrl('/');
			return false;
		}
		return parent::isValid($data);
	}


}