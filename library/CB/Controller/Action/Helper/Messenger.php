<?php

class CB_Controller_Action_Helper_Messenger extends Zend_Controller_Action_Helper_FlashMessenger {

	/**
	 * @var Zend_Controller_Action_Helper_FlashMessenger
	 */
	protected $flashMessenger;

	private $namespaces=array('error','message');

	private $currentNS;

	public function messenger($message=null, $status='message', $id=null){
		if ($status=='message' && $message===null) {
			return $this;
		}
		if (!isset($this->flashMessenger[$status])) {
			$this->flashMessenger[$status]=$this->getActionController()->getHelper('FlashMessenger')->setNamespace('messenger_'.$status);
		}
		if ($message !== null) {
			if($id) $message=array('message'=>$message, 'id'=>$id);
			$this->flashMessenger[$status]->addMessage($message);
		}
		return $this->flashMessenger[$status];
	}

	public function getMessages($id=null){
		$this->flashMessenger=$this->getActionController()->getHelper('FlashMessenger');
		$return=array();
		foreach($this->namespaces as $ns){
			$this->currentNS='messenger_'.$ns;
			$this->flashMessenger->setNamespace($this->currentNS);
			$return[$ns]=$this->_filter(array_merge($this->flashMessenger->getMessages(), $this->flashMessenger->getCurrentMessages()), $id);
			if($this->flashMessenger->hasCurrentMessages()) $this->flashMessenger->clearCurrentMessages(); //@TODO Tuti jó lesz ez így?
		}
		return $return;
	}

	private function _filter($messages=array(), $id=null){
		if(!$id){
			$this->flashMessenger->clearMessages();
			return $messages;
		}
		$return=array();
		foreach($messages as $key=>$message){
			if(is_array($message) && isset($message['id']) && $message['id']==$id){
				$return[]=$message;
				$this->flashMessenger->deleteMessage($key);
			}
		}
		return $return;
	}


}
