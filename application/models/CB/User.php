<?php
namespace CB;

use CB\Model\Rating;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="users")
 */
class User extends \CB_Resource_ModelItem {

	/**
	 * @ODM\Id
	 */
	public $id;

	/**
	 * @ODM\Field(type="string")
	 */
	public $username;

	/**
	 * @ODM\Field(type="string")
	 */
	public $password;

	/**
	 * @ODM\Field(type="string")
	 */
	public $email;

	/**
	 * @ODM\Field(type="string")
	 */
	public $desc;

	/**
	 * @ODM\Field(type="string")
	 */
	public $gender;

	/**
	 * @ODM\Field(type="date")
	 */
	public $birth_date;

	/**
	 * @ODM\Field(type="string")
	 */
	public $phone;

	/**
	 * @ODM\Field(type="date")
	 */
	public $date_reg;

	/**
	 * @ODM\Field(type="date")
	 */
	public $date_last_login;

	/**
	 * @ODM\Field(type="boolean")
	 */
	public $active;

	/**
	 * @ODM\Field(type="string")
	 */
	public $activation_code;

	/**
	 * @ODM\Field(type="hash")
	 */
	public $address;

	/**
	 * @ODM\Field(type="hash")
	 */
	public $postaddress;

	/**
	 * @ODM\Field(type="hash")
	 */
	public $avatar;

	/**
	 * @ODM\Field(type="string")
	 */
	public $role;

	/**
	 * @ODM\Field(type="int")
	 */
	public $balance;

	/**
	 * @ODM\ReferenceMany(targetDocument="CB\Product")
	 */
	public $favourites;


	/**
	 * @ODM\Field(type="hash")
	 */
	public $promotes;


	/**
	 * @ODM\Field(type="string")
	 */
	public $fbid;


	/**
	 * @ODM\Field(type="string")
	 */
	public $gpid;


	/**
	 * @ODM\Field(type="string")
	 */
	public $billingoid;


	/**
	 * @ODM\Field(type="hash")
	 */
	public $subscribed;


	/**
	 * @ODM\Field(type="boolean")
	 */
	public $blogadmin;
	
	/**
	 * @ODM\Field(type="boolean")
	 */
	public $deleted;


	public function getRating($type='avg'){
		$orderModel=new \CB\Model\Order();
		$all=$positive=0;
		if($type=='avg' || $type=='buy'){
			$userOrdersBuy=$orderModel->find(array('conditions'=>array('user._id'=>new \MongoDB\BSON\ObjectId($this->id))));
			foreach($userOrdersBuy as $uob){
				if($uob->shop_user_rating){
					$all++;
					if($uob->shop_user_rating->get()->positive==true) $positive++;
				}
			}
		}
		if($type=='avg' || $type=='sell'){
			$userOrdersSell=$orderModel->find(array('conditions'=>array('shop_user._id'=>new \MongoDB\BSON\ObjectId($this->id))));
			foreach($userOrdersSell as $uos){
				if($uos->user_rating){
					$all++;
					if($uos->user_rating->get()->positive==true) $positive++;
				}
			}
		}
		return $all ? ($positive/$all) : 0;
	}


	public function getProducts($random=false, $count=10, $status=array(1)){
		$productModel=new \CB\Model\Product();
		$productModel->initQb();
		$productModel->qb->field('user')->equals(new \MongoDB\BSON\ObjectId($this->id));
		$productModel->qb->field('status')->in($status);
		$productModel->qb->sort('date_added', 'DESC');
		$products=$productModel->runQuery();
		if($random){
			shuffle($products);
			$products=array_slice($products, 0, $count);
		}
		return $products;
	}

    public function getActiveProductsCount(){
        $productModel=new \CB\Model\Product();
        $productModel->initQb();
        $productModel->qb->field('user')->equals(new \MongoDB\BSON\ObjectId($this->id));
        $productModel->qb->field('status')->in([1]);
        $productModel->qb->field('deleted')->notEqual(true);
        $productModel->qb->count();
        $count=$productModel->runQuery();
        return $count;
    }

	public function isValid(){
		return (//!empty($this->address['name']) &&
						//!empty($this->address['zip']) &&
						//!empty($this->address['city']) &&
						//!empty($this->address['street']) &&
						!empty($this->email) &&
						!empty($this->phone) &&
						!empty($this->username) &&
						!empty($this->active)
		);
	}

	public function isValidToBuy(){
		return (!empty($this->postaddress['name']) &&
						!empty($this->postaddress['zip']) &&
						!empty($this->postaddress['city']) &&
						!empty($this->postaddress['street']) &&
						!empty($this->email) &&
						!empty($this->phone) &&
						!empty($this->username) &&
						!empty($this->active)
		);
	}

	public function isValidToCharge(){
		return (
				((!empty($this->address['name']) &&
				!empty($this->address['zip']) &&
				!empty($this->address['city']) &&
				!empty($this->address['street']))
				
				||
				
				(!empty($this->postaddress['name']) &&
				!empty($this->postaddress['zip']) &&
				!empty($this->postaddress['city']) &&
				!empty($this->postaddress['street'])))
			
				&&
			
				!empty($this->email) &&
				!empty($this->phone) &&
				!empty($this->username) &&
				!empty($this->active)
		);
	}
	
	public function getInvoiceAddress(){
		if((!empty($this->address['name']) &&
			!empty($this->address['zip']) &&
			!empty($this->address['city']) &&
			!empty($this->address['street']))){
			return $this->address;
		} else {
			return $this->postaddress;
		}
	}


    public function modifyBalance($amount){
        $this->balance=intval($this->balance) + $amount;
        if($this->balance <= (2 * \Zend_Registry::get('uploadPrice'))){
            $emails = new \CB_Resource_Emails($this);
            $emails->balanceLow(array('user'=>$this));
        }
        return $this;
    }
    
    public function getToken(){
        return md5($this->id.$this->password);
    }


    static $usernameRegex = '/^[a-zA-Z0-9\.\-]{5,16}$/';


}
