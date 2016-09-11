<?php

class CB_Array_Categories {

	public $_multiArray;
	public $_singleArray;
	public $_urlMap;
	public $_reverseUrlMap;
	public $_props;
	public $_comboList=array();

    private $_listOptions = [
        'rootChar'=>' |',
        'levelChar'=>'_'
    ];

	public function __construct(){
		$categoriesPHP=require(APPLICATION_PATH.'/configs/categories.php');
		$this->_props=$categoriesPHP['PROP'];
		$this->_toSingleArray($categoriesPHP['CAT']);
		$this->_reverseUrlMap=array_flip($this->_urlMap);
	}

	public function getUri($id){
		return !empty($this->_urlMap[$id]) ? $this->_urlMap[$id] : '';
	}

	public function getOptions($id){
		return !empty($this->_singleArray[$id]) ? $this->_singleArray[$id]->props : '';
	}

    public function getById($id){
        return !empty($this->_singleArray[$id]) ? $this->_singleArray[$id] : false;
    }

	public function getComboList($listOptions, $children = null){

        $this->_listOptions = array_merge($this->_listOptions, $listOptions);
        $this->_comboList = [];
		$this->_walkRecursive(function($item, $array, $level){
			$array->_comboList[strval((!empty($item->children)?'x':'').$item->id)]=($level? $this->_listOptions['rootChar'] : '').str_repeat($this->_listOptions['levelChar'], $level).$item->name;
		}, (!empty($listOptions['parent_id']) ? $listOptions['parent_id'] : null), 0, $children);
		return $this->_comboList;
	}

    public function getCombo($listOptions = [], $name = 'category_id', $emptyText = 'Válassz kategóriát!'){
        $children = null;
        if(!empty($listOptions['parent_id'])){
            $children = $this->_multiArray[$listOptions['parent_id']]->children;
        }
        $categoryOptions=$this->getComboList($listOptions, $children);
        $disabledOptions=array();
        foreach($categoryOptions as $id=>$option){ if(strpos($id, 'x')!==false) $disabledOptions[]=$id; }
        $categorySelect=new Zend_Form_Element_Select($name);
        $categorySelect->setMultioptions(array(''=>$emptyText)+$categoryOptions)->setAttrib('disable', $disabledOptions);
        return $categorySelect;
    }

	public function fetchCategory($params){
		$extraParams=array();
		if(!is_object($params)){
			$category=false;
			while(!empty($params)){
				if(empty($this->_reverseUrlMap['/'.implode('/', $params)])) $extraParams[]=array_pop($params);
				else {
					$category=$this->_singleArray[$this->_reverseUrlMap['/'.implode('/', $params)]];
					$params=array();
				}
			}
		} else {
			$category=$params;
		}
		if($category===false) return false;
		$path=$this->getPath($category->id);
		return array($category, $path, $category->props, $category->children, $extraParams);
	}

	public function getPath($id){
		$path=array();
		while(!empty($id)){
			$path[]=$this->_singleArray[$id];
			$id=$this->_singleArray[$id]->parent_id;
		}
		return $path;
	}

	private function _walkRecursive(Closure $function=null, $parent_id=null, $level=0, $children=null){
		$walkArray=(is_array($children)) ? $children : $this->_multiArray;
		foreach($walkArray as $item){
			if($function) $function($item, $this, $level, $this->_listOptions);
			if($item->children) $this->_walkRecursive($function, $item->id, $level+1, $item->children);
		}
	}

	private function _toSingleArray($array, $idPrefix='', $urlPrefix='', $props=array(), $sex=true, $parent_id=''){
		$classes=array();
		foreach($array as $id=>$item){
			$this->_urlMap[$idPrefix.$id]=$urlPrefix.'/'.$item['slug'];
			$thisProps=!empty($item['prop']) ? array_merge($props, $item['prop']) : $props;
			$thisSex=isset($item['sex']) ? $item['sex'] : $sex;
			$class=new CB_Array_Category($item, $idPrefix.$id, $this->_urlMap[$idPrefix.$id], $thisProps, $thisSex, $parent_id);
			$children=array();
			if(!empty($item['children'])){
				$children=$this->_toSingleArray($item['children'], $idPrefix.$id.'-', $urlPrefix.'/'.$item['slug'], $thisProps, $thisSex, $class->id);
			}
			if(!empty($children)) $class->children=$children;
			$classes[$idPrefix.$id]=$class;
			if(empty($idPrefix)){
				$this->_multiArray[$idPrefix.$id]=$class;
			}
			$this->_singleArray[$idPrefix.$id]=$class;
		}
		return $classes;
	}


}