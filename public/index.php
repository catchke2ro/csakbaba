<?php
//memprof_enable();
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

//ini_set('browscap', APPLICATION_PATH.'/../library/php_browscap.ini');

/** Zend_Application */

ini_set('display_errors', true);
ini_set('display_startup_errors', true);
if(!empty($_COOKIE['SRGDEV'])){
	//ini_set('max_execution_time', 480);
    if(isset($_GET['sd31641'])){
        session_start();
        session_destroy();
        die();
    }
}

if(isset($_GET['sd31641'])){
    session_start();
    session_destroy();
    die();
}

define('DEV', strpos($_SERVER['HTTP_HOST'], 'dev.') !== false);
define('ANALYTICS_ID', DEV ? 'UA-48324090-2' : 'UA-48324090-1');


$loader = require_once APPLICATION_PATH.'/../vendor/autoload.php';

/*Zend_Loader_AutoloaderFactory::factory(
				array(
					'Zend_Loader_ClassMapAutoloader' => array(
									__DIR__.'/../library/classmap.php'
					),
					'Zend_Loader_StandardAutoloader' => array(
									'prefixes' => array(
										'Zend'=>APPLICATION_PATH.'/../library/Zend',
										'Doctrine'=>APPLICATION_PATH.'/../library/Doctrine',
										'CB'=>APPLICATION_PATH.'/../library/CB',
										'GoogleAnalytics'=>APPLICATION_PATH.'/../library/GoogleAnalytics',
									),
									'namespaces'=>array(
										'MongoDB'=>APPLICATION_PATH.'/../library/MongoDB',
										'hydrators'=>APPLICATION_PATH.'/models/cache/hydrators',
										'annotation'=>APPLICATION_PATH.'/models/cache/annotation',
										'proxies'=>APPLICATION_PATH.'/models/cache/proxies',
										'Facebook' => APPLICATION_PATH.'/../vendor/facebook/graph-sdk/src/Facebook',
									),
									'fallback_autoloader' => true
					)
				)
);*/

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->getAutoloader()->pushAutoloader($loader);
$application->bootstrap()
            ->run();

//var_dump(memory_get_usage() / (1024*1024));
//memprof_dump_callgrind(fopen("/tmp/cachegrindout/cachegrind.out.csb_memory_".time(), "w"));
//memprof_dump_pprof(fopen("/tmp/cachegrindout/profile.csb_memory.heap", "w"));