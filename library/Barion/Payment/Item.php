<?php

namespace Barion\Payment;

class Item {

	/**
	 * @var string Item name
	 */
	private $_name;

	/**
	 * @var string Item description
	 */
	private $_description;

	/**
	 * @var int Item quantity
	 */
	private $_quantity;

	/**
	 * @var string Unit
	 */
	private $_unit;

	/**
	 * @var int Price per unit
	 */
	private $_unitPrice;

	/**
	 * @var int Total price
	 */
	private $_itemTotal;

	/**
	 * @var string SKU
	 */
	private $_sku;


	public function __construct($options=[]){
		foreach($options as $key=>$value){
			if(method_exists($this, 'set'.$key)){
				$this->{'set'.$key}($value);
			}
		}
	}


	public function getRequestArray(){
		$array=[
			'Name'=>$this->getName(),
			'Description'=>$this->getDescription(),
			'Quantity'=>$this->getQuantity(),
			'Unit'=>$this->getUnit(),
			'UnitPrice'=>$this->getUnitPrice(),
			'ItemTotal'=>$this->getItemTotal(),
			'SKU'=>$this->getSku()
		];
		return $array;
	}


	/**
	 * @return string
	 */
	public function getName(){
		return $this->_name;
	}

	/**
	 * @param string $name
	 * @return Item
	 */
	public function setName($name){
		$this->_name=$name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription(){
		return $this->_description;
	}

	/**
	 * @param string $description
	 * @return Item
	 */
	public function setDescription($description){
		$this->_description=$description;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getQuantity(){
		return $this->_quantity;
	}

	/**
	 * @param int $quantity
	 * @return Item
	 */
	public function setQuantity($quantity){
		$this->_quantity=$quantity;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getUnit(){
		return $this->_unit;
	}

	/**
	 * @param string $unit
	 * @return Item
	 */
	public function setUnit($unit){
		$this->_unit=$unit;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getUnitPrice(){
		return $this->_unitPrice;
	}

	/**
	 * @param int $unitPrice
	 * @return Item
	 */
	public function setUnitPrice($unitPrice){
		$this->_unitPrice=$unitPrice;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getItemTotal(){
		return $this->_itemTotal;
	}

	/**
	 * @param int $itemTotal
	 * @return Item
	 */
	public function setItemTotal($itemTotal){
		$this->_itemTotal=$itemTotal;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSku(){
		return $this->_sku;
	}

	/**
	 * @param string $sku
	 * @return Item
	 */
	public function setSku($sku){
		$this->_sku=$sku;
		return $this;
	}





}