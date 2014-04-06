<?php

class CB_Form_Element_Upload extends Zend_Form_Element_Xhtml {

	public $helper = 'formUpload';

	public $uploadUrl='/upload/upload';
	public $targetDir='/upload/img';
	public $buttonLabel='Képek feltöltése';

	public function __construct($spec, $options = null){
		parent::__construct($spec, $options);
		$this->addFilter(new CB_Form_Filter_JSON());
	}


	public function setUploadUrl($url){
		$this->uploadUrl=$url;
	}

	public function setTargetDir($dir){
		$this->targetDir=$dir;
	}

}