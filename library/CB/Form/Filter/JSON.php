<?php

class CB_Form_Filter_JSON implements Zend_Filter_Interface {

	public function filter($value){
		return (is_string($value)) ? json_decode($value, true) : $value;
	}

}