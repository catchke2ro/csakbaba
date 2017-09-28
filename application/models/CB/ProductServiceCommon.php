<?php

namespace CB;

trait ProductServiceCommon {
	
	public function isService() {
		return get_class($this) == 'CB\\Service';
	}
	
	public function isPromoted($type='first'){
		return (!empty($this->promotes[$type]) && $this->promotes[$type]>=time());
	}
	
	public function getComments(){
		$commentModel=new \CB\Model\Comment();
		$foreignKey = $this->isService() ? 'service_id' : 'product_id';
		$comments=$commentModel->find(array('conditions'=>array($foreignKey => $this->id), 'order'=>'date ASC'));
		return $comments ? $comments : array();
	}
	
	public function getActivePromotes(){
		$promoteOptions=\Zend_Registry::get('promoteOptions');
		$promotes = [];
		foreach($this->promotes ?: [] as $type=>$date){
			if($date < time()) continue;
			$promotes[] = [
				'name'=>$promoteOptions[$type],
				'date'=>(new \DateTime())->setTimestamp($date)
			];
		}
		return $promotes;
	}
	
	
	
	
	
	protected function _processPromotes($promoteTypes = [], $user){
		/**
		 * @var $user User
		 */
		\CB_Resource_Functions::logEvent('userProductPromoteStarted', array('product'=>$product));
		
		$promote=$this->promotes ?: [];
		$prices=\Zend_Registry::get('promoteOptionPrices');
		
		$price=0;
		$newPromote = false;
		foreach($promoteTypes as $type){
			if(!empty($promote[$type]) && $promote[$type] > time()) continue;
			$newPromote = true;
			$price+=$prices[$type];
			$promote[$type]=strtotime('+1 week');
		}
		$this->promotes = $promote;
		$user->modifyBalance(-$price);
		
		\CB_Resource_Functions::logEvent('userProductPromoteEnded', array('product'=>$product));
		
		
		return $newPromote;
	}
	
}