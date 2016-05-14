<?php

namespace Barion;

class Request {

	/**
	 * @var Barion Barion instance
	 */
	private $_barion;

	private $_action;

	private $_params;

	private $_method;

	public function __construct(Barion $barion=null, $action=null, $params=null, $method='POST'){
		$this->setBarion($barion);
		$this->setAction($action);
		$this->setParams($params);
		$this->setMethod($method);
	}


	public function send(){
		$ch = curl_init();


		$url=$this->getBarion()->getApiUrl().'/'.$this->getAction();

		if($this->getMethod()=='GET'){
			$url.='?'.http_build_query($this->getParams());
		}

		curl_setopt($ch, CURLOPT_URL, $url);

		if($this->getMethod()=='POST'){
			$request_json=json_encode($this->getParams());
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request_json);
			curl_setopt($ch, CURLOPT_HTTPHEADER, Array( // a kérésben jelezni kell, hogy ez egy JSON formátumú tartalom
				'Content-Type: application/json',
				'Content-Length: ' . strlen($request_json)
			));
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);



		$response = curl_exec($ch); // a $response object tartalmazza a Barion API által adott választ

		curl_close($ch);

		if($response===false) $response=curl_error($ch);

		return $response;
	}

	/**
	 * @return Barion
	 */
	public function getBarion()
	{
		return $this->_barion;
	}

	/**
	 * @param Barion $barion
	 * @return Request
	 */
	public function setBarion($barion)
	{
		$this->_barion=$barion;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAction()
	{
		return $this->_action;
	}

	/**
	 * @param mixed $action
	 * @return Request
	 */
	public function setAction($action)
	{
		$this->_action=$action;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getParams()
	{
		return $this->_params;
	}

	/**
	 * @param mixed $params
	 * @return Request
	 */
	public function setParams($params)
	{
		$this->_params=$params;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMethod()
	{
		return $this->_method;
	}

	/**
	 * @param mixed $method
	 * @return Request
	 */
	public function setMethod($method)
	{
		$this->_method=$method;
		return $this;
	}



}