<?php

class CB_Form_Decorator_RadioDivWrapper extends Zend_Form_Decorator_Fieldset {

	public function render($content) {
		return '<div class="radios">'.$content.'</div>';
	}
}
