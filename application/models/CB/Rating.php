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
	 * @ODM\Field(type="date")
	 */
	public $date;

	/**
	 * @ODM\Field(type="boolean")
	 */
	public $seller;

	/**
	 * @ODM\Field(type="boolean")
	 */
	public $success;

	/**
	 * @ODM\Field(type="boolean")
	 */
	public $positive;

	/**
	 * @ODM\Field(type="string")
	 */
	public $text;

	/**
	 * @ODM\EmbedOne(targetDocument="CB\Product")
	 */
	public $product;


}
