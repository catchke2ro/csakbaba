<?php

class Frontend_Form_Contact extends CB_Form_Form {

	public function init(){
		$this->initFields();
	}

	public function initFields(){
		$name=new Zend_Form_Element_Text('name');
		$name->setLabel('név')->setAttribs(array('placeholder'=>$name->getLabel(), 'required'=>'required'))->setRequired(true);
		$name->addValidator(new Zend_Validate_NotEmpty());
		$email=new CB_Resource_Form_Element_Email('email');
		$email->setLabel('e-mail cím')->setAttribs(array('placeholder'=>$email->getLabel(), 'required'=>'required'))->setRequired(true);
		$email->addValidators(array(new Zend_Validate_NotEmpty(), new Zend_Validate_EmailAddress()));
		$text=new Zend_Form_Element_Textarea('text');
		$text->setLabel('szöveg')->setAttribs(array('placeholder'=>$text->getLabel(), 'required'=>'required'))->setRequired(true);
		$text->addValidator(new Zend_Validate_NotEmpty());
		
		$captcha=new CB_Form_Element_ReCaptcha('captcha', []);
		$captcha->setRequired(true)->setValidators(array(new CB_Form_Validator_ReCaptcha()));
		
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