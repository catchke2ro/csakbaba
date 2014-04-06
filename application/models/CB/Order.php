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
	 * @ODM\Date
	 */
	public $date;

	/**
	 * @ODM\String
	 */
	public $code;

	/**
	 * @ODM\EmbedOne(targetDocument="Product")
	 */
	public $product;

	/**
	 * @ODM\EmbedOne(targetDocument="User")
	 */
	public $user;

	/**
	 * @ODM\EmbedOne(targetDocument="User")
	 */
	public $shop_user;

	/**
	 * @ODM\ReferenceOne(targetDocument="Rating")
	 */
	public $user_rating;

	/**
	 * @ODM\ReferenceOne(targetDocument="Rating")
	 */
	public $shop_user_rating;

	/**
	 * @ODM\Int
	 */
	public $orderEmail;


}
