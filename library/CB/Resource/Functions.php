<?php
class CB_Resource_Functions extends Zend_Application_Resource_ResourceAbstract {

	public function init(){}

	public function slug($string=''){
		$slug = trim($string);
		$trArray=array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
			'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
			'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
			'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
			'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ő'=>'o', 'Ő'=>'O', 'ű'=>'u', 'Ű'=>'U', 'ü'=>'u');
		$slug =strtr($slug, $trArray);
		$slug= preg_replace('/[^a-zA-Z0-9 -]/','',$slug );
		$slug= str_replace(' ','-', $slug);
		$slug= strtolower($slug);
		return $slug;
	}


	static function logEvent($eventName, $params=array()){
		$logModel=new \CB\Model\Log();
		$log=new \CB\Log();
		$log->event_name=$eventName;
		$log->date=date('Y-m-d H:i:s');
		if(Zend_Registry::isRegistered('user')){
			$log->user=object_to_array(Zend_Registry::get('user'));
		}
		$log->ip=(!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'cron');
		$log->server=$_SERVER;
		$log->post=$_POST;
		$log->get=$_GET;
		$log->params=object_to_array($params);
		$logModel->save($log);
	}

	static function addFeed($type, $user, $product=false){
		$feedModel=new \CB\Model\Feed();
		$feed=new \CB\Feed();
		$feed->saveAll(array(
			'date'=>date('Y-m-d H:i:s'), 'user'=>$user, 'product_id'=>($product ? $product->id : ''), 'type'=>$type, 'read'=>false
		));
		$feedModel->save($feed);
	}

}