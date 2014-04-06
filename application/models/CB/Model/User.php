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

}
