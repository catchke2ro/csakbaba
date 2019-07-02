<?php
namespace CB;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="blog_posts")
 */
class BlogPost extends \CB_Resource_ModelItem {

	/**
	 * @ODM\Id
	 */
	public $id;

	/**
	 * @ODM\Field(type="string")
	 */
	public $title;

	/**
	 * @ODM\Field(type="string")
	 */
	public $slug;

	/**
	 * @ODM\Field(type="date")
	 */
	public $date;

	/**
	 * @ODM\Field(type="string")
	 */
	public $teaser;

	/**
	 * @ODM\Field(type="string")
	 */
	public $body;

    public function getComments(){
        $commentModel=new \CB\Model\Comment();
        $comments=$commentModel->find(array('conditions'=>array(
            'post_id'=>$this->id
        ), 'order'=>'date ASC'));
        return $comments ? $comments : array();
    }

}
