<?php

class Frontend_Form_Forgotten extends CB_Form_Form {

	private $validators;

	public function init(){
		$this->initFields();
	}

	public function initFields(){
		$email=new CB_Resource_Form_Element_Email('email');
		$email->setLabel('E-mail cím')->setRequired(true)->addValidators(array(array('NotEmpty',true)))->setAttrib('autocomplete', 'off');
		$submit=new Zend_Form_Element_Submit('Küldés');
		$submit->removeDecorator('label');

		$this->addElements(array($email, $submit));
	}

}