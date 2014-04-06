<?php
class CB_Resource_Model{

	/**
	 * @var \Doctrine\ODM\MongoDB\DocumentManager Doctrine Document Manager
	 */
	public $dm;

	/**
	 * @var \CB_Resource_ModelItem() Repository (ModelItem) for this Model
	 */
	public $repository;

	/**
	 * @var Zend_Cache_Core
	 */
	public $cache;

	/**
	 * @var array Default options for queries
	 */
	private $defaultOptions=array(
		'conditions'=>null,
		'order'=>null,
		'fields'=>null,
		'limit'=>null,
		'toArray'=>true
	);

	private $operations=array('where', 'in', 'notIn', 'equals', 'notEqual', 'gt', 'gte', 'lt', 'lte', 'range', 'size', 'exists', 'type', 'all', 'mod');

	/**
	 * @var Doctrine\ODM\MongoDB\Query\Builder()
	 */
	public $qb='';

	/**
	 * Constructor for this resource
	 */
	function __construct(){
		$this->dm=\Zend_Registry::get('dm'); //Init Document Manager
		$this->repository=$this->dm->getRepository('CB\\'.end(explode('\\', get_class($this))));
		$cacheManager=Zend_Registry::get('cache');
		$this->cache=$cacheManager->getCache('general');
	}

	/**
	 * Init function for this resource
	 */
	function init(){}

	/**
	 * Saving document (ModelItem)
	 * @param \CB_Resource_ModelItem() $data The saved data object
	 * @param boolean $onlyOneDocumentFlush Language of data
	 */
	public function save($data=null, $onlyOneDocumentFlush=false){
		$documentName=$this->repository->getDocumentName();
		if(is_object($data)){
			$document=$data;
		}
		if(is_array($data)){
			$document=new $documentName();
			foreach($data as $key=>$value){
				$document->$key=$value;
			}
		}
		if(!empty($document)){
			$this->dm->persist($document);
			$flushDocument=($onlyOneDocumentFlush) ? $document : null;
			$this->dm->flush($flushDocument);
			return $document;
		}
		return false;
	}

	public function initQb(){
		$this->qb=$this->dm->createQueryBuilder($this->repository->getDocumentName());
		return $this->qb;
	}

	public function runQuery(){
		$items=$this->qb->getQuery()->execute(); //Execute query
		$items=$this->afterFind($items->toArray()); //Run afterFind callback
		return $items;
	}

	/**
	 * Find by given options
	 * @param array $options Options of query
	 * @return Array|CB_Resource_ModelItem()[] Array of result ModelItems
	 */
	public function find($options=array()){
		$options=array_merge($this->defaultOptions, $options); //Extend default options
		$this->qb=$this->dm->createQueryBuilder($this->repository->getDocumentName()); //Create query builder
		$this->_buildOptions($options); //Organizing options
		$items=$this->qb->getQuery()->execute(); //Execute query

		if($options['toArray']){
			$items=$this->afterFind($items->toArray()); //Run afterFind callback
		}
		return $items;
	}

	/**
	 * Find first document by given options
	 * @param array $options Options of query
	 * @return CB_Resource_ModelItem() First item of query result
	 */
	public function findOne($options=array()){
		$options['limit']=1;
		$items=$this->find($options);
		$items=is_array($items) ? $items : $items->toArray();
		return reset($items);
	}

	/**
	 * Find first document by given ID
	 * @param string $id
	 * @return CB_Resource_ModelItem() Result item
	 */
	public function findOneById($id){
		$options['conditions']=array('id'=>$id);
		$item=$this->findOne($options);
		return $item;
	}

	/**
	 * Find first document by given field
	 * @param string $field
	 * @param string $value
	 * @return CB_Resource_ModelItem() Result item
	 */
	public function findOneBy($field='id', $value=''){
		$options['conditions']=array($field=>$value);
		$item=$this->findOne($options);
		return $item;
	}

	/**
	 * Find first document by given Slug
	 * @param string $slug
	 * @return CB_Resource_ModelItem() Result item
	 */
	public function findOneBySlug($slug){
		$options['conditions']=array('slug'=>$slug);
		$item=$this->findOne($options);
		return $item;
	}

	/**
	 * Find list based on the given fields
	 * @param array $options Options of query
	 * @return array Result list
	 */
	public function findList($options=array()){
		if(empty($options['fields'])){$options['fields']=array('id', 'id');}
		elseif(count($options['fields'])<2){$options['fields'][1]='id';}
		$items=$this->find($options);
		$list=array();
		/**
		 * Convert results to list
		 */
		foreach(is_array($items) ? $items : $items->toArray() as $item){
			$list[$item->{$options['fields'][0]}]=$item->{$options['fields'][1]};
		}
		return $list;
	}

	public function findEmbed($field, $conditionField, $value, $operation='equals'){
		$this->initQb();
		$this->qb->field($field)->elemMatch($this->qb->expr()->field($conditionField)->$operation($value));
		return $this->runQuery();
	}

	/**
	 * @param CB_Resource_ModelItem()|string $data The document to be deleted or the ID of the document
	 */
	public function delete($data){
		if(is_object($data)){
			$document=$data; //$data is a document
		} else {
			$document=$this->findOneById($data); //$data is an ID
		}
		$continue=$this->beforeDelete($document); //Run afterFind callback
		if($continue){
			$this->dm->remove($document);
			$this->dm->flush();
		}
	}

	/**
	 * Orgaizing options, apply it to query builder
	 * @param array $options Options of query
	 */
	protected function _buildOptions($options){
		if($options['conditions']!=null){
			foreach($options['conditions'] as $field=>$value){
				if($field==='OR'){
					foreach($value as $orField=>$orValue){
						$this->_condition($orField, $orValue, true);
					}
					continue;
				}
				$this->_condition($field, $value);
			}
		}

		if($options['fields']!=null){
			foreach($options['fields'] as $field){
				$this->qb->select($field);
			}
		}

		if($options['limit']!=null){
			$this->qb->limit($options['limit']);
		}

		$this->qb->sort(array());
		if($options['order']!=null){
			$order=explode(' ', $options['order']);
			$this->qb->sort($order[0], $order[1]);
		}

	}

	private function _condition($field, $value, $or=false){
		$operation='equals';
		if(is_numeric($field) && is_array($value) && count($value)==1){
			$field=key($value);
			$value=$value[key($value)];
		}
		if(is_array($value) && count($value)==1 && in_array(key($value), $this->operations)){
			$operation=key($value);
			$value=$value[key($value)];
		}
		($or) ? $this->qb->addOr($this->qb->expr()->field($field)->$operation($value)) : $this->qb->field($field)->$operation($value);
	}

	/**
	 * Callback after finding documents for ext. Handle DateTime object
	 * @param \CB_Resource_ModelItem()[]|\CB_Resource_ModelItem() $items Array of documents or single document
	 * @param array $params Sepcial params for callback
	 * @return \CB_Resource_ModelItem()[]|\CB_Resource_ModelItem() Array of documents or single document
	 */
	public function extAfterFind($items, $params=array()){
		if(isset($params['single'])){ $items=array($items); }
		foreach($items as $key=>$item){
			foreach(get_object_vars($item) as $field=>$value){
				if(is_object($value) && get_class($value)=='DateTime'){
					$value->__wakeup();
					$items[$key]->$field=$value->date;
				}
			}
		}
		if(isset($params['single'])){ $items=$items[0]; }
		return $items;
	}

	/**
	 * Callback before saving documents for ext.
	 * @param \CB_Resource_ModelItem()[]|\CB_Resource_ModelItem() $items Array of documents or single document
	 * @param array $params Special params for callback
	 * @return \CB_Resource_ModelItem()[]|\CB_Resource_ModelItem() Array of documents or single document
	 */
	public function extBeforeSave($items, $params){
		return $items;
	}

	/**
	 * Callback after saving documents for ext.
	 * @param \CB_Resource_ModelItem()[]|\CB_Resource_ModelItem() $items Array of documents or single document
	 * @param array $params Special params for callback
	 */
	public function extAfterSave($items, $params){}

	/**
	 * Callback after finding documents.
	 * @param \CB_Resource_ModelItem()[]|\CB_Resource_ModelItem() $items Array of documents or single document
	 * @return \CB_Resource_ModelItem()[]|\CB_Resource_ModelItem() Array of documents or single document
	 */
	public function afterFind($items){
		foreach($items as $key=>$item){
			$items[$key]->get();
		}
		return $items;
	}

	/**
	 * Callback after finding documents.
	 * @param \CB_Resource_ModelItem()[]|\CB_Resource_ModelItem() $items Array of documents or single document
	 * @return \CB_Resource_ModelItem()[]|\CB_Resource_ModelItem() Array of documents or single document
	 */
	public function beforeDelete($document){
		return true;
	}

	/**
	 * Generate children items for navigation object.
	 * @param \CB\Page() $page Page of this content type
	 * @param \CB_Resource_ModelItem() $content The content type
	 * @param \Zend_Navigation_Page_Uri() $parent Parent page in the navigation tree
	 * @param \Zend_Navigation() $navigation The navigation instance
	 */
	public function navigationChildren($page, $content, $parent, $navigation){}


}