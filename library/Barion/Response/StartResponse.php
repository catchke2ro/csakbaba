<?php

namespace Barion\Response;

use Barion\Barion;

class StartResponse extends Response{


	public function getBarionPaymentId(){
		if(!empty($this->rawResponse['PaymentId'])){
			return $this->rawResponse['PaymentId'];
		}
		return false;
	}

	public function getStatus(){
		if(!empty($this->rawResponse['Status'])){
			return $this->rawResponse['Status'];
		}
		return false;
	}

	public function getRedirectUrl(Barion $barion){
		return $barion->getBarionRedirectUrl().$this->getBarionPaymentId();
	}

}