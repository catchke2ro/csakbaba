<?php

/**
 * Abstract class for extension
 */
require_once 'Zend/View/Helper/FormElement.php';


class CB_View_Helper_FormUpload extends Zend_View_Helper_FormElement {

	public function formUpload($name, $value = null, $attribs = null) {

		$xhtml='';
		$info = $this->_getInfo($name, $value, $attribs);
		extract($info);

		$attribs['data-targetDir']=$attribs['targetDir']; unset($attribs['targetDir']);
		$attribs['data-url']=$attribs['uploadUrl']; unset($attribs['uploadUrl']);
		$attribs['data-name']=$name;
		$label=$attribs['buttonLabel']; unset($attribs['buttonLabel']);

		$uploadTpl='<div class="file template-upload">'
						.'<div class="preview"></div>'
						.'<div class="filename"></div>'
						.'<div class="size"></div>'
						.'<div class="cancel">X</div>'
						.'<div class="error"></div>'
						.'<div class="progress"><div class="progress-bar progress-bar-success"></div></div>'
						.'</div>';
		$downloadTpl='<div class="file template-download">'
						.'<div class="previewImage"><img class="preview" /></div>'
						.'<div class="filename"></div>'
						.'<div class="delete">X</div>'
						.'<div class="move"></div>'
						.'<div class="error"></div>'
						.'</div>';

		$xhtml.='<div class="fileUploadContainer">'
						.'<div class="btn btn-success fileinput-button dropzone"><span>'.$label.'</span>'
						.'<input id="'.$this->view->escape($id).'" type="file" capture="image" name="'.$this->view->escape($name).'file[]" multiple '.$this->_htmlAttribs($attribs).' class="fileupload" />'
						.'<input type="hidden" name="'.$this->view->escape($name).'" value="'.$this->view->escape(json_encode(array_values(!is_array($value) ? array() : $value))).'" class="saveinput">'
						.'</div>'
            .'<div class="files"></div>'
						.$uploadTpl
						.$downloadTpl
						.'</div>';


		return $xhtml;
	}

}
