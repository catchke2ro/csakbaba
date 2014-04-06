<?php
namespace CB;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="ratings")
 */
class Rating extends \CB_Resource_ModelItem {

	/**
	 * @ODM\Id
	 */
	public $id;

	/**
	 * @ODM\Date
	 */
	public $date;

	/**
	 * @ODM\Boolean
	 */
	public $seller;

	/**
	 * @ODM\Boolean
	 */
	public $success;

	/**
	 * @ODM\Boolean
	 */
	public $positive;

	/**
	 * @ODM\String
	 */
	public $text;

	/**
	 * @ODM\EmbedOne(targetDocument="Product")
	 */
	public $product;


}
