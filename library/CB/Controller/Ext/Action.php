<?php
/**
 * Class CB_Controller_Ext_Action
 * @author CB Group
 * Action controller for Ext module's methods
 */
abstract class CB_Controller_Ext_Action extends Zend_Controller_Action
{
	/**
	 * @var Zend_Application Bootstrap
	 */
	protected $bootstrap;

	/**
	 * @var \Doctrine\ODM\MongoDB\DocumentManager Doctrine MongoDB Document Manager
	 */
	protected $dm;

	/**
	 * @var array Fetched ext POST
	 */
	public $extPost;

	/**
	 * @var array Ext return data
	 */
	public $returnArray;

	/**
	 * @param Zend_Controller_Request_Abstract $request Request
	 * @param Zend_Controller_Response_Abstract $response Response
	 * @param array $invokeArgs
	 */
	public function __construct(\Zend_Controller_Request_Abstract $request, \Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {

		/**
		 * Init request and response
		 */
		$this->setRequest($request)->setResponse($response)->_setInvokeArgs($invokeArgs);
		$response = $this->getResponse();
		$response->setHeader('Content-type', 'text/html', true);

		/**
		 * Disable layout and rendering (only JSON response)
		 */
		$this->_helper = new \Zend_Controller_Action_HelperBroker($this);
		$this->_helper->layout()->disableLayout();
		$this->getHelper('viewRenderer')->setNoRender();

		/**
		 * Set document manager as a property
		 */
		$this->dm = $this->getBootstrap()->getResource('odm');

		/**
		 * Decode POST if valid JSON and set the extPost variable
		 */
		json_decode($this->_request->getRawBody());
		if(json_last_error()==JSON_ERROR_NONE){
			$this->extPost=Zend_Json::decode($this->_request->getRawBody());
		}

		/**
		 * Call init function
		 */
		$this->init();
	}

	/**
	 * Callback after dispatch
	 */
	public function postDispatch(){
		/**
		 * Encode return array to JSON
		 */
		if(!empty($this->returnArray)){
			echo Zend_Json::encode($this->returnArray);
		}
	}

	/**
	 * Get bootstrap instance
	 * @return mixed|Zend_Application
	 */
	public function getBootstrap(){
		if (null === $this->bootstrap) {
			$this->bootstrap = $this->getInvokeArg('bootstrap');
		}
		return $this->bootstrap;
	}

}

