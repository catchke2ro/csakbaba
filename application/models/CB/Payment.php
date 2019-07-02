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
	 * @ODM\Field(type="string")
	 */
	public $pid;

	/**
	 * @ODM\Field(type="string")
	 */
	public $bpid;

	/**
	 * @ODM\EmbedOne(targetDocument="CB\User")
	 */
	public $user;

	/**
	 * @ODM\Field(type="date")
	 */
	public $date;

	/**
	 * @ODM\Field(type="int")
	 */
	public $amount;

	/**
	 * @ODM\Field(type="int")
	 */
	public $status;

	/**
	 * @ODM\Field(type="hash")
	 */
	public $invoice_data;

	/**
	 * @var @ODM\Field(type="hash")
	 */
	public $barion_data;

	/**
	 * @ODM\Field(type="string")
	 */
	public $type;


}
