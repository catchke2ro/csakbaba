<?php
namespace CB;

use CB\Model\Comment;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="products")
 */
class Product extends \CB_Resource_ModelItem {

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
	 * @ODM\String
	 */
	public $category;

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
	 * @ODM\String
	 */
	public $type;

	/**
	 * @ODM\Boolean
	 */
	public $new;

	/**
	 * @ODM\Int
	 */
	public $visitors;

	/**
	 * @ODM\Hash
	 */
	public $promotes;

	/**
	 * @ODM\Hash
	 */
	public $deliveries;

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


	public function isPromoted($type='first'){
		return (!empty($this->promotes[$type]) && $this->promotes[$type]>=time());
	}

	public function getComments(){
		$commentModel=new \CB\Model\Comment();
		$comments=$commentModel->find(array('conditions'=>array('product_id'=>$this->id), 'order'=>'date ASC'));
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


    private function _processPromotes($promoteTypes = [], $user){
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
