<?php
namespace CB;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="feeds")
 */
class Feed extends \CB_Resource_ModelItem {

	/**
	 * @ODM\Id
	 */
	public $id;

	/**
	 * @ODM\Field(type="date")
	 */
	public $date;

	/**
	 * @ODM\Field(type="string")
	 */
	public $user_id;

	/**
	 * @ODM\Field(type="string")
	 */
	public $product_id;

	/**
	 * @ODM\Field(type="string")
	 */
	public $product_name;

	/**
	 * @ODM\Field(type="string")
	 */
	public $type;

	/**
	 * @ODM\Field(type="boolean")
	 */
	public $read;



}
