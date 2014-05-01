<?php
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),

    get_include_path(),
)));


/** Zend_Application */
require_once 'Zend/Application.php';


require_once APPLICATION_PATH.'/../library/Zend/Loader/AutoloaderFactory.php';
require_once APPLICATION_PATH.'/../library/Zend/Loader/ClassMapAutoloader.php';
Zend_Loader_AutoloaderFactory::factory(
				array(
								'Zend_Loader_ClassMapAutoloader' => array(
												__DIR__.'/../library/classmap.php'
								),
								'Zend_Loader_StandardAutoloader' => array(
												'prefixes' => array('Zend'=>APPLICATION_PATH.'/../library/Zend','Doctrine'=>APPLICATION_PATH.'/../library/Doctrine','CB'=>APPLICATION_PATH.'/../library/CB','GoogleAnalytics'=>APPLICATION_PATH.'/../library/GoogleAnalytics'),
												'fallback_autoloader' => true
								)
				)
);

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();