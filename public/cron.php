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

define('CRON', true);

$get=$_SERVER['argv'];
$_SERVER['HTTP_HOST']=end(explode('=', $argv[1]));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
				APPLICATION_ENV,
				APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()->run();