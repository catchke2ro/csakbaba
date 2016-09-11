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
	 * @ODM\Date
	 */
	public $date;

	/**
	 * @ODM\ReferenceOne(targetDocument="User", simple=true)
	 */
	public $user;

	/**
	 * @ODM\String
	 */
	public $product_id;

	/**
	 * @ODM\String
	 */
	public $post_id;

	/**
	 * @ODM\String
	 */
	public $text;

	/**
	 * @ODM\Boolean
	 */
	public $moderated;



}
