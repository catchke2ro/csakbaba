<?php

class CB_Array_OldCategories extends ArrayIterator {

	public $_multiArray;
	public $_comboList;
	public $_urlParams;


	public function __construct($array){
		parent::__construct($array);
		$this->_multiArray=$this->getMultiArray();
	}

	public function getKeyPosition($key){
		$keys=array_keys($this->getArrayCopy());
		return array_search($key, $keys);
	}

	public function filter($property, $value){
		$copy=$this->getArrayCopy();
		$filtered=array_filter($copy, function($item) use ($property, $value){
			return $item->$property==$value;
		});
		return $filtered;
	}

	public function fetchCategory($params){
		if(!is_object($params)){
			$this->_urlParams=$params;
			$category=$this->_searchCategory();
		} else {
			$category=$params;
		}
		if($category===false) return false;
		$children=$category->children;
		$category=$this->offsetGet($category->id);
		$path=$this->getPath($category->id);
		$options=$this->getOptions($path);
		return array($category, $path, $options, $children, $this->_urlParams);
	}

	public function getPath($id){
		$path=array();
		$this->_walkRecursiveUp($this->offsetGet($id), function($item, $array) use (&$path){
			$path[]=$item;
		});
		return $path;
	}

	public function getUri($id){
		$path=array_reverse($this->getPath($id));
		$uri='/';
		foreach($path as $p){
			$uri.=$p->slug.'/';
		}
		return $uri;
	}

	public function getOptions($path){
		if(is_string($path)) $path=$this->getPath($path);
		$options=array();
		foreach(array_reverse($path) as $item){
			$options=array_merge($item->options, $options);
		}
		usort($options, function($a, $b){	return $a['o']<$b['o'] ? -1 : 1; });
		foreach($options as $key=>$option){
			if(!empty($option['children'])){
				foreach($option['children'] as $childKey=>$child){
					$option['children'][$child['id']->__toString()]=$child;
					unset($option['children'][$childKey]);
				}
			}
			$options[$option['id']->__toString()]=$option;
			unset($options[$key]);
		}
		return $options;
	}

	public function getMultiArray($parent_id=0){
		if(!empty($this->_multiArray)) return $this->_multiArray;
		$items=array_values($this->filter('parent_id', $parent_id));
		foreach($items as $key=>$item){
			$items[$key]->children=$this->getMultiArray($item->id);
		}
		return $items;
	}

	public function getComboList($onlySelectable=false){
		$this->_walkRecursive(function($item, $array, $level){
			$array->_comboList[strval(($item->children?'x':'').$item->id)]=($level?' |':'').str_repeat('_', $level).$item->name;
		});
		return $this->_comboList;
	}

	private function _walkRecursive(Closure $function=null, $parent_id=0, $level=0, $children=null){
		$walkArray=(is_array($children)) ? $children : $this->_multiArray;
		foreach($walkArray as $item){
			if($function) $function($item, $this, $level);
			if($item->children) $this->_walkRecursive($function, $item->id, $level+1, $item->children);
		}
	}

	private function _walkRecursiveUp($item, Closure $function=null){
		if($function) $function($item, $this);
		if($item->parent_id!='0') $this->_walkRecursiveUp($this->offsetGet($item->parent_id), $function);
	}

	private function _searchCategory(){
		$category='0';
		$this->_walkRecursive(function($item, $array, $level) use (&$category){
			if($item->slug==reset($array->_urlParams) && $category==$item->parent_id){
				$category=$item->id;
				$array->_urlParams=array_slice($array->_urlParams, 1);
			}
		});
		return ($category!='0') ? $this->offsetGet($category) : false;
	}
}