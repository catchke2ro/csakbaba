<?php
namespace CB\Model;

class Comment extends \CB_Resource_Model{

	public function extAfterFind($items, $params=array()){
		if(isset($params['single'])){ $items=array($items); }

		foreach($items as $key=>$item){
			$items[$key]->user->get();
		}
		if(isset($params['single'])){ $items=$items[0]; }
		return parent::extAfterFind($items, $params);
	}


	public function extBeforeSave($items, $params){
		if(isset($params['single'])){ $items=array($items); }

		foreach($items as $key=>$item){
			if(!is_string($items[$key]->user)) {
				$userModel=new \CB\Model\User();
				$items[$key]->user=$userModel->findOneById($items[$key]->user['id']);
			}
		}
		if(isset($params['single'])){ $items=$items[0]; }
		return parent::extBeforeSave($items, $params);
	}

}
