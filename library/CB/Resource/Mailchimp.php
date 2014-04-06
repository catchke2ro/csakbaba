<?

class CB_Resource_Mailchimp {

	public $mc;
	public $listId='fc8df37cb6';
	public $apiKey='2d9c511106eaf12b7e4e7824cf0e96ba-us3';

	public function __construct(){
		include APPLICATION_PATH.'/../library/Mailchimp/Mailchimp.php';
		$this->mc=new Mailchimp($this->apiKey);

	}

	public function subscribe($email, $vars=array()){
		$data=$this->mc->lists->getList(array('list_id'=>$this->listId));
		if(is_array($data['data'])){
			foreach($data['data'] as $list){
				try{
					$response=$this->mc->lists->subscribe($list['id'], array('email'=>$email), $vars);
				} catch(Exception $e){

				}
			}
		}
	}

	public function modifyEmail($oldEmail, $newEmail, $vars=array()){
		$data=$this->mc->lists->getList(array('list_id'=>$this->listId));
		if(is_array($data['data'])){
			foreach($data['data'] as $list){
				try{
					$this->mc->lists->unsubscribe($list['id'], array('email'=>$oldEmail));
					$this->mc->lists->subscribe($list['id'], array('email'=>$newEmail), $vars);
				} catch(Exception $e){

				}
			}
		}
	}

}