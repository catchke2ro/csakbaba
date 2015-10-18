<?php

namespace Barion\Payment;

use Barion\Barion;

class Payment {

	/**
	 * Default Options of payment requests
	 * @var array
	 */
	private $_defaultOptions;

	/**
	 * @var Barion Barion service
	 */
	private $_barion;

	/**
	 * @var string ID of payment transaction
	 */
	private $_paymentId;


	public function __construct(Barion $barion, $paymentId){
		$this->setPaymentId($paymentId);
		$this->setBarion($barion);
	}

	/**
	 * @param Barion $barion
	 * @return $this
	 */
	public function setBarion(Barion $barion){
		$this->_barion=$barion;
		return $this;
	}

	/**
	 * @return Barion
	 */
	public function getBarion(){
		return $this->_barion;
	}

	/**
	 * @param string $paymentId
	 * @return $this
	 */
	public function setPaymentId($paymentId){
		$this->_paymentId=$paymentId;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPaymentId(){
		return $this->_paymentId;
	}

}