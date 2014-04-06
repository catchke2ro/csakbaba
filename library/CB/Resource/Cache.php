<?php

class CB_Resource_Cache extends Zend_Cache_Core {

	public function __construct($options = array()){
		parent::__construct($options);
	}

	protected function _id($id){
		return parent::_id($id);
	}

	public function remove($id){
		parent::remove($id);
	}
}
