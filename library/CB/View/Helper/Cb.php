<?php

class CB_View_Helper_Cb extends Zend_View_Helper_Abstract{

	function cb(){
		return $this;
	}

	function slug($str=''){
		$functions=new CB_Resource_Functions();
		return $functions->slug($str);
	}

}