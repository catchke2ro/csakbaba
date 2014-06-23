<?php
class CB_Resource_Auth implements Zend_Auth_Adapter_Interface {

	/**
	 * @var string Username
	 */
	private $email;

	/**
	 * @var string Password
	 */
	private $password;

	private $user;

	/**
	 * @param $username string Username
	 * @param $password string Password
	 */
	public function __construct($email='', $password=''){
		$this->email=$email;
		$this->password=$password;
	}

	/**
	 * Authentication method
	 * @var $field string
	 * @return Zend_Auth_Result
	 */
	public function authenticate(){
		$result=null;
		$userModel=new \CB\Model\User();
		$this->user=$userModel->findOneByEmail($this->email);

		if(empty($this->user)){
			$result = new Zend_Auth_Result(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, null);
		} else {
			$id=$this->user->id;
			if($this->user->password!=md5($this->password)){
				$result = new Zend_Auth_Result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, $id);
			} else if(!$this->user->active) {
				$result = new Zend_Auth_Result(Zend_Auth_Result::FAILURE, $id);
			} else {
				$result = new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $id, array());
				$this->_store();
			}
		}
		return $result;
	}

	public function logout(){
		$auth=Zend_Auth::getInstance();
		$auth->clearIdentity();
	}

	private function _store(){
		$namespace = new Zend_Session_Namespace('Zend_Auth');
		$namespace->setExpirationSeconds(86400);
		$auth=Zend_Auth::getInstance();
		$auth->getStorage()->write($this->_generateIdentity($this->user));
	}

	private function _generateIdentity($user){
		$identity['id']=$user->id;
		$identity['username']=$user->username;
		$identity['email']=$user->email;
		return $identity;
	}


	public function storeUser($user){
		$this->user=$user;
		$this->_store();
	}

	public function slogin($s, $suser){
		$userModel=new \CB\Model\User();

		if(empty($suser['email'])) return false;
		if(($user=$userModel->findOneBy($s.'id', $suser['id']))){

		} else if(($user=$userModel->findOneByEmail($suser['email']))) {
			$user->{$s.'id'}=$suser['id'];
			$userModel->save($user);
		} else if(!($user=$userModel->findOneBy($s.'id', $suser['id']))){
			$user=new \CB\User();
			$func='_'.$s.'ToUser';
			$user=$this->$func($user, $suser);
			$user->role='user';
			$userModel->save($user);
			$user=$userModel->findOneBy($s.'id', $suser['id']);
		}

		$user->date_last_login=date('Y-m-d H:i:s');
		$userModel->save($user);
		$this->storeUser($user);
		return true;
	}


	private function _fbToUser($user, $facebook){
		$user->username=$this->_username($facebook['username']);
		$user->address=$user->postaddres=array();
		$user->address['name']=$user->postaddress['name']=$facebook['name'];
		if(!empty($facebook['location']['name'])) $user->address['city']=$user->postaddress['city']=$facebook['location']['name'];
		$user->email=$facebook['email'];
		$user->fbid=$facebook['id'];
		$user->gender=$facebook['gender'];
		$user->active=true;
		$user->date_reg=date('Y-m-d H:i:s');
		return $user;
	}

	private function _gpToUser($user, $google){
		$user->username=$this->_username(reset(explode('@', $google['email'])));
		$user->address=$user->postaddress=array();
		$user->address['name']=$user->postaddress['name']=$google['name'];
		$user->email=$google['email'];
		$user->gpid=$google['id'];
		$user->active=true;
		$user->gender=$google['gender'];
		$user->date_reg=date('Y-m-d H:i:s');
		return $user;
	}


	private function _username($username){
		$username=preg_replace('/[^0-9a-zA-Z]+/i', '', $username);
		$userModel=new \CB\Model\User();
		if(!$userModel->findOneByUsername($username)){
			return $username;
		}
		$stop=false;
		$i=1;
		while($stop==false){
			$username=$username.$i;
			if(!$userModel->findOneByUsername($username)){
				$stop=true;
				return $username;
			}
		}
		return $username;
	}


}