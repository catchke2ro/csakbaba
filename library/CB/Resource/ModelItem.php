<?php
class CB_Resource_ModelItem {

	public function get(){
		return $this;
	}

	public function saveAll($data){
		foreach($data as $field=>$value){
			$this->$field=$value;
		}
	}

}