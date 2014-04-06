<?php

class CB_Form_Form extends Zend_Form {

	public function __construct($options=null){
		parent::__construct($options);
		$this->getDecorator('HtmlTag')->setTag('ul')->removeOption('tag');
	}


	public function addElement($element, $name = null, $options = null){
		parent::addElement($element, $name, $options);

		$element->setDecorators(array(
			'ViewHelper',
			array('Label', array('placement'=>'append', 'escape'=>false)),
			'Errors',
			array('Description', array('escape'=>false)),
			array('HtmlTag', array('tag'=>'li', 'class'=>'field '.strtolower(end(explode('_', $element->getType()))).' '.($element->isRequired() ? 'req' : '').' '.$element->getAttrib('class')))
		));

		if($element->getType()=='Zend_Form_Element_Radio' || $element->getType()=='Zend_Form_Element_MultiCheckbox'){
			$element->setDecorators(array(
				'ViewHelper',
				new CB_Form_Decorator_RadioDivWrapper(),
				'Errors',
				array('Label'),
				array('Description', array('escape'=>false)),
				array('HtmlTag', array('tag'=>'li', 'class'=>'field '.strtolower(end(explode('_', $element->getType()))).' '.($element->isRequired() ? 'req' : '')))
			));
		}

		if($element->getType()=='Zend_Form_Element_Submit') $element->removeDecorator('Label');
		if($element->getType()=='Zend_Form_Element_Captcha') $element->removeDecorator('ViewHelper');
	}

	public function addDisplayGroup(array $elements, $name, $options = null){
		parent::addDisplayGroup($elements, $name, $options);
		$this->_displayGroups[$name]->setDecorators(array(
			array('FormElements'),
			new CB_Form_Decorator_UlFieldset(),
			array('Fieldset'),
			array('HtmlTag', array('tag'=>'li', 'class'=>'fieldset '.$name))
		));
	}

	public function addSubForm(Zend_Form $form, $name, $order = null){
		parent::addSubForm($form, $name, $order);
		$form->setDecorators(array(
			array('FormElements'),
			new CB_Form_Decorator_UlFieldset(),
			array('Fieldset'),
			array('HtmlTag', array('tag'=>'li', 'class'=>'fieldset '.$name))
		));
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