<?php

/**
 * Abstract class for extension
 */
require_once 'Zend/View/Helper/FormElement.php';


class CB_View_Helper_FormReCaptcha extends Zend_View_Helper_FormElement {

	public function formReCaptcha($name, $value = null, $attribs = null) {

        $id = uniqid('captcha');
        $xhtml='<div class="recaptchaWrapper recaptcha_'.$name.'"><div class="g-recaptcha" data-callback="captchaCallback'.$id.'" data-sitekey="'.$attribs['data-sitekey'].'"></div>';
        $xhtml.='<input name="'.$name.'" type="hidden" id="'.$id.'"/>';
        $xhtml.= '<script type="text/javascript">function captchaCallback'.$id.'(response){ document.getElementById("'.$id.'").value=response; }</script>';
		return $xhtml;
	}

}
