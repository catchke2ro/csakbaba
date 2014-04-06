<?php
/**
 * Class SRG_View_Helper_FlashMessages
 * @author SRG Group
 * Helper for Flash messages
 * @TODO Frontendre talán kell. Majd bekommentelem, ha értem hogy hova kell.
 */
class CB_View_Helper_Messenger{

	var $messenger;

	public function messenger($id=null){

		$this->messenger = Zend_Controller_Action_HelperBroker::getStaticHelper('Messenger');

		$messages=$this->messenger->getMessages($id);
		return $this->_renderMessages($messages);
	}

	private function _renderMessages($statusMessages){
		if(array_filter($statusMessages)==array()) return '';
		$output='<div class="flash visible">';
		foreach($statusMessages as $status=>$messages){
			if(empty($messages)) continue;
			$output.='<ul class="'.$status.'">';
			foreach($messages as $message){
				$output.=(is_array($message)) ? '<li class="'.(isset($message['id']) ? $message['id'] : '').'">'.$message['message'].'</li>' : '<li>'.$message.'</li>';
			}
			$output.='<li class="close">X</li></ul>';
		}
		$output.="</div>\r\n";
		return $output;
	}
}