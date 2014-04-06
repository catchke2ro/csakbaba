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
	 * @ODM\String
	 */
	public $event_name;

	/**
	 * @ODM\Date
	 */
	public $date;

	/**
	 * @ODM\Hash
	 */
	public $user;

	/**
	 * @ODM\String
	 */
	public $ip;

	/**
	 * @ODM\Hash
	 */
	public $server;

	/**
	 * @ODM\Hash
	 */
	public $post;

	/**
	 * @ODM\Hash
	 */
	public $get;

	/**
	 * @ODM\Hash
	 */
	public $params;

}
