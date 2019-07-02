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
	 * @ODM\Field(type="string")
	 */
	public $name;

	/**
	 * @ODM\Field(type="string")
	 */
	public $desc;

	/**
	 * @ODM\Field(type="string")
	 */
	public $slug;

	/**
	 * @ODM\ReferenceOne(targetDocument="CB\User", storeAs="id")
	 */
	public $user;

	/**
	 * @ODM\Field(type="hash")
	 */
	public $address;

	/**
	 * @ODM\Field(type="date")
	 */
	public $date_reg;

	/**
	 * @ODM\Field(type="hash")
	 */
	public $image;


	public function getProducts(){
		$productModel=new \CB\Model\Product();
		return $productModel->find(array('conditions'=>array('shop'=>new \MongoDB\BSON\ObjectId($this->id))));
	}




}
