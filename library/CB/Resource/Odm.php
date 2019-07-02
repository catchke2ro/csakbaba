<?php

use Doctrine\Common\ClassLoader,
		Doctrine\Common\Annotations\FileCacheReader,
		Doctrine\Common\Annotations\AnnotationReader,
		Doctrine\ODM\MongoDB,
		Doctrine\ODM\MongoDB\DocumentManager,
		Doctrine\ODM\MongoDB\Mongo,
		Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

class CB_Resource_Odm extends Zend_Application_Resource_ResourceAbstract {

	/**
	 * Doctrine MongoDB ODM initialization
	 * @return DocumentManager
	 */
	public function init() {

		//include APPLICATION_PATH.'/../library/MongoDB/functions.php';
		$config = new \Doctrine\ODM\MongoDB\Configuration(); //Create configuration
		$options = $this->getOptions(); //Read options (defined in application.ini)
		//$this->registerAutoloaders($options); //Registering autoloader namespaces

		/**
		 * Call setters for option values
		 */
		foreach ($options['config'] as $option => $value) {
			$method = "set" . ucfirst($option);
			$config->{$method}($value);
		}

		$sc=Zend_Registry::get('CsbConfig');
		
		MongoDB\Types\Type::registerType(MongoDB\Types\Type::HASH, \CB\Resource\Odm\Type\HashType::class);
		MongoDB\Types\Type::registerType(MongoDB\Types\Type::DATE, \CB\Resource\Odm\Type\DateType::class);
		$reader=new FileCacheReader(
			new AnnotationReader(),
			APPLICATION_PATH.'/models/cache/annotation',
			!((bool) $sc->get('cache')->get('caching'))
		);
		$driverChain = new \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain();
		$annotationDriver=new \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver($reader);
		
		spl_autoload_register($config->getProxyManagerConfiguration()->getProxyAutoloader());
		
		$composerAutoloader = function (){};
		foreach ($this->getBootstrap()->getApplication()->getAutoloader()->getAutoloaders() as $autoloader) {
			if ($autoloader instanceof \Composer\Autoload\ClassLoader) {
				$composerAutoloader = $autoloader;
				break;
			}
		}
		\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$composerAutoloader, 'loadClass']);
		$driverChain->addDriver($annotationDriver, 'CB');
		$config->setMetadataDriverImpl($driverChain);


		$config->setDefaultDB(($database=$sc->get('mongo')->get('defaultdb', 'csb')));
		$username=$sc->get('mongo')->get('username');
		$password=$sc->get('mongo')->get('password');
		$host=$sc->get('mongo')->get('host', 'localhost');
		$port=$sc->get('mongo')->get('port', '27017');
		
		$dm = MongoDB\DocumentManager::create(new \MongoDB\Client(
			'mongodb://'.$username.':'.urlencode($password).'@'.$host.':'.$port.'/'.$database.'',
			[],
			['typeMap' => MongoDB\DocumentManager::CLIENT_TYPEMAP]
		), $config);

		//$dm->getEventManager()->addEventListener(array(\Doctrine\ODM\MongoDB\Events::preUpdate, \Doctrine\ODM\MongoDB\Events::prePersist), new \CB_Resource_Doctrine_Evm());

		return $dm;
	}

	/**
	 * Registering autoloader namespaces
	 * @param array $options
	 */
	public function registerAutoloaders($options)	{
		$autoloader = \Zend_Loader_Autoloader::getInstance();
		$classLoader = new ClassLoader($options['documents']['namespace'], $options['documents']['dir']);
		$autoloader->pushAutoloader(array($classLoader, 'loadClass'), $options['documents']['namespace']);
	}
}



class CB_Resource_Doctrine_Evm {

	/**
	 * @var SRG_Resource_Cache
	 */
	private $_cache;

	public function __construct(){
		//$this->_cache=Zend_Registry::get('cache')->getCache('general');
	}

	public function preUpdate(\Doctrine\ODM\MongoDb\Event\LifecycleEventArgs $eventArgs){
		//$this->_invalidateCache($eventArgs->getDocument());
	}
	public function prePersist(\Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $eventArgs){
		//$this->_invalidateCache($eventArgs->getDocument());
	}

	private function _invalidateCache($document){
		$className=get_class($document);
		$classNameEnd=substr($className, strrpos($className, '\\')+1);
		if(!empty($this->keys[$classNameEnd])){
			foreach($this->keys[$classNameEnd] as $cacheKey){
				$this->_cache->remove($cacheKey);
			}
		}
	}

	private $keys=array(
		'Page'=>array('navigation','acl'),
		'Gallery'=>array('navigation'),
		'GalleryFolder'=>array('navigation'),
		'Post'=>array('navigation'),
		'PostEntry'=>array('navigation'),
		'Shop'=>array('navigation'),
		'ShopProduct'=>array('navigation'),
		'Social'=>array('navigation'),
		'Role'=>array('acl'),
		'Permission'=>array('acl')
	);
}

