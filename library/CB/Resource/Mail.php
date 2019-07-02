<?php
class CB_Resource_Mail extends Zend_Mail {

	public $options=array(
		'body'=>'',
		'fromName'=>'',
		'subject'=>'',
		'to'=>'',
		'toName'=>'',
		'cc'=>array(),
		'bcc'=>array(),
		'template'=>'default',
		'layout'=>'email',
		'data'=>array()
	);

	protected $defaultFrom=array('info@csakbaba.hu', 'csakbaba.hu');

	private $defaultOptions=array('body'=>'','fromName'=>'','subject'=>'','to'=>'','toName'=>'','cc'=>array(),'bcc'=>array(),'template'=>'default','layout'=>'email','data'=>array());

	function init(){
		$this->setHeaderEncoding(Zend_Mime::ENCODING_BASE64);
		$this->setDefaultTransport(new CB_Resource_Mailjet());
		$this->options['fromName']='csakbaba.hu';
	}

	private function _reset(){
		$this->clearSubject()->clearRecipients()->clearFrom();
		$this->options=$this->defaultOptions;
	}

	public function s($options=array()){
		$this->_reset();
		$this->setFrom($this->defaultFrom[0], $this->defaultFrom[1]);
		$this->options=array_merge($this->options, $options);

		$this->setBodyHtml($this->html());
		$this->addTo($this->options['to']);
		$this->setSubject($this->options['subject']);
		if(!empty($options['attachment']) && file_exists($options['attachment'])){
			$att=$this->createAttachment(file_get_contents($options['attachment']));
			$attExploded = explode('/', $options['attachment']);
			$att->filename=end($attExploded);
		}
		if(!empty($this->options['cc'])) $this->addCc($this->options['cc']);
		if(!empty($this->options['bcc'])) $this->addBcc($this->options['bcc']);

		return $this->send();
	}

	public function html(){
		$view=new Zend_View();
		$view->setScriptPath(APPLICATION_PATH.'/modules/frontend/views/emails/');
		$view->registerHelper(new CB_View_Helper_Url(), 'Url');
		$view->assign(array('data'=>$this->options['data']));
		$viewHtml=$view->render($this->options['template'].(strpos('.phtml', $this->options['template'])==false ? '.phtml' : ''));

		$layout=Zend_Layout::getMvcInstance();
		$layout->setLayout($this->options['layout'])->disableLayout();
		$subjectExploded = explode(' - ', $this->options['subject']);
		$layout->assign(array('title'=>end($subjectExploded),	'content'=>$viewHtml));
		$html=$layout->render();

		$html=mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
		$ctis=new CB_Resource_CssToInlineStyles($html, file_get_contents(APPLICATION_PATH.'/../public/stylesheets/css/email.css'));
		$html=$ctis->convert();

		return $html;
	}

}
