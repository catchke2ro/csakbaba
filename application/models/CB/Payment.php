<?php
namespace CB;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="payments")
 */
class Payment extends \CB_Resource_ModelItem {

	/**
	 * @ODM\Id
	 */
	public $id;

	/**
	 * @ODM\String
	 */
	public $pid;

	/**
	 * @ODM\String
	 */
	public $bpid;

	/**
	 * @ODM\EmbedOne(targetDocument="User")
	 */
	public $user;

	/**
	 * @ODM\Date
	 */
	public $date;

	/**
	 * @ODM\Int
	 */
	public $amount;

	/**
	 * @ODM\Int
	 */
	public $status;

	/**
	 * @ODM\Hash
	 */
	public $invoice_data;

	/**
	 * @var @ODM\Hash
	 */
	public $barion_data;

	/**
	 * @ODM\STRING
	 */
	public $type;


}
