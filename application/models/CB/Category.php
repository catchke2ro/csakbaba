<?php
namespace CB;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="categories")
 */
class Category extends \CB_Resource_ModelItem {

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
	public $slug;

	/**
	 * @ODM\String
	 */
	public $parent_id;

	/**
	 * @ODM\ReferenceMany(targetDocument="Category", simple=true)
	 */
	public $children;

	/**
	 * @ODM\Int
	 */
	public $o;

	/**
	 * @ODM\Boolean
	 */
	public $active;

	/**
	 * @ODM\Hash
	 */
	public $options;

	/**
	 * @ODM\Hash
	 */
	public $images;

}
