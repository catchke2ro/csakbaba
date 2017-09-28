<?php
namespace CB\Model;

class Product extends \CB_Resource_Model{


	use ProductServiceCommon;
	
	function findByCategory($category, $tree){
		//if(!$category) return array();
		$this->initQb();
		if(!$category){
		
		} else if(!empty($category->children)){
			$this->qb->field('category')->equals(new \MongoRegex('/^'.$category->id.'\-.*/i'));
		} else {
			$this->qb->field('category')->equals($category->id);
		}
		$this->qb->field('status')->equals(1);
		
		$sort=!empty($_POST['sort']) ? explode('-', $_POST['sort']) : array('date_added', 'desc');
		$this->qb->sort($sort[0], $sort[1]);
		
		if($tree && !empty($_POST)){
			$options=$category->props;
			$props=\Zend_Registry::get('categories')->_props;
			foreach($options as $id){
				$o=$props[$id];
				if(array_key_exists($o['slug'], $_POST)){
					switch($o['type']){
						case 'select':
							$this->qb->field('options.'.$id)->in($_POST[$o['slug']]);
							break;
						case 'number':
							$range=range(reset(explode('-', $_POST[$o['slug']])), end(explode('-', $_POST[$o['slug']])));
							$range=array_map(function($a){ return strval($a);	}, $range);
							$this->qb->field('options.'.$id)->in($range);
							//$this->qb->field('options.'.$id)->equals(new \MongoRegex('/['.implode(',',$range).']{1}/i'));
							break;
						default: break;
					}
				}
			}
			
			$genreArray=array();
			foreach(\Zend_Registry::get('genreTypes') as $slug=>$type){
				if(array_key_exists($slug, $_POST) && $_POST[$slug]==1) $genreArray[]=$slug;
			}
			if(!empty($genreArray)) $this->qb->field('type')->in($genreArray);
			
		}
		$results=$this->runQuery();
		if(empty($_POST['sort'])){
			$promoted=$this->getPromoted('list');
			foreach($promoted as $p){
				if(array_key_exists($p->id, $results)) $results=array($p->id => $results[$p->id]) + $results;
			}
		}
		
		$page=!empty($_GET['page']) ? $_GET['page'] : 1;
		$pageSize=15;
		if(!empty($_COOKIE['resolution'])){
			$res=$_COOKIE['resolution'];
			foreach(self::$rowSizes as $limit=>$rs){
				if($res >= $limit) $pageSize=3*$rs;
			}
		}
		header('X-CSB-PRC: '.count($results));
		\Zend_Registry::set('productsCount', count($results));
		$results=array_slice($results, ($page-1)*$pageSize, $pageSize);
		return $results;
	}

}
