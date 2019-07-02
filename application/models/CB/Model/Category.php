<?php
namespace CB\Model;

class Category extends \CB_Resource_Model{

	public function extBeforeSave($items, $params){
		if(isset($params['single'])){ $items=array($items); }

		$functions=new \CB_Resource_Functions();
		foreach($items as $key=>$item){
			foreach((is_array($item->options) ? $item->options : array()) as $optKey=>$option){
				if(empty($option['id'])) $items[$key]->options[$optKey]['id']=new \MongoDB\BSON\ObjectId();
				$items[$key]->options[$optKey]['slug']=$functions->slug($option['name']);

				if(is_array($option['children'])){
					foreach($option['children'] as $childKey=>$child){
						if(empty($child['id'])) $items[$key]->options[$optKey]['children'][$childKey]['id']=new \MongoDB\BSON\ObjectId();
						$items[$key]->options[$optKey]['children'][$childKey]['slug']=$functions->slug($child['name']);
					}

				}
			}

		}

		if(isset($params['single'])){ $items=$items[0]; }
		return parent::extBeforeSave($items, $params);
	}

	public function extAfterFind($items, $params=array()){
		if(isset($params['single'])){ $items=array($items); }

		if(isset($_GET['node'])){
			$items=array_filter($items, function($item){
				return $item->parent_id==$_GET['node'];
			});
		}
		$items=array_values($items);
		if(isset($params['single'])){ $items=$items[0]; }
		return parent::extAfterFind($items, $params);
	}

}
