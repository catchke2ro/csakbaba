<?php

class Frontend_Form_ProductEdit extends CB_Form_Form {

	public $user;
	public $category;
	public $options;
	public $deliveryOptions;
    public $product;

	public function init(){
		$this->setAttrib('enctype', 'multipart/form-data');
	}

	public function initFields($category = null, $deliveryOptions = null){
        $this->category = $category;
        $this->options = $category->props;
        $this->deliveryOptions = $deliveryOptions;
        $this->setAttrib('data-amount', 0);

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
		$desc->setLabel('Leírás')->addValidators(array(array('NotEmpty',true)));
		$price=new Zend_Form_Element_Text('price');
		$price->setLabel('Ár')->setRequired(true)->addValidators(array(array('NotEmpty',true),array('Digits',true)))->setAttrib('class', 'priceInput')->addFilters(array(array('StringTrim')));
		$image=new CB_Form_Element_Upload('images');
		$image->setLabel('Kép feltöltése')->setTargetDir('/upload/product');

        $moreOpened = new Zend_Form_Element_Hidden('moreopened');
        $moreOpened->setValue('0');

		$type=new Zend_Form_Element_Radio('type');
		$types=Zend_Registry::get('genreTypes');
		if($this->category->sex==false){
			$types=array_slice($types, 2, 1);
			$type->setValue(key($types));
		}
		$type->setLabel('Típus')->setMultiOptions($types)->setAttrib('class', 'genre');
		$new=new Zend_Form_Element_Radio('new');
		$new->setLabel('Állapot')->setMultiOptions(array('0'=>'Használt', '1'=>'Új'));
		$deliveries=new Zend_Form_Element_MultiCheckbox('deliveries');
		$deliveries->setLabel('Szállítási módok')->setMultiOptions($this->deliveryOptions);
		$autorenew=new Zend_Form_Element_Radio('autorenew');
		$autorenew->setLabel('Automatikus megújítás')->setMultiOptions(Zend_Registry::get('autoRenewOptions'))->setValue('never');


		$this->addElements(array($id,$name,$desc,$price,$image,$moreOpened,$type,$new,$deliveries,$autorenew,$id,$categoryId,$userId));
		$ids=$this->_categoryFields();



        $moreButton=new Zend_Form_Element_Button('moreButton');
        $moreButton->setLabel('További adatok');
        $moreButtonInfo = (new Zend_Form_Element_Note('moreButtonInfo'));
        $moreButtonInfo->setValue('<p class="infoText">További részletek megadásával könnyebben megtalálható lesz a terméked, és az eladás menetét is gyorsítja</p>');
        $this->addElements([$moreButton, $moreButtonInfo]);



        //Promote
        $prices=Zend_Registry::get('promoteOptionPrices');
        $promoteOptions=Zend_Registry::get('promoteOptions');
        $opts=array();
        foreach($promoteOptions as $type=>$text){
            $opts[$type]=$text.' ('.$prices[$type].' HUF)';
        }

        $promoteButton=new Zend_Form_Element_Button('promoteButton');
        $promoteButton->setLabel('Termék kiemelése');
        $promoteButtonInfo = (new Zend_Form_Element_Note('promoteButtonInfo'));
        $promoteButtonInfo->setValue('<p class="infoText">Kiemeléssel a vásárlók könnyebben megtalálhatják és megásárolhatják a termékedet</p>');
        $promoteButtonInfo = (new Zend_Form_Element_Note('promoteButtonInfo'));
        $promoteButtonInfo->setValue('<p class="infoText">Kiemeléssel a vásárlók könnyebben megtalálhatják és megásárolhatják a termékedet</p>');
        $this->addElements([$promoteButton, $promoteButtonInfo]);

        $cb=new Zend_Form_Element_MultiCheckbox('promote_types');
        $cb->setLabel('Kiemelés típusa')->setMultiOptions($opts);
        $hint=new Zend_Form_Element_Note('promote_hint');
        $hint->setValue('<p class="infoText error">Nincs elég pénz az egyenlegeden minden kiemelés beállításához.</p>');

        $this->addElements(array($cb, $hint));
        $this->setAttrib('data-promoteprices', json_encode($prices));




		$this->addDisplayGroup(array('name','price','images'),'generalleft');
		$this->addDisplayGroup(array('desc'),'generalright');
		$this->addDisplayGroup(['moreButton','moreButtonInfo'],'moreButtonFieldset', null, 'collapseOpenButtonFieldset');
		$this->addDisplayGroup(array_merge(['moreopened'], $ids, ['type']),'more1', null, 'hidden more collapseOpenFieldset');
		$this->addDisplayGroup(array('new','deliveries','autorenew'),'more2', null, 'hidden more collapseOpenFieldset');
        $this->addDisplayGroup(['promoteButton','promoteButtonInfo'],'promoteButtonFieldset', null, 'collapseOpenButtonFieldset');
        $this->addDisplayGroup(array('promote_types', 'promote_hint'),'hidden promote collapseOpenFieldset');



        $submit=new Zend_Form_Element_Submit('Mentés');
		$cancel=new Zend_Form_Element_Button('Mégse');
		$cancel->setAttrib('class', 'cancelButton');
        $this->addElement($cancel);
        $this->addElement($submit);

        $this->addDisplayGroup(array('Mégse', 'Mentés'),'buttons');


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

	public function populate($values=array(), $copy = false){
		if(!empty($values['options']) && is_array($values['options'])){
			foreach($values['options'] as $id=>$opt){
				if(!isset($values['options_'.$id])) $values['options_'.$id]=$opt;
			}
		}
        if(!empty($values['promotes']) && !$copy){
            foreach($values['promotes'] as $type=>$date){
            	if($date < time()) continue;
                $this->getElement('promote_types')->removeMultiOption($type);
            }
        }
		return parent::populate($values);
	}

    public function setProduct($product, $copy = false){
        if(!$product) return $this;
        $this->product = $product;
        $this->populate(get_object_vars($product), $copy);
        if($copy){
            $this->getElement('id')->setValue('');
            $this->getElement('images')->setValue('');
        }
        return $this;
    }


    public function processDescription($user){
        $userActiveProductsCount=$user->getActiveProductsCount();
    
        if(Zend_Registry::get('uploadPrice') == 0){
            $this->setAttrib('data-amount', 0);
            $this->setDescription('A termék feltöltése most ingyenes!');
        } else if($userActiveProductsCount < Zend_Registry::get('freeUploadLimit')){
            $this->setAttrib('data-amount', 0);
            $this->setDescription(''.Zend_Registry::get('freeUploadLimit').' aktív termékig a feltöltés ingyenes!');
        } else if($user->balance < Zend_Registry::get('uploadPrice')){
            $this->getView()->assign(array(
                'noBalanceError'=>true
            ));
        } else {
            $this->setAttrib('data-amount', Zend_Registry::get('uploadPrice'));
            $this->setDescription('A termék feltöltésének díja '.Zend_Registry::get('uploadPrice').' Ft, amit az egyenlegedből vonunk le.');
        }
    }
}