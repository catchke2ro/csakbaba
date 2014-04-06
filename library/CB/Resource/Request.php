<?php
/**
 * Class SRG_Resource_Request
 * @author SRG Group
 * Request object for frontend requests
 */
class CB_Resource_Request extends Zend_Controller_Request_Http {

	/**
	 * @var array Page params based on Uri items
	 */
	public $pageParams=array();

	/**
	 * Get only Uri params (without module, controller, action)
	 * @return array Params
	 */
	public function getUriParams(){
		$params=array_filter($this->getParams());
		foreach($params as $key=>$param){
			if(!is_numeric($key)){ unset($params[$key]); }
		}
		return $params;
	}

	public function getUri(){
		$url=reset(explode('?', $this->_pathInfo));
		if(substr($url, -1)=='/') $url=substr($url, 0, strlen($url)-1);
		return $url;
		//return implode('/', $this->getUriParams());
	}

	/**
	 * Get only non-page, extra params
	 * @return array Params
	 */
	public function getExtraParams($key=false){
		$ep=array_values(array_diff($this->getUriParams(), $this->pageParams));
		return $key===false ? $ep : (!empty($ep[$key]) ? $ep[$key] : false);
	}

}