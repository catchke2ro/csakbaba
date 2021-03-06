<?php

class Frontend_Form_Comment extends CB_Form_Form {

	public function init(){
		$this->initFields();
	}

	public function initFields(){
		$this->setAction('#');
		$user=Zend_Layout::getMvcInstance()->getView()->user;
		$text=new Zend_Form_Element_Textarea('text');
		$text->setAttrib('placeholder', 'üzenet')->removeDecorator('label')->setAttrib('required', 'required');
		$pid=new Zend_Form_Element_Hidden('product_id');
		$pid->removeDecorator('label');
		$poid=new Zend_Form_Element_Hidden('post_id');
		$poid->removeDecorator('label');
		$submit=new Zend_Form_Element_Submit('Küldés');
		$submit->removeDecorator('label');

		$note=new Zend_Form_Element_Note('user');
		if(!$user){
			$text->setAttrib('disabled', 'disabled')->setAttrib('placeholder', 'Kérdés írásához be kell jelentkezned');
			$submit->setAttrib('disabled', 'disabled');
		} else {
			$text->setAttrib('placeholder', 'Ide írd az üzenetedet...');
			if(!empty($user->avatar[0]['small'])) $note->setValue('<div style="background-image: url('.$user->avatar[0]['small'].');"></div>');
		}

		$this->addElements(array($text, $note, $pid, $poid, $submit));
	}


}