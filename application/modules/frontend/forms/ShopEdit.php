<?php

class Frontend_Form_ShopEdit extends CB_Form_Form {

	public $user;

	public function init(){
		$this->initFields();
		$this->setAttrib('enctype', 'multipart/form-data');
	}

	public function initFields(){
		$nameCb=new Zend_Validate_Callback(array($this, 'uniqueName')); $nameCb->setMessage('A név már foglalt!');

		$id=new Zend_Form_Element_Hidden('id');
		$name=new Zend_Form_Element_Text('name');
		$name->setLabel('Bolt neve')->setRequired(true)->addValidators(array(array('NotEmpty',true)),$nameCb);
		$desc=new Zend_Form_Element_Textarea('desc');
		$desc->setLabel('Leírás')->setAttrib('class', 'ck');
		$image=new CB_Form_Element_Upload('image');
		$image->setLabel('Kép feltöltése')->setTargetDir('/upload/shop');

		$addressName=new Zend_Form_Element_Text('address_name');
		$addressName->setLabel('Számlázási név')->setRequired(true)->addValidators(array(array('NotEmpty',true)));
		$addressZip=new Zend_Form_Element_Text('address_zip');
		$addressZip->setLabel('Irányítószám')->setRequired(true)->addValidators(array(array('NotEmpty',true),array('Digits',true),array('Between',true,'options'=>array('min'=>1000,'max'=>9999))));
		$addressCity=new Zend_Form_Element_Text('address_city');
		$addressCity->setLabel('Város')->setRequired(true)->addValidators(array(array('NotEmpty',true)));
		$addressAddress=new Zend_Form_Element_Text('address_address');
		$addressAddress->setLabel('Utca, házszám')->setRequired(true)->addValidators(array(array('NotEmpty', true)));
		$addressBankNumber=new Zend_Form_Element_Text('address_banknum');
		$addressBankNumber->setLabel('Számlaszám')->setRequired(true)->addValidators(array(array('NotEmpty', true)));

		$addressSF=new CB_Form_SubForm(array('legend'=>'Számlázási adatok'));
		$addressSF->addElements(array($addressName,$addressZip,$addressCity,$addressAddress,$addressBankNumber));

		$submit=new Zend_Form_Element_Submit('Küldés');

		$this->addElements(array($id,$desc,$image));
		$this->addSubForm($addressSF, 'address', 1);
		$this->addDisplayGroup(array('id','desc','image'),'general',array('legend'=>'Általános'));
		$this->addElements(array($submit));

	}

	public function passwordConfirm($value, $values){
		if(empty($values['new_password'])) return true;
		return $values['new_password']==$values['passwordconfirm'];
	}

	public function uniqueName($value, $values){
		$shopModel=new \CB\Model\Shop();
		$shop=$shopDb->findOneBy('name', $value);
		if(!$shop) return false;
		if($shop->user_id!=$this->user->id) return false;
		return true;
	}

}