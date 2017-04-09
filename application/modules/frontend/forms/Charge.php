<?php

class Frontend_Form_Charge extends CB_Form_Form {


	public function initFields(){
	    $min = Zend_Registry::get('minCharge');
	    
		$amount=new CB_Form_Element_Number('amount');
		$amount->setAttrib('placeholder', 'összeg')->removeDecorator('label');
		$amount->setAttrib('data-upload', Zend_Registry::get('uploadPrice'))->setAttrib('data-min', $min);
		$amount->setAttrib('min', $min);
		$validator=	new Zend_Validate_GreaterThan($min-1);
		$validator->setMessage('A minimum összeg '.$min.' Ft.');
		$amount->addValidator($validator)->setAllowEmpty(false);
		$submit=new Zend_Form_Element_Submit('paysubmit');
		$submit->removeDecorator('label')->setLabel(html_entity_decode('Fizetés bankkártyával vagy Barionnal'));

		$this->addElements(array($amount, $submit));
	}


}