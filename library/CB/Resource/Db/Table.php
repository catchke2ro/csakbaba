<?

class CB_Resource_Db_Table extends Zend_Db_Table_Abstract {

	private $_mapperName;

	public function __construct($config=array()){
		$this->_mapperName='CB_Model_Mapper_'.end(explode('_', get_class($this)));
		parent::__construct($config);
	}

	public function getMapper(){
		$mapperName=$this->_mapperName;
		return new $mapperName();
	}


}