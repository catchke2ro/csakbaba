<?php

class Frontend_Form_Charge extends CB_Form_Form {


	public function initFields(){
		$amount=new CB_Form_Element_Number('amount');
		$amount->setAttrib('placeholder', 'összeg')->removeDecorator('label');
		$amount->setAttrib('data-upload', Zend_Registry::get('uploadPrice'))->setAttrib('data-min', Zend_Registry::get('minCharge'));
		$amount->setAttrib('min', Zend_Registry::get('minCharge'));
		$validator=	new Zend_Validate_GreaterThan(Zend_Registry::get('minCharge')-1);
		$validator->setMessage('A minimum összeg '.Zend_Registry::get('minCharge').' Ft.');
		$amount->addValidator($validator)->setAllowEmpty(false);
		$submit=new Zend_Form_Element_Submit('paysubmit');
		$submit->removeDecorator('label')->setLabel('Fizetés bankkártyával');

		$this->addElements(array($amount, $submit));
	}


}