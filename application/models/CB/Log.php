<?php
namespace CB;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="logs")
 */
class Log extends \CB_Resource_ModelItem {

	/**
	 * @ODM\Id
	 */
	public $id;

	/**
	 * @ODM\Field(type="string")
	 */
	public $event_name;

	/**
	 * @ODM\Field(type="date")
	 */
	public $date;

	/**
	 * @ODM\Field(type="hash")
	 */
	public $user;

	/**
	 * @ODM\Field(type="string")
	 */
	public $ip;

	/**
	 * @ODM\Field(type="hash")
	 */
	public $server;

	/**
	 * @ODM\Field(type="hash")
	 */
	public $post;

	/**
	 * @ODM\Field(type="hash")
	 */
	public $get;

	/**
	 * @ODM\Field(type="hash")
	 */
	public $params;

}
