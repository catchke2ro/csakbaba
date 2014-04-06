<?php
namespace CB;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="shops")
 */
class Shop extends \CB_Resource_ModelItem {

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
	public $desc;

	/**
	 * @ODM\String
	 */
	public $slug;

	/**
	 * @ODM\ReferenceOne(targetDocument="User", simple=true)
	 */
	public $user;

	/**
	 * @ODM\Hash
	 */
	public $address;

	/**
	 * @ODM\Date
	 */
	public $date_reg;

	/**
	 * @ODM\Hash
	 */
	public $image;


	public function getProducts(){
		$productModel=new \CB\Model\Product();
		return $productModel->find(array('conditions'=>array('shop'=>new \MongoId($this->id))));
	}




}
