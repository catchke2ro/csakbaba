<?php

class Frontend_Form_Promote extends CB_Form_Form {

	public $prices;
	public $options=array();

	public function init(){
	}

	public function initFields(){
		$this->prices=Zend_Registry::get('promoteOptionPrices');
		$opts=array();
		foreach($this->options as $type=>$text){
			$opts[$type]=$text.' ('.$this->prices[$type].')';
		}

		$cb=new Zend_Form_Element_MultiCheckbox('types');
		$cb->setLabel('Kiemelés típusa')->setRequired(true)->setMultiOptions($opts);

		$view=Zend_Layout::getMvcInstance()->getView();
		$submit=new Zend_Form_Element_Submit('Kiemelés');
		$submit->removeDecorator('label');

		$hint=new Zend_Form_Element_Note('hint');
		$hint->setValue('Nincs elég pénz az egyenlegeden. Előbb <a href="'.$view->url('egyenleg').'">töltsd fel</a>!');


		$this->addElements(array($cb, $submit, $hint));
		$this->setAttrib('data-prices', json_encode($this->prices));
	}

	public function setProduct($product){
		$value=array();
		if(is_array($product->promotes)){
			foreach($product->promotes as $type=>$date){
				if($date>time()) $value[]=$type;
			}
		}
		$this->getElement('types')->setValue($value)->setAttrib('disable', $value);
	}


}