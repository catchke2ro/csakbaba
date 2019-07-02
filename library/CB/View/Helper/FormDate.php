<?php


require_once 'Zend/View/Helper/FormElement.php';


class CB_View_Helper_FormDate extends Zend_View_Helper_FormElement
{
	public function formDate($name, $value = null, $attribs = null)
	{
		$info = $this->_getInfo($name, $value, $attribs);
		extract($info); // name, value, attribs, options, listsep, disable

		// build the element
		$disabled = '';
		if ($disable) {
			// disabled
			$disabled = ' disabled="disabled"';
		}

		// XHTML or HTML end tag?
		$endTag = ' />';
		if (($this->view instanceof Zend_View_Abstract) && !$this->view->doctype()->isXhtml()) {
			$endTag= '>';
		}

		if(is_object($value)) $value=$value->format('Y-m-d');
		$xhtml = '<input type="text"'
						. ' name="' . $this->view->escape($name) . '"'
						. ' id="' . $this->view->escape($id) . '"'
						. ' value="' . $this->view->escape($value) . '"'
						. $disabled
						. $this->_htmlAttribs($attribs)
						. $endTag;

		return $xhtml;
	}
}
