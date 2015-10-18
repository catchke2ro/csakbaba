<?php

namespace Barion\Payment;

class Transaction {

	/**
	 * @var string Transaction's ID
	 */
	private $_transactionId;

	/**
	 * @var string Payee's e-mail address
	 */
	private $_payee;

	/**
	 * @var int Total amount
	 */
	private $_total;

	/**
	 * @var string Comment of transaction
	 */
	private $_comment;

	/**
	 * @var Item[] Array of Items
	 */
	private $_items=[];


	public function __construct($options=[]){
		foreach($options as $key=>$value){
			if(method_exists($this, 'set'.$key)){
				$this->{'set'.$key}($value);
			}
		}
	}



	public function getRequestArray(){
		$array=[
			'POSTransactionId'=>$this->getTransactionId(),
			'Payee'=>$this->getPayee(),
			'Total'=>$this->getTotal(),
			'Comment'=>$this->getComment()
		];
		foreach($this->getItems() as $item){
			$array['Items'][]=$item->getRequestArray();
		}
		return $array;
	}


	/**
	 * @return string
	 */
	public function getTransactionId(){
		return $this->_transactionId;
	}

	/**
	 * @param string $transactionId
	 * @return $this
	 */
	public function setTransactionId($transactionId){
		$this->_transactionId=$transactionId;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPayee(){
		return $this->_payee;
	}

	/**
	 * @param string $payee
	 * @return $this
	 */
	public function setPayee($payee){
		$this->_payee=$payee;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getTotal(){
		return $this->_total;
	}

	/**
	 * @param int $total
	 * @return $this
	 */
	public function setTotal($total){
		$this->_total=$total;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getComment(){
		return $this->_comment;
	}

	/**
	 * @param string $comment
	 * @return $this
	 */
	public function setComment($comment){
		$this->_comment=$comment;
		return $this;
	}

	/**
	 * @param Item[] $items
	 * @return $this
	 */
	public function setItems(array $items=[]){
		$this->_items=$items;
		return $this;
	}

	/**
	 * @return Item[]
	 */
	public function getItems(){
		return $this->_items;
	}

	/**
	 * @param Item $item
	 * @return $this
	 */
	public function addItem(Item $item){
		$this->_items[]=$item;
		return $this;
	}


}