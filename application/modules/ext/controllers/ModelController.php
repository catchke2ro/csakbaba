<?php
/**
 * Class Ext_ModelController
 * @author CB Group
 * Query functions for ExtJS administration area's synchronisation functions
 */
class Ext_ModelController extends CB_Controller_Ext_Action {

	/**
	 * @var string Name of Model
	 */
	public $modelName;

	/**
	 * @var string Name of Model item
	 */
	public $modelItemName;

	/**
	 * @var string Slug of model in request and JSON response
	 */
	public $modelKey;

	/**
	 * Init query. Define Model name, Model item name and Model key
	 */
	public function init(){
		$params=$this->getRequest()->getUserParams();
		$this->modelName='\\CB\Model\\'.ucfirst($params['model']);
		$this->modelItemName='\\CB\\'.ucfirst($params['model']);
		$this->modelKey=$params['model'];
	}

	/**
	 * READ Model
	 */
	public function readAction(){
		$Model=new $this->modelName();
		$Model->ext=true;
		$items=$Model->find(array('withDeleted'=>true));
		$items=array_values($items);
		$items=$this->_callback('extAfterFind', $Model, $items); //Run callback
		$this->returnArray[$this->modelKey]=$items; //Return items
	}

	/**
	 * CREATE Model item
	 */
	public function createAction(){
		if($this->_request->isPost()){
			$post=$this->extPost; //Fetched POST
			$Model=new $this->modelName();
			$Model->ext=true;

			/**
			 * Handle multiple creations
			 */
			$keys=array_keys($post);
			if(!is_numeric($keys[0])){$posts=array($post);} else {$posts=$post;}

			/**
			 * Run creation process for all items in POST
			 */
			$items=array();
			foreach($posts as $post){
				$item=new $this->modelItemName(); //Create Model item
				$items[]=$item;
				$item->saveAll($post); //Apply data for Model item
				$item=$this->_callback('extBeforeSave', $Model, $item, array('single'=>true)); //Run callback
				$Model->save($item); //Save Model item
				$this->_callback('extAfterSave', $Model, $item, array('single'=>true)); //Run callback
			}
			$this->returnArray['success']=true;

			/**
			 * Run afterFind callback for the stored Model items
			 */
			foreach($items as $item){
				$item=$this->_callback('extAfterFind', $Model, $item, array('single'=>true));
				$this->returnArray[$this->modelKey][]=$item;
			}
		}
	}

	/**
	 * UPDATE Model item
	 */
	public function updateAction(){
		if($this->_request->isPost()){
			$post=$this->extPost; //Fetched POST
			$Model=new $this->modelName();
			$Model->ext=true;

			/**
			 * Handle multiple updates
			 */
			$keys=array_keys($post);
			if(!is_numeric($keys[0])){$posts=array($post);} else {$posts=$post;}

			/**
			 * Run update process for all items in POST
			 */
			$items=array();
			foreach($posts as $post){
				$item=$Model->findOneById($post['id']); //Find Model item
				if(!empty($item)){
					$items[]=$item;
					$item->saveAll($post); //Apply data for Model item
					$item=$this->_callback('extBeforeSave', $Model, $item, array('single'=>true)); //Run callback
					$Model->save($item); //Save Model item
					$this->_callback('extAfterSave', $Model, $item, array('single'=>true)); //Run callback
				}
			}
			$this->returnArray['success']=true;

			/**
			 * Run afterFind callback for the stored Model items
			 */
			foreach($items as $item){
				$item=$this->_callback('extAfterFind', $Model, $item, array('single'=>true));
				$this->returnArray[$this->modelKey][]=$item;
			}
		}
	}

	/**
	 * DESTROY Model item
	 */
	public function destroyAction(){
		if($this->_request->isPost()){
			$post=$this->extPost; //Fetched POST
			$Model=new $this->modelName();
			$Model->ext=true;
			$item=$Model->findOneById($post['id']); //Find Model item
			if(!empty($item)){
				$continue=$this->_callback('extBeforeDelete', $Model, $item, array('single'=>true)); //Run callback
				if($continue) {
					$Model->delete($item);
					$this->returnArray['success']=true;
				} else {
					$this->returnArray['success']=false; //@TODO message EXT-re
				}
			}
		}
	}

	/**
	 * GET Translate fields array
	 */
	public function translateFieldsAction(){
		$Model=new $this->modelName();
		$Model->ext=true;

		if(property_exists($Model, 'translateFields')){
			$translateFields=$Model->translateFields;
			$this->returnArray['translateFields']=$translateFields;
		} else {
			$this->returnArray['translateFields']=array();
		}
		$this->returnArray['success']=true;
	}

	/**
	 * @param string $callbackname Name of callback function
	 * @param CB_Resource_Model() $Model Model
	 * @param CB_Resource_ModelItem() $data ModelItem data
	 * @param array $params Special params for callback
	 * @return mixed
	 */
	function _callback($callbackname, &$Model, $data, $params=array()){
		if(method_exists($Model, $callbackname)){
			$returnData=$Model->$callbackname($data, $params); //Run callback, if exists
			return $returnData;
		}
		return $data; //If method not exists, return the raw data
	}

}