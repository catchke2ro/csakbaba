<?php
namespace CB;

use CB\Model\Comment;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="products")
 */
class Product extends \CB_Resource_ModelItem {

	/**
	 * @ODM\Id
	 */
	public $id;

	/**
	 * @ODM\String
	 */
	public $name;

	/**
	 * @ODM\String
	 */
	public $search_name;

	/**
	 * @ODM\String
	 */
	public $desc;

	/**
	 * @ODM\String
	 */
	public $category;

	/**
	 * @ODM\ReferenceOne(targetDocument="User", simple=true)
	 */
	public $user;

	/**
	 * @ODM\Hash
	 */
	public $images;

	/**
	 * @ODM\Int
	 */
	public $price;

	/**
	 * @ODM\Date
	 */
	public $date_added;

	/**
	 * @ODM\Date
	 */
	public $date_period;

	/**
	 * @ODM\Hash
	 */
	public $options;

	/**
	 * @ODM\Int
	 */
	public $status;

	/**
	 * @ODM\String
	 */
	public $type;

	/**
	 * @ODM\Boolean
	 */
	public $new;

	/**
	 * @ODM\Int
	 */
	public $visitors;

	/**
	 * @ODM\Hash
	 */
	public $promotes;

	/**
	 * @ODM\Hash
	 */
	public $deliveries;

	/**
	 * @ODM\String
	 */
	public $autorenew;

	/**
	 * @ODM\Boolean
	 */
	public $deleted;

	/**
	 * @ODM\String
	 */
	public $code;


	public function isPromoted($type='first'){
		return (!empty($this->promotes[$type]) && $this->promotes[$type]>=time());
	}

	public function getComments(){
		$commentModel=new \CB\Model\Comment();
		$comments=$commentModel->find(array('conditions'=>array('product_id'=>$this->id), 'order'=>'date ASC'));
		return $comments ? $comments : array();
	}


}
