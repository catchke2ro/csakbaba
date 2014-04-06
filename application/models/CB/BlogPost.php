<?php
namespace CB;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="blog_posts")
 */
class BlogPost extends \CB_Resource_ModelItem {

	/**
	 * @ODM\Id
	 */
	public $id;

	/**
	 * @ODM\String
	 */
	public $title;

	/**
	 * @ODM\String
	 */
	public $slug;

	/**
	 * @ODM\Date
	 */
	public $date;

	/**
	 * @ODM\String
	 */
	public $teaser;

	/**
	 * @ODM\String
	 */
	public $body;


}
