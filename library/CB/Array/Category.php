<?

class CB_Array_Category {


	public $name;
	public $id;
	public $url;
	public $slug;
	public $props;
	public $parent_id;
	public $sex;
	/**
	 * @var CB_Array_Category[]
	 */
	public $children=array();
	public $promoteName='';

	public function __construct($category, $id, $url='', $props=array(), $sex, $parent_id=''){
		$this->name=$category['name'];
		$this->id=$id;
		$this->url=$url;
		$this->slug=$category['slug'];
		$this->props=$props;
		$this->parent_id=$parent_id;
		$this->sex=$sex;
		$this->promoteName=!empty($category['promoteName']) ? $category['promoteName'] : '';
	}


}