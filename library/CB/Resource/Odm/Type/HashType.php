<?php

namespace CB\Resource\Odm\Type;

/**
 * Class Hash
 *
 * @package   SRG\Resource\Odm
 * @author    SRG Group <dev@srg.hu>
 * @copyright 2019 SRG Group Kft.
 * @Annotation
 */
class HashType extends \Doctrine\ODM\MongoDB\Types\HashType {

	public function convertToDatabaseValue($value)
	{
		/*if ($value !== null && ! is_array($value)) {
			throw MongoDBException::invalidValueForType('Hash', array('array', 'null'), $value);
		}*/
		return ($value !== null && $value != 'null' && $value != '') ? ($this->isAssoc($value) ? (object) $value : (array) $value) : null;
	}

	public function convertToPHPValue($value)
	{
		return $value !== null ? (array) $value : null;
	}

	private function isAssoc($arr){
		return array_keys($arr) !== range(0, count($arr) - 1);
	}

}