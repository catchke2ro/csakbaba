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
	 * @ODM\Field(type="string")
	 */
	public $name;

	/**
	 * @ODM\Field(type="string")
	 */
	public $slug;

	/**
	 * @ODM\Field(type="string")
	 */
	public $parent_id;

	/**
	 * @ODM\ReferenceMany(targetDocument="CB\Category", storeAs="id")
	 */
	public $children;

	/**
	 * @ODM\Field(type="int")
	 */
	public $o;

	/**
	 * @ODM\Field(type="boolean")
	 */
	public $active;

	/**
	 * @ODM\Field(type="hash")
	 */
	public $options;

	/**
	 * @ODM\Field(type="hash")
	 */
	public $images;

}
