<?php
class CB_Exception_Default extends Zend_Exception{

  public function __construct($message, $code = 0, Exception $previous = null) {
      parent::__construct($message, $code, $previous);
      if($code=="404"){
          $handler=new SRG_Exception_Handler();
          $handler->Error404();
      }
  }

  static function fault(){
    $args = func_get_args();
    if(!is_object($args[0])){
      self::errorHandling($args[0],$args[1],$args[2],$args[3]);
    }else{
      self::exceptionHandling($args[0]);
    }
	  return true;
  }

	static function exceptionHandling($e){
		$code = $e->getCode();
		if($code==404){
			self::notFound();
			return;
		}
		if($code>=E_STRICT) return;
		$mes = $e->getMessage();
		$file = $e->getFile();
		$line = $e->getLine();
		$trace = str_replace("\n", "\n\t\t\t\t", $e->getTraceAsString());

		$sep='<br/>';
		$message ='';
		$message.="\t\tException: ".$sep;
		$message.="\t\tCode: ".$code.$sep;
		$message.="\t\tMessage: ".$mes.$sep;
		$message.="\t\tFile: ".$file.$sep;
		$message.="\t\tLine: ".$line.$sep;
		$message.="\t\tTrace: ".$trace.$sep.$sep;
		//l($message, 'ERR', true);
		l($message, 'ERR', false);
		//if(!empty($_COOKIE['CSBDEV'])){
			echo $message ;
		//}
	}

	static function errorHandling($code, $mes, $file, $line){
		if($code==404){
			self::notFound();
			return;
		}
		if($code>=E_STRICT) return;
		$sep='<br/>';
		$message ='';
		$message.="\t\tException: ".$sep;
		$message.="\t\tCode: ".$code.$sep;
		$message.="\t\tMessage: ".$mes.$sep;
		$message.="\t\tFile: ".$file.$sep;
		$message.="\t\tLine: ".$line.$sep;
		//l($message, 'ERR', true);
		l($message, 'ERR', false);
		//f(!empty($_COOKIE['CSBDEV'])){
			echo $message ;
		//}
	}

	static function notFound(){
		$front=Zend_Controller_Front::getInstance();
		$front->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
		$front->getRequest()->setModuleName('frontend')->setControllerName('error')->setActionName('notfound');
	}

}

