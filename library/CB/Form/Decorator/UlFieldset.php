<?php

class CB_Form_Decorator_UlFieldset extends Zend_Form_Decorator_Fieldset {

	public function render($content) {
		return '<ul>'.$content.'</ul>';
	}
}
