<?php

class Frontend_Form_UserEdit extends CB_Form_Form {

	private $validators;

	public function init(){
		$this->initFields();
		$this->setAttrib('enctype', 'multipart/form-data');
	}

	public function initFields(){
		$emailCb=new Zend_Validate_Callback(array($this, 'uniqueEmail')); $emailCb->setMessage('Az e-mail cím már foglalt');
		$passwordCb=new Zend_Validate_Callback(array($this, 'passwordConfirm')); $passwordCb->setMessage('A két jelszó nem egyezik');

		$username=new Zend_Form_Element_Text('username');
		$username->setLabel('Felhasználónév')->setRequired(true)->addValidators(array(array('NotEmpty',true),array('Alnum',true),array('StringLength',true,'options'=>array('min'=>5,'max'=>16))))->setAttrib('autocomplete', 'off');
		$gender=new Zend_Form_Element_Radio('gender');
		$gender->setLabel('Nem')->setRequired(true)->setMultiOptions(array('male'=>'Férfi', 'female'=>'Nő'));
		$birth=new Zend_Form_Element_Date('birth_date');
		$birth->setLabel('Születési idő')->setAttrib('class', 'maskBirth');
		$email=new Zend_Form_Element_Email('email');
		$email->setLabel('E-mail cím')->setRequired(true)->addValidators(array(array('NotEmpty',true),array('EmailAddress',true), array($emailCb,true)))->setAttrib('autocomplete', 'off');
		$phone=new Zend_Form_Element_Text('phone');
		$phone->setLabel('Telefonszám')->setRequired(true)->addValidators(array(array('NotEmpty',true)))->setAttrib('autocomplete', 'off')->setAttrib('class', 'maskPhone');
		$hasNewPassword=new Zend_Form_Element_Checkbox('has_new_password');
		$hasNewPassword->setLabel('Jelszó megváltoztatása')->setAttrib('disabled', 'disabled')->setAttrib('class', 'changePswd');
		$password=new Zend_Form_Element_Password('new_password');
		$password->setLabel('Új jelszó')->addValidators(array(array('Alnum',true),array('StringLength',true,'options'=>array('min'=>5,'max'=>16)),array($passwordCb,true)))->setAttrib('autocomplete', 'off')->setAttrib('class', 'newPswd hidden');
		$passwordConfirm=new Zend_Form_Element_Password('passwordconfirm');
		$passwordConfirm->setLabel('Új jelszó megerősítése')->addValidators(array(array('Alnum',true),array($passwordCb,true)))->setAttrib('autocomplete', 'off')->setAttrib('class', 'newPswd hidden');

		$desc=new Zend_Form_Element_Textarea('desc');
		$desc->setLabel('Leírás')->setAttrib('class', 'ck');
		$avatar=new CB_Form_Element_Upload('avatar');
		$avatar->setLabel('Kép feltöltése')->setTargetDir('/upload/avatar');
		$avatar->buttonLabel='Kép feltöltése';

		$addressName=new Zend_Form_Element_Text('name');
		$addressName->setLabel('Név');
		$addressZip=new Zend_Form_Element_Text('zip');
		$addressZip->setLabel('Irányítószám')->setAllowEmpty(true)->addValidators(array(array('Digits',true),array('Between',true,'options'=>array('min'=>1000,'max'=>9999))));
		$addressCity=new Zend_Form_Element_Text('city');
		$addressCity->setLabel('Város');
		$addressAddress=new Zend_Form_Element_Text('street');
		$addressAddress->setLabel('Utca, házszám');
		$addressBankNumber=new Zend_Form_Element_Text('address_banknum');
		$addressBankNumber->setLabel('Számlaszám');

		$postaddressName=new Zend_Form_Element_Text('name');
		$postaddressName->setLabel('Név')->setRequired(true)->addValidators(array(array('NotEmpty',true)));
		$postaddressZip=new Zend_Form_Element_Text('zip');
		$postaddressZip->setLabel('Irányítószám')->setRequired(true)->addValidators(array(array('NotEmpty',true),array('Digits',true),array('Between',true,'options'=>array('min'=>1000,'max'=>9999))));
		$postaddressCity=new Zend_Form_Element_Text('city');
		$postaddressCity->setLabel('Város')->setRequired(true)->addValidators(array(array('NotEmpty',true)));
		$postaddressAddress=new Zend_Form_Element_Text('street');
		$postaddressAddress->setLabel('Utca, házszám')->setRequired(true)->addValidators(array(array('NotEmpty', true)));

		$addressSF=new CB_Form_SubForm(array('legend'=>'Számlázási cím'));
		$addressSF->addElements(array($addressName,$addressZip,$addressCity,$addressAddress,$addressBankNumber));


		$postaddressSF=new CB_Form_SubForm(array('legend'=>'Postázási cím'));
		$postaddressSF->addElements(array($postaddressName,$postaddressZip,$postaddressCity,$postaddressAddress));

		$oldEmail=new Zend_Form_Element_Hidden('old_email'); $oldEmail->removeDecorator('label');
		$submit=new Zend_Form_Element_Submit('Mentés');
		$submit->removeDecorator('label');

		$this->addElements(array($username,$gender,$birth,$email,$phone,$hasNewPassword,$password,$passwordConfirm,$avatar,$desc));

		$this->addDisplayGroup(array('avatar','desc'),'avatardesc',array('legend'=>'Általános tudnivalók'));
		$this->addDisplayGroup(array('username','email','gender','birth_date','phone','has_new_password','new_password','passwordconfirm'),'base',array('legend'=>'Alapadatok'));
		$this->addSubForm($addressSF, 'address', 2);
		$this->addSubForm($postaddressSF, 'postaddress', 3);

		//$this->addDisplayGroup(array('addressname','addresszip','addresscity','addressaddress'),'address',array('legend'=>'Cím'));
		$this->addElements(array($oldEmail,$submit)); //@TODO le kéne még biztosítani jelszóval az egészet
	}

	public function passwordConfirm($value, $values){
		if(empty($values['new_password'])) return true;
		return $values['new_password']==$values['passwordconfirm'];
	}

	public function uniqueEmail($value, $values){
		if($value==$values['old_email']) return true;
		$userModel=new \CB\Model\User();
		return !$userModel->findOneByEmail($value);
	}

	public function uniqueUsername($value, $values){
		$userModel=new \CB\Model\User();
		return !$userModel->findOneByUsername($value);
	}

	public function processData($values, $controller){
		$newPassword=$this->getElement('new_password')->getValue();
		if(!empty($newPassword)){
			$values['password']=md5($this->getElement('new_password')->getValue());
			$controller->m('A jelszót sikeresen megváltoztattad');
		}

		if($values['email']!=$values['old_email']){
			$values['activation_code']=md5($values['email'].time());
			$controller->emails->reactivation(array('user'=>$values), $controller->url('aktivacio'));
			$mc=new CB_Resource_Mailchimp();
			$mc->modifyEmail($values['old_email'], $values['email'], array('NAME'=>$values['username']));
			$values['email']=$values['old_email'];
			$controller->m('E-mail címed megváltoztatásához kattints az aktivációs linkre az e-mailben, amit az új címedre küldtünk');
		}

		return $values;
	}

}