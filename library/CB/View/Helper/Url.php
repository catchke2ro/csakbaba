<?php
/**
 * Class SRG_View_Helper_FlashMessages
 * @author SRG Group
 * Helper for Flash messages
 * @TODO Frontendre talán kell. Majd bekommentelem, ha értem hogy hova kell.
 */
class CB_View_Helper_Url extends Zend_View_Helper_Abstract{

	public $urlMap=array();


	public function url($id=null){
		if(!empty($this->urlMap[$id])) return $this->urlMap[$id];
		$nav=Zend_Registry::get('nav');
		$page=$nav->findBy('resource', $id);
		$uri=$page ? $page->get('uri') : '/';
		$this->urlMap[$id]=$uri;
		return $uri;
	}
}