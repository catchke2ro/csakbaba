<?php

class Frontend_Form_ProductEdit extends CB_Form_Form {

	public $user;
	public $category;
	public $options;
	public $deliveryOptions;

	public function init(){
		$this->setAttrib('enctype', 'multipart/form-data');
	}

	public function initFields(){

		$this->setAttrib('class', reset(explode('-', $this->category->id)));

		$id=new Zend_Form_Element_Hidden('id');
		$id->removeDecorator('label');
		$userId=new Zend_Form_Element_Hidden('user_id');
		$userId->removeDecorator('label');
		$categoryId=new Zend_Form_Element_Hidden('category_id');
		$categoryId->removeDecorator('label');
		$name=new Zend_Form_Element_Text('name');
		$name->setLabel('Áru neve')->setRequired(true)->addValidators(array(array('NotEmpty',true)));
		$desc=new Zend_Form_Element_Textarea('desc');
		$desc->setLabel('Leírás')->setRequired(true)->addValidators(array(array('NotEmpty',true)));
		$price=new Zend_Form_Element_Text('price');
		$price->setLabel('Ár')->setRequired(true)->addValidators(array(array('NotEmpty',true),array('Digits',true)))->setAttrib('class', 'priceInput');
		$image=new CB_Form_Element_Upload('images');
		$image->setLabel('Kép feltöltése')->setTargetDir('/upload/product');
		$type=new Zend_Form_Element_Radio('type');
		$types=Zend_Registry::get('genreTypes');
		if($this->category->sex==false){
			$types=array_slice($types, 2, 1);
			$type->setValue(key($types));
		}
		$type->setLabel('Típus')->setRequired(true)->setMultiOptions($types)->setAttrib('class', 'genre');
		$new=new Zend_Form_Element_Radio('new');
		$new->setLabel('Állapot')->setRequired(true)->setMultiOptions(array('0'=>'Használt', '1'=>'Új'));
		$deliveries=new Zend_Form_Element_MultiCheckbox('deliveries');
		$deliveries->setLabel('Szállítási módok')->setRequired(true)->setMultiOptions($this->deliveryOptions);
		$autorenew=new Zend_Form_Element_Radio('autorenew');
		$autorenew->setLabel('Automatikus megújítás')->setMultiOptions(Zend_Registry::get('autoRenewOptions'))->setValue('never');

		$this->addElements(array($id,$name,$desc,$price,$image,$type,$new,$deliveries,$autorenew,$id,$categoryId,$userId));
		$ids=$this->_categoryFields();

		$this->addDisplayGroup(array('name','price','desc','type','new'),'general');
		$this->addDisplayGroup(array_merge($ids,array('deliveries','autorenew','images')),'others');

		$submit=new Zend_Form_Element_Submit('Mentés');
		$cancel=new Zend_Form_Element_Button('Mégse');
		$cancel->setAttrib('class', 'cancelButton');
		$preview=new Zend_Form_Element_Button('Előnézet');
		$preview->setAttrib('class', 'previewButton');
		$this->addElement($submit);
		$this->addElement($preview);
		$this->addElement($cancel);

	}

	private function _categoryFields(){
		$ids=array();
		$props=Zend_Registry::get('categories')->_props;
		foreach($this->options as $option){
			$id=$option; $option=$props[$option];
			switch($option['type']){
				case 'select':
					$element=new Zend_Form_Element_Select('options_'.$id);
					$element->setMultiOptions($this->_fetchSelectOptions($option['options']));
					break;
				case 'number':
					$element=new Zend_Form_Element_Text('options_'.$id);
					$this->_fetchRangeOptions($option, $element);
					break;
				default: break;
			}
			$ids[]='options_'.$id;
			$element->setLabel($option['name']);
			$this->addElement($element);
		}
		return $ids;
	}

	private function _fetchRangeOptions($option, $element){
		$element->setAttrib('data-min', (!empty($option['min']) ? $option['min'] : 0));
		$element->setAttrib('data-max', (!empty($option['max']) ? $option['max'] : 1000));
		$element->setAttrib('data-step', (!empty($option['step']) ? $option['step'] : 1));
		$element->setAttrib('class', $element->getAttrib('class').' range');
	}

	private function _fetchSelectOptions($children=array()){
		$return=array();
		foreach($children as $child){
			$return[$child['value']]=$child['name'];
		}
		return $return;
	}

	public function processData($values, $controller){
		foreach($values as $fieldId=>$value){
			if(strpos($fieldId, 'options_')!==false){
				if(empty($values['options'])) $values['options']=array();
				$values['options'][str_replace('options_', '', $fieldId)]=$value;
				unset($values[$fieldId]);
			}
		}
		$values['images']=is_array($values['images']) ? array_filter($values['images']) : array();
		$values['user']=$controller->userModel->findOneById($values['user_id']);
		$values['category']=$values['category_id'];
		$values['search_name']=strtolower($values['name']);
		if(empty($values['id'])){
			$values['date_added']=new DateTime(date('Y-m-d H:i:s'));
			$values['date_period']=new DateTime(date('Y-m-d H:i:s'));
			$values['status']=1;
		}
		return $values;
	}

	public function populate($values=array()){
		if(!empty($values['options']) && is_array($values['options'])){
			foreach($values['options'] as $id=>$opt){
				if(!isset($values['options_'.$id])) $values['options_'.$id]=$opt;
			}
		}
		return parent::populate($values);
	}

}