<?php

class Frontend_Form_Rating extends CB_Form_Form {

	public $oid;

	public function init(){
	}

	public function initFields(){
		$success=new Zend_Form_Element_Radio('success');
		$success->setLabel('Sikeres volt a rendelés?')->setRequired(true)->setMultiOptions(array(0=>'Nem', 1=>'Igen'));
		$positive=new Zend_Form_Element_Radio('positive');
		$positive->setLabel('Felhasználó értékelése')->setRequired(true)->setMultiOptions(array(0=>'Negatív', 1=>'Pozitív'));
		$text=new Zend_Form_Element_Textarea('text');
		$text->setLabel('Szöveges értékelés');
		$oid=new Zend_Form_Element_Hidden('oid');
		$oid->removeDecorator('label')->setValue($this->oid);
		$submit=new Zend_Form_Element_Submit('Értékelés elküldése');
		$submit->setLabel('Értékelés elküldése');
		$submit->removeDecorator('label');

		$this->addElements(array($success, $positive, $text, $submit));
	}


}