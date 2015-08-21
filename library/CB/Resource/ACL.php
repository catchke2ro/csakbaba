<?php
class CB_Resource_ACL extends Zend_Acl {

	private $roleIds=array('user','public','admin');
	private $publicRoleIds=array('user','public');
	private $adminRoleIds=array('admin');

	public function __construct(){
		$this->initRoles();
		$this->initResources();
		$this->allow();
	}

	public function initRoles(){
		$this->addRole('public');
		$this->addRole('user', 'public');
		$this->addRole('admin');
	}

	public function initResources(){
		if(!Zend_Registry::isRegistered('nav')) return;
		$nav=Zend_Registry::get('nav');
		$this->_generateResourceList($nav);
	}

	private function _generateResourceList($nav, $parent=null){
		foreach($nav->getPages() as $page){
			if(empty($page->get('resource'))) continue;
			$this->addResource($page->get('resource'), $parent);
			if(!empty($page->_pages)) $this->_generateResourceList($page, $page->get('resource'));
		}
	}

	public function initPermissions(){
		$this->deny('public', array('felhasznalo'));
		$this->allow('user', array('felhasznalo'));
	}

	public function initAdminPermissions(){
		$this->addResource('/cmsadmin');
		$this->addResource('/cmsadmin/index', '/cmsadmin');
		$this->addResource('/cmsadmin/index/login', '/cmsadmin/index');
		$this->deny($this->publicRoleIds, '/cmsadmin');
		$this->allow($this->publicRoleIds, '/cmsadmin/index/login');
		$this->allow($this->adminRoleIds, '/cmsadmin');
	}

	public function isAllowed($role = null, $resource = null, $privilege = null){
		return parent::isAllowed($role, $resource, $privilege);
	}


}
