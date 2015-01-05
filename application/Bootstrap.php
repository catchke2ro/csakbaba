<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

	function _initGlobalFunctions(){
		include_once(APPLICATION_PATH.'/../library/CB/global_functions.php');
	}

	function _initCSBConfig(){
		$config=new Zend_Config_Ini(APPLICATION_PATH.'/configs/csb.ini');
		Zend_Registry::set('CsbConfig', $config);
	}

	function _initException(){
		//error_reporting(E_ERROR | E_WARNING | E_NOTICE);
		if(empty($_COOKIE['CSBDEV'])){
			//Zend_Controller_Front::getInstance()->throwExceptions(false);
		}
		set_exception_handler(array('CB_Exception_Default', 'fault'));
		set_error_handler(array('CB_Exception_Default','fault'));
		register_shutdown_function('shutdown');
	}

	function _initLogger(){
		$writer=new Zend_Log_Writer_Stream(APPLICATION_PATH.'/../tmp/info.log');
		$writer->setFormatter(new Zend_Log_Formatter_Simple("\n[%timestamp%]%priorityName%: %message%"));
		$logger=new Zend_Log($writer);
		Zend_Registry::set('logger', $logger);

		$writer=new Zend_Log_Writer_Firebug();
		$writer->setFormatter(new Zend_Log_Formatter_Simple("\n[%timestamp%]%priorityName%: %message%"));
		$logger=new Zend_Log($writer);
		Zend_Registry::set('FBlogger', $logger);
	}

	function _initCache(){
		$options=$this->getOptions();
		$sc=Zend_Registry::get('CsbConfig');
		$opts=array_merge($options['cache'], $sc->get('cache')->toArray());
		$manager=new Zend_Cache_Manager();
		$cache=Zend_Cache::factory(
						new CB_Resource_Cache($opts),
						new Zend_Cache_Backend_File(),
						$opts,
						array('server'=>array($options['memcached'])));
		$manager->setCache('general', $cache);
		Zend_Registry::set('cache', $manager);
	}



	function _initGlobals(){
		Zend_Registry::set('genreTypes', array('fiu'=>'Fiú', 'lany'=>'Lány', 'felnott'=>'Felnőtt', 'egyeb'=>'Unisex'));
		$cacheManager=Zend_Registry::get('cache');
		$cache=$cacheManager->getCache('general');
		if(!($categories=$cache->load('categories'))){
			$categories=new CB_Array_Categories();
			$cache->save($categories, 'categories');
		}
		Zend_Registry::set('categories', $categories);
		Zend_Registry::set('uploadPrice', 40);
		Zend_Registry::set('freeUploadLimit', 10);
		Zend_Registry::set('minCharge', 320);
		Zend_Registry::set('deliveryOptions', array('personal'=>'Személyes átvétel','post'=>'Posta', 'futar'=>'Futárszolgálat'));
		Zend_Registry::set('autoRenewOptions', array('never'=>'Nem', 'once'=>'Egyszer', 'always'=>'Mindig'));
		Zend_Registry::set('promoteOptions', array('first'=>'Főoldalon - 1 hét','list'=>'Listaoldalon első helyeken - 1 hét','frame'=>'Kiemelés kerettel - 1 hét'));
		Zend_Registry::set('promoteOptionPrices', array('first'=>520,'list'=>160,'frame'=>120,'allfirst'=>960));
		Zend_Registry::set('promoteAllOptions', array('allfirst'=>'Kiemelem az asztalomat a főoldalon'));
		Zend_Registry::set('statusCodes', array(0=>'Inaktív',	1=>'Aktív',	2=>'Eladott',	3=>'Lejárt'));
		Zend_Registry::set('feedTypes', array(
			'newComment'=>'Új hozzászólás érkezett egy termékedhez',
			'newOrder'=>'Megvásárolták egy termékedet',
			'newRating'=>'Új értékelés érkezett',
			'productExpired'=>'Egy terméked lejárt',
			'productRenewed'=>'Egy terméked automatikusan megújult',
			'balanceCharged'=>'Egyenlegfeltöltés sikeres'
		));
	}

	function _initRequest(){
		$this->bootstrap('frontController');
		$front = $this->getResource('frontController');
		$front->setRequest(new CB_Resource_Request());

		header('Content-Type: text/html; charset=utf-8');
	}

	/**
	 * Init view resource settings
	 */
	function _initViewSettings(){
		$this->bootstrap('view');
		$view=$this->getResource('view');
		$view->headTitle()->setSeparator(' - ');
		$view->doctype('HTML5');
		$view->setEncoding('UTF-8');
		$view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf-8');
	}

	function _initAutoloader(){
		$this->_resourceLoader->addResourceTypes(array(
			'formResource' => array('path'=>'forms','namespace' => 'Form'),
			'model' => array('path'=>'models/','namespace' => 'Model_'),
			'mappers' => array('namespace' =>'Model_Mapper_', 'path'=>'models/Mapper/')
		));
	}

	function _initRoutes(){
		$router=Zend_Controller_Front::getInstance()->getRouter();
		$router->addRoute('frontend', new CB_Resource_Route());
		$router->addRoute('admin', new Zend_Controller_Router_Route('/admin/:controller/:action', array('module'=>'admin','controller'=>'index','action'=>'index')));
		$router->addRoute('ext', new Zend_Controller_Router_Route('/ext/:controller/:action/*', array('module'=>'ext','controller'=>'index','action'=>'index')));
		$router->addRoute('sitemap', new Zend_Controller_Router_Route('/sitemap.xml', array('module'=>'frontend','controller'=>'index','action'=>'sitemapxml')));
		$router->addRoute('paymentredirect', new Zend_Controller_Router_Route('/paymentredirect/*', array('module'=>'frontend','controller'=>'user','action'=>'paymentredirect')));
		$router->addRoute('paymenthook', new Zend_Controller_Router_Route('/paymenthook/*', array('module'=>'frontend','controller'=>'user','action'=>'paymenthook')));

	}

	function _initDB(){
		$this->bootstrap('odm');
		$dm = $this->getResource('odm');
		Zend_Registry::set('dm', $dm); //Set Document Manager in Zend_Registry
	}

	function _initPlugins(){
		$front=Zend_Controller_Front::getInstance();
		$front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(array('module'=>'frontend', 'controller'=>'error', 'action'=>'error')));
		$front->registerPlugin(new CB_Controller_Frontend_Plugin_CB());
		$front->registerPlugin(new CB_Controller_Frontend_Plugin_ACL());
	}

	function _initValidationMessages(){
		$translator=new Zend_Translate(array(
			'adapter'=>'array', 'content'=>APPLICATION_PATH.'/configs/languages', 'locale'=>'hu', 'scan'=>Zend_Translate::LOCALE_DIRECTORY
		));
		Zend_Validate_Abstract::setDefaultTranslator($translator);
	}



}
