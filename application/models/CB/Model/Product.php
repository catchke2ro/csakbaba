<?php
namespace CB\Model;

class Product extends \CB_Resource_Model{


	protected function _buildOptions($options){
		parent::_buildOptions($options);

		$this->qb->addOr($this->qb->expr()->field('deleted')->exists(false));
		$this->qb->addOr($this->qb->expr()->field('deleted')->equals(false));
	}

	public function afterFind($items){
		foreach((is_array($items) ? $items : array()) as $key=>$item){
			if($item->deleted){
				unset($items[$key]);
			}
		}
		return $items;
	}

	public function extAfterFind($items, $params=array()){
		if(isset($params['single'])){ $items=array($items); }
		foreach($items as $key=>$item){
			$items[$key]->user->get();
		}
		$items=array_values($items);
		if(isset($params['single'])){ $items=$items[0]; }
		return parent::extAfterFind($items, $params);
	}

	public function extBeforeSave($items, $params){
		if(isset($params['single'])){ $items=array($items); }
		foreach($items as $key=>$item){
			if(is_array($items[$key]->user) && array_key_exists('__isInitialized__', $items[$key]->user)){
				$userModel=new \CB\Model\User();
				$items[$key]->user=$userModel->findOneById($items[$key]->user['id']);
			}
		}
		if(isset($params['single'])){ $items=$items[0]; }
		return parent::extBeforeSave($items, $params);
	}

	function findByCategory($category, $tree){
		if(!$category) return array();
		$this->initQb();
		$this->qb->field('category')->equals($category->id);
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
		return $results;
	}

	private function _getIds($value, $option){
		switch($option['type']){
			case 'select':
				foreach($option['children'] as $child){
					if(($key=array_search($child['slug'], $value))!==false) $value[$key]=$child['id']->__toString();
				}
				break;
			default: break;
		}
		return $value;
	}

	public function getMostVisited(){
		if(!($visited=$this->cache->load('mainVisited'))){
			$visited=$this->find(array('conditions'=>array('status'=>1), 'order'=>'visitors desc', 'limit'=>12));
			$this->cache->save($visited, 'mainVisited', array(), 120);
		}
		return $visited;
	}

	public function getFresh(){
		if(!($fresh=$this->cache->load('mainFresh'))){
			$fresh=$this->find(array('conditions'=>array('status'=>1), 'order'=>'date_added desc', 'limit'=>12));
			$this->cache->save($fresh, 'mainFresh', array(), 120);
		}
		return $fresh;
	}

	public function getFavouriteLists(){
		$userModel=new \CB\Model\User();
		$userModel->initQb();
		$userModel->qb->field('favourites')->exists(true);
		$userModel->qb->where('this.favourites.length > 0');
		//$userModel->qb->field('favourites')->not($userModel->qb->expr()->size(0));
		//$userModel->qb->field('favourties')->equals('function() { this.length > 0; }');
		$users=$userModel->runQuery();
		shuffle($users);
		$user=reset($users);
		return $user ? $user->favourites : array();
	}

	public function getPromoted($type, $randomize=true, $key=''){
		if(!($products=$this->cache->load('promoted_'.$type.'_'.($randomize?1:0).'_'.$key))){
			$this->initQb();
			$this->qb->field('promotes.'.$type)->gte(time());
			$this->qb->field('status')->equals(1);
			if(!empty($key)) $this->qb->field('category')->equals(new \MongoRegex('/^'.$key.'-.*/iu'));
			//$this->qb->field('promotes.'.$type)->lte(strtotime('-1 weeks'));
			$products=$this->runQuery();
			shuffle($products);
			$products=$products ? $products : array();

			$this->cache->save($products,'promoted_'.$type.'_'.($randomize?1:0).'_'.$key, array(), 120);
		}
		return $products;
	}

}
