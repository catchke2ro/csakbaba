<?php

namespace Barion;

class Barion {

	/**
	 * @var string Barion shop's POSKey
	 */
	private $_posKey;

	/**
	 * @var string Url of api calls
	 */
	private $_apiUrl='https://api.barion.com/v2';

	/**
	 * @var string Url of api calls
	 */
	private $_barionRedirectUrl='https://barion.com/pay?id=';



	/**
	 * @var string Url of callback
	 */
	private $_callbackUrl;

	/**
	 * @var string Url of redirect
	 */
	private $_redirectUrl;

	/**
	 * @var string Locale of payment area
	 */
	private $_locale;


	public function __construct($posKey=null, $locale='hu-HU'){
		$this->setPOSKey($posKey);
		$this->setLocale($locale);
	}

	/**
	 * @param string $posKey
	 * @return $this
	 */
	public function setPOSKey($posKey){
		$this->_posKey=$posKey;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPOSKey(){
		return $this->_posKey;
	}

	/**
	 * @param string $apiUrl
	 * @return $this
	 */
	public function setApiUrl($apiUrl){
		$this->_apiUrl=$apiUrl;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getApiUrl(){
		return $this->_apiUrl;
	}

	/**
	 * @return string
	 */
	public function getBarionRedirectUrl()
	{
		return $this->_barionRedirectUrl;
	}

	/**
	 * @param string $barionRedirectUrl
	 */
	public function setBarionRedirectUrl($barionRedirectUrl)
	{
		$this->_barionRedirectUrl=$barionRedirectUrl;
	}



	/**
	 * @param string $callbackUrl
	 * @return $this
	 */
	public function setCallbackUrl($callbackUrl){
		$this->_callbackUrl=$callbackUrl;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCallbackUrl(){
		return $this->_callbackUrl;
	}

	/**
	 * @param string $redirectUrl
	 * @return $this
	 */
	public function setRedirectUrl($redirectUrl){
		$this->_redirectUrl=$redirectUrl;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRedirectUrl(){
		return $this->_redirectUrl;
	}

	/**
	 * @param string $locale
	 * @return $this
	 */
	public function setLocale($locale){
		$this->_locale=$locale;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLocale(){
		return $this->_locale;
	}






}