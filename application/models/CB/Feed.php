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
	 * @ODM\Date
	 */
	public $date;

	/**
	 * @ODM\String
	 */
	public $user_id;

	/**
	 * @ODM\String
	 */
	public $product_id;

	/**
	 * @ODM\String
	 */
	public $product_name;

	/**
	 * @ODM\String
	 */
	public $type;

	/**
	 * @ODM\Boolean
	 */
	public $read;



}
