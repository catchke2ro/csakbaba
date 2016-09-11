<?php

class Frontend_Form_BlogEdit extends CB_Form_Form {

	public $user;

	public function init(){
		$this->setAttrib('enctype', 'multipart/form-data');
        $this->initFields();
    }

	public function initFields($category = null, $deliveryOptions = null){
		$id=new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label');
        
		$title=new Zend_Form_Element_Text('title');
		$title->setLabel('Cím')->setRequired(true)->addValidators(array(array('NotEmpty',true)));
		$slug=new Zend_Form_Element_Text('slug');
		$slug->setLabel('Slug')->setRequired(true)->addValidators(array(array('NotEmpty',true)));
        
        
        $teaser=new Zend_Form_Element_Textarea('teaser');
        $teaser->setLabel('Bevezető')->setAttrib('class', 'ck')->addValidators(array(array('NotEmpty',true)))
            ->setAttrib('data-tb', 'Full')->setAttrib('data-ss', 'blog')->setAttrib('data-height', 300);
        $body=new Zend_Form_Element_Textarea('body');
        $body->setLabel('Szöveg')->setAttrib('class', 'ck')->addValidators(array(array('NotEmpty',true)))
            ->setAttrib('data-tb', 'Full')->setAttrib('data-ss', 'blog')->setAttrib('data-height', 400);

        $submit=new Zend_Form_Element_Submit('Mentés');
        $this->addElements(array($id, $title, $slug, $teaser, $body, $submit));
    }

}