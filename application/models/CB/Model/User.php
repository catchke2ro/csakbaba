<?php
namespace CB\Model;

class User extends \CB_Resource_Model{

	public function findOneByEmail($email){
		return $this->findOneBy('email', $email);
	}

	public function findOneByUsername($username){
		return $this->findOneBy('username', $username);
	}

	public function getPromoted($type='allfirst'){
		$this->initQb();
		$this->qb->field('promotes.'.$type)->gte(time());
		$this->qb->field('active')->equals(true);
		$users=$this->runQuery();
		shuffle($users);
		return $users ? $users : array();
	}
	
	public function extAfterFind($items, $params=array()){
		if(isset($params['single'])){ $items=array($items); }
		foreach($items as $key=>$item){
			$items[$key]->paymentid = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $item->username), 0, 4).substr(md5($item->id), 0, 4)) ?: '';
		}
		$items=array_values($items);
		if(isset($params['single'])){ $items=$items[0]; }
		return parent::extAfterFind($items, $params);
	}

}
