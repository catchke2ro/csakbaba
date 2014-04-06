<?
/**
 * Global functions for easier developement
 * @author SRG Group
 */

/**
 * Print_r with 'pre' tags
 * @param $var mixed Anything to debug
 */
function pr($var){
	echo '<pre>';
	print_r($var);
	echo '</pre>';
}



function isJson($string) {
	if(empty($string)) return false;
	json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE);
}



function pd($class, $level=4){
	\Doctrine\Common\Util\Debug::dump($class, $level, false);
}



function l($message='', $type='info', $firebug=false){
	$logger=$firebug ? 'FBlogger' : 'logger';
	if(!Zend_Registry::isRegistered($logger)) die('No logger');
	$logger=Zend_Registry::get($logger);
	$message=strip_tags(str_replace(array("</p>", "<br/>"), "\n", $message));
	$logger->log($message, constant('Zend_Log::'.strtoupper($type)), array('timestamp'=>date('Y-m-d H:i:s')));
}


function shutdown() {
	if($error=error_get_last()){
		l($error['message'], 'crit', false);
		if(empty($_COOKIE['CSBDEV'])){
			if(ob_get_length()) ob_clean();
		}
		echo file_get_contents(APPLICATION_PATH.'/../public/500.html');
	}
}


function object_to_array($obj) {
	$_arr = is_object($obj) ? get_object_vars($obj) : $obj;
	$arr=array();
	foreach ($_arr as $key => $val) {
		$val = (is_array($val) || is_object($val)) ? object_to_array($val) : $val;
		$arr[$key] = $val;
	}
	return $arr;
}
