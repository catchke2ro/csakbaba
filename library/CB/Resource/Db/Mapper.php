<?php 

class CB_Resource_Db_Mapper {

	protected $_tableName;

	/**
	 * @var Zend_Db_Table_Abstract
	 */
	protected $_dbTable;
	protected $_primary='id';

	public function __construct(){
		$classExploded = explode('_', get_class($this));
		$classname=end($classExploded);
		if($this->_tableName===null){
			$this->_tableName=strtolower($classname);
		}
		$dbTableName=Zend_Controller_Front::getInstance()->getParam('bootstrap')->getAppNamespace().'_Model_'.ucfirst($classname);
		$this->_dbTable=new $dbTableName;
	}

	public function save($row){
		if(! $row instanceof Zend_Db_Table_Row_Abstract){
			$data=$row;
			if(!empty($row[$this->_primary])){
				$row=$this->findOneById($row[$this->_primary]);
				$row->setFromArray($data);
			} else {
				$row=$this->_dbTable->createRow($data);
			}
		}
		$row=$this->beforeSave($row);
		if(!empty($row->{$this->_primary})){
			$row=$this->beforeUpdate($row);
			$primary=$row->save();
		} else {
			$row=$this->beforeAdd($row);
			$primary=$row->save();
		}
		$row->__set($this->_primary, $primary);
		return $row;
	}

	/**
	 * @param $options array
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function find($options=array()){
		$result=false;
		if($options && !is_array($options)){
			$result=$this->_dbTable->find($primary);
		} else if(is_array($options)) {
			$select=$this->_dbTable->select();
			$select->setIntegrityCheck(false);
			$select->from(array(substr($this->_tableName, 0, 1)=>$this->_tableName));
			if(!empty($options['conditions'])){	$this->_buildConditions($select, $options['conditions']);	}
			if(!empty($options['join'])){ $select->join($options['join'][0], $options['join'][1]); }
			if(!empty($options['joinLeft'])){ $select->joinLeft($options['join'][0], $options['join'][1]); }
			if(!empty($options['order'])){ $select->order($options['order']);	}
			if(!empty($options['limit'])){ $select->limit($options['limit']);	}
 			$result=$this->_dbTable->fetchAll($select);
		}
		if($result) $result=$this->afterFind($result, $options);
		return $result;
	}

	/**
	 * @param $options array
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function findOne($options=null){
		$options['limit']=1;
		$result=$this->find($options);
		return $result->count()>0 ? $result->getRow(0) : false;
	}

	/**
	 * @param $field string
	 * @param $value string
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function findOneBy($field, $value, $options=array()){
		$options['conditions']=array($field=>$value);
		return $this->findOne($options);
	}

	/**
	 * @param $value string
	 * @return Zend_Db_Table_Row_Abstract
	 */
	public function findOneById($value, $options=array()){
		$options['conditions']=array('id'=>$value);
		return $this->findOne($options);
	}

	public function findList($fields=array('id', 'name')){
		$all=$this->find();
		$list=array();
		$keyField=$fields[0];
		$valueField=$fields[1];
		foreach($all as $item){
			$list[$item->$keyField]=$valueField=='row' ? $item : $item->$valueField;
		}
		return $list;
	}


	public function beforeSave(Zend_Db_Table_Row_Abstract $row){
		return $row;
	}

	public function beforeAdd(Zend_Db_Table_Row_Abstract $row){
		return $row;
	}

	public function beforeUpdate(Zend_Db_Table_Row_Abstract $row){
		return $row;
	}

	public function afterFind($rowset){
		return $rowset;
	}

	public function getDbTable(){
		return $this->_dbTable;
	}


	public function createRowset(array $rows){
		foreach($rows as $key=>$row){
			if($row instanceof Zend_Db_Table_Row_Abstract) $rows[$key]=$row->toArray();
		}
		$rowset=new Zend_Db_Table_Rowset(array('table'=>$this, 'data'=>$rows));
		return $rowset;
	}



	private function _buildConditions($select, $conditions=array(), $type=null){
		foreach($conditions as $field=>$value){
			if($field==='OR'){
				$this->_buildConditions($select, $value, 'OR'); continue;
			}
			if(is_array($value)){
				$valueKeys = array_keys($value);
				$this->addWhere($select, reset($valueKeys), reset(array_values($value)), $type);
			} else {
				$this->addWhere($select, $field, $value, $type);
			}
		}
	}

	private function addWhere($select, $field, $value, $type){
		$f=strpos($field, ' ')===false ? $field.' = ?' : $field.' ?';
		if(is_numeric($field)) { $f=$value; $value=null; }
		if($type=='OR'){
			$select->orWhere($f, $value);
		} else {
			$select->where($f, $value);
		}
	}



/*	public function findOneBy*/



}