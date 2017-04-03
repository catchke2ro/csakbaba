<?php
namespace CB\Model;

class BlogPost extends \CB_Resource_Model{
    
    public function getRelated($count = 10, $notId = []){
        $this->initQb();
        
        $notId = array_map(function($id){
            return is_string($id) ? new \MongoId($id) : $id;
        }, $notId);
        
        $this->qb->field('_id')->notIn($notId);
        $this->qb->sort('date', 'desc');
        $this->qb->limit($count);
        $related=$this->runQuery();
        return $related;
    }

}
