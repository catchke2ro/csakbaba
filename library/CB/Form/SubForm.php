<?php

class CB_Form_SubForm extends Zend_Form_SubForm {

	public function __construct($options=null){
		parent::__construct($options);
		$this->getDecorator('HtmlTag')->setTag('ul')->removeOption('tag');
	}


	public function addElement($element, $name = null, $options = null){
		parent::addElement($element, $name, $options);

		$element->setDecorators(array(
			'ViewHelper',
			array('Label', array('placement'=>'append')),
			'Errors',
			array('HtmlTag', array('tag'=>'li', 'class'=>'field '.strtolower(end(explode('_', $element->getType()))).' '.($element->isRequired() ? 'req' : '')))
		));
		if($element->getType()=='Zend_Form_Element_Submit') $element->removeDecorator('Label');
		if($element->getType()=='Zend_Form_Element_Captcha') $element->removeDecorator('ViewHelper');
	}

	public function render(Zend_View_Interface $view = null){
		foreach($this->getElements() as $el){
			if($el->hasErrors()){
				$el->setAttrib('class', $el->getAttrib('class').' errors');
				$el->getDecorator('HtmlTag')->setOption('class', $el->getDecorator('HtmlTag')->getOption('class').' errors');
			}
		}
		return parent::render($view);
	}

}