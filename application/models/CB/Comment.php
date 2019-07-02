<?php
namespace CB;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="comments")
 */
class Comment extends \CB_Resource_ModelItem {

	/**
	 * @ODM\Id
	 */
	public $id;

	/**
	 * @ODM\Field(type="date")
	 */
	public $date;

	/**
	 * @ODM\ReferenceOne(targetDocument="CB\User", storeAs="id")
	 */
	public $user;

	/**
	 * @ODM\Field(type="string")
	 */
	public $product_id;

	/**
	 * @ODM\Field(type="string")
	 */
	public $post_id;

	/**
	 * @ODM\Field(type="string")
	 */
	public $text;

	/**
	 * @ODM\Field(type="boolean")
	 */
	public $moderated;



}
