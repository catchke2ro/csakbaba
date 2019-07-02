<?php
namespace CB;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="orders")
 */
class Order extends \CB_Resource_ModelItem {

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
	public $code;

	/**
	 * @ODM\EmbedOne(targetDocument="CB\Product")
	 */
	public $product;

	/**
	 * @ODM\EmbedOne(targetDocument="CB\User")
	 */
	public $user;

	/**
	 * @ODM\EmbedOne(targetDocument="CB\User")
	 */
	public $shop_user;

	/**
	 * @ODM\ReferenceOne(targetDocument="CB\Rating")
	 */
	public $user_rating;

	/**
	 * @ODM\ReferenceOne(targetDocument="CB\Rating")
	 */
	public $shop_user_rating;

	/**
	 * @ODM\Field(type="int")
	 */
	public $orderEmail;


}
