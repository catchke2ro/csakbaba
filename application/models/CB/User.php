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
	 * @ODM\String
	 */
	public $username;

	/**
	 * @ODM\String
	 */
	public $password;

	/**
	 * @ODM\String
	 */
	public $email;

	/**
	 * @ODM\String
	 */
	public $desc;

	/**
	 * @ODM\String
	 */
	public $gender;

	/**
	 * @ODM\Date
	 */
	public $birth_date;

	/**
	 * @ODM\String
	 */
	public $phone;

	/**
	 * @ODM\Date
	 */
	public $date_reg;

	/**
	 * @ODM\Date
	 */
	public $date_last_login;

	/**
	 * @ODM\Boolean
	 */
	public $active;

	/**
	 * @ODM\String
	 */
	public $activation_code;

	/**
	 * @ODM\Hash
	 */
	public $address;

	/**
	 * @ODM\Hash
	 */
	public $postaddress;

	/**
	 * @ODM\Hash
	 */
	public $avatar;

	/**
	 * @ODM\String
	 */
	public $role;

	/**
	 * @ODM\Int
	 */
	public $balance;

	/**
	 * @ODM\ReferenceMany(targetDocument="Product")
	 */
	public $favourites;


	/**
	 * @ODM\Hash
	 */
	public $promotes;


	/**
	 * @ODM\String
	 */
	public $fbid;


	/**
	 * @ODM\String
	 */
	public $gpid;


	/**
	 * @ODM\Hash
	 */
	public $subscribed;


	public function getRating($type='avg'){
		$orderModel=new \CB\Model\Order();
		$all=$positive=0;
		if($type=='avg' || $type=='buy'){
			$userOrdersBuy=$orderModel->find(array('conditions'=>array('user._id'=>new \MongoId($this->id))));
			foreach($userOrdersBuy as $uob){
				if($uob->shop_user_rating){
					$all++;
					if($uob->shop_user_rating->get()->positive==true) $positive++;
				}
			}
		}
		if($type=='avg' || $type=='sell'){
			$userOrdersSell=$orderModel->find(array('conditions'=>array('shop_user._id'=>new \MongoId($this->id))));
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
		$productModel->qb->field('user')->equals(new \MongoId($this->id));
		$productModel->qb->field('status')->in($status);
		$productModel->qb->sort('date_added', 'DESC');
		$products=$productModel->runQuery();
		if($random){
			shuffle($products);
			$products=array_slice($products, 0, $count);
		}
		return $products;
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


}
