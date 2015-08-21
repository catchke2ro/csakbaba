<?php

class CB_View_Helper_Cb extends Zend_View_Helper_Abstract{

	function cb(){
		return $this;
	}

	function slug($str=''){
		$functions=new CB_Resource_Functions();
		return $functions->slug($str);
	}

	function elapsed($date){
		$now=time();
		$time=$date->getTimestamp();
		$diff=$now-$time;

		if($diff <= 60) $str='kevesebb mint 1 perce';
		elseif($diff <= 3600) $str=round($diff/60).' perce';
		elseif($diff <= 7200) $str='kb. 1 órája';
		elseif(date('Ymd', $time) == date('Ymd', $now)) $str='ma, '.date('H:i', $time);
		elseif(date('Ymd', strtotime('yesterday', $now)) == date('Ymd', $time)) $str='tegnap, '.date('H:i', $time);
		else $str=date('Y. m. d. H:i', $time);
		return $str;
	}

}