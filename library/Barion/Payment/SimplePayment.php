<?php

namespace Barion\Payment;


use Barion\Barion;
use Barion\Request;
use Barion\Response\PaymentStateResponse;
use Barion\Response\StartResponse;

class SimplePayment extends Payment {

	private $_startOptions=[
		'PaymentType'=>'Immediate',
		'PaymentWindow'=>'00:30:00',
		'GuestCheckOut'=>true,
		'FundingSources'=>['All'],
	];

	/**
	 * @param Transaction[] $transactions
	 * @param array $options
	 * @return StartResponse
	 */
	public function startPayment($transactions=[], $options=[]){
		$this->_startOptions=array_replace_recursive($this->_startOptions, [
			'RedirectUrl'=>$this->getBarion()->getRedirectUrl(),
			'CallbackUrl'=>$this->getBarion()->getCallbackUrl(),
			'PaymentRequestId'=>$this->getPaymentId(),
			'Locale'=>$this->getBarion()->getLocale(),
			'POSKey'=>$this->getBarion()->getPOSKey()
		]);
		$options=array_replace_recursive($this->_startOptions, $options);

		foreach($transactions as $transaction){
			$options['Transactions'][]=$transaction->getRequestArray();
		}

		$request=new Request($this->getBarion(), 'payment/start', $options);
		$response=new StartResponse($request->send());

		return $response;
	}


	public function getPaymentState($barionPaymentId, $extraOptions=[]){
		$options=[
			'POSKey'=>$this->getBarion()->getPOSKey(),
			'PaymentId'=>$barionPaymentId
		];
		$options=array_replace_recursive($options, $extraOptions);

		$request=new Request($this->getBarion(), 'payment/getpaymentstate', $options, 'GET');
		$response=new PaymentStateResponse($request->send());

		return $response;
	}





}