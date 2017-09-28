<?php
namespace CB;

use CB\Model\Comment as CommentModel;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="services")
 */
class Service extends \CB_Resource_ModelItem {
	
	use ProductServiceCommon;

	/**
	 * @ODM\Id
	 */
	public $id;

	/**
	 * @ODM\String
	 */
	public $name;

	/**
	 * @ODM\String
	 */
	public $search_name;

	/**
	 * @ODM\String
	 */
	public $desc;
	
	/**
	 * @ODM\ReferenceOne(targetDocument="User", simple=true)
	 */
	public $user;

	/**
	 * @ODM\Hash
	 */
	public $images;

	/**
	 * @ODM\Int
	 */
	public $price;

	/**
	 * @ODM\Date
	 */
	public $date_added;

	/**
	 * @ODM\Date
	 */
	public $date_period;

	/**
	 * @ODM\Hash
	 */
	public $options;

	/**
	 * @ODM\Int
	 */
	public $status;

	/**
	 * @ODM\Int
	 */
	public $visitors;

	/**
	 * @ODM\Hash
	 */
	public $promotes;

	/**
	 * @ODM\String
	 */
	public $autorenew;

	/**
	 * @ODM\Boolean
	 */
	public $deleted;

	/**
	 * @ODM\String
	 */
	public $code;
	
	
	public function fromEditForm($form, $user){
		/**
		 * @var $form \Frontend_Form_ProductEdit
		 * @var $user User
		 */
		$values = $form->getValues();
		
		$edit = !empty($values['id']);
		
		$userActiveProductsCount = $user->getActiveProductsCount();
		
		
		if($values['moreopened']){
			foreach($values as $fieldId=>$value){
				if(strpos($fieldId, 'options_')!==false){
					if(empty($values['options'])) $values['options']=array();
					$values['options'][str_replace('options_', '', $fieldId)]=$value;
					unset($values[$fieldId]);
				}
			}
		}
		
		$values['images']=is_array($values['images']) ? array_filter($values['images']) : array();
		$values['user']=$user;
		$values['category']=$values['category_id'];
		$values['search_name']=strtolower($values['name']);
		
		if(!$edit){
			$values['date_added']=new \DateTime(date('Y-m-d H:i:s'));
			$values['date_period']=new \DateTime(date('Y-m-d H:i:s'));
			$values['status']=1;
			$values['code']=uniqid('CSB');
			\CB_Resource_Functions::logEvent('userProductAddStarted');
		} else {
			\CB_Resource_Functions::logEvent('userProductEditStarted');
		}
		
		$this->saveAll($values);
		
		if(!$edit){
			if($userActiveProductsCount >= \Zend_Registry::get('freeUploadLimit')){
				$user->modifyBalance(-\Zend_Registry::get('uploadPrice'));
			}
		}
		
		$promoted = null;
		if(!empty($values['promote_types'])){
			$promoted = $this->_processPromotes($values['promote_types'], $user);
		}
		
		
		return [$promoted];
	}
	

}
