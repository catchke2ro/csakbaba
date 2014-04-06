<?php
class SRG_Exception_Handler{

    protected $code = null;
    protected $message = null;
    protected $file = null;
    protected $line = null;
    protected  $trace = null;
    protected  $logMessage = null;
    protected  $logRecord = null;

    /*
     * If there is a 404 page in the CMS, it will use that, otherwise it uses the error controller page-not-found action
     */
    public function Error404(){
        $front=Zend_Controller_Front::getInstance();
        $pageModel=new \SRG\Model\Page();
        $page404=$pageModel->findOneBySlug('404');
        $front->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
        if($page404){
            Zend_Registry::set('errorpage', $page404);
            $front->getRequest()->contents=$page404->contents->toArray();
        } else {
            $front->getRequest()->setModuleName('frontend')->setControllerName('error')->setActionName('page-not-found');
            $front->unregisterPlugin('SRG_Controller_Frontend_Plugin_Pages');
        }
    }

    public function errorHandling($code, $message, $file, $line){
        if($code==2048) return;
        $this->code = $code;
        $this->message = $message;
        $this->file = $file;
        $this->line = $line;

        $this->newMessage();
        $this->showMessage();
    }

    public function exceptionHandling($e){
        $this->code = $e->getCode();
        $this->message = $e->getMessage();
        $this->file = $e->getFile();
        $this->line = $e->getLine();
        $this->trace = $e->getTraceAsString();

        $this->newMessage();
        $this->showMessage();
    }

    public function showMessage(){
        if(getenv('APPLICATION_ENVIRONMENT')=="development" || (!empty($_COOKIE['SRGCMSDEV']))){
            echo $this->logMessage;
        }else{
	        l($this->logMessage, 'err', true);
	        l($this->logRecord, 'err', true);
            //$GLOBALS['err'][] = $this->logMessage;
        }
        error_log($this->logRecord);
    }

    public function newMessage($clear = false,$error_file = 'error/exceptions_log.html' ) {
        $message = $this->message;
        $code = $this->code;
        $file = $this->file;
        $line = $this->line;
        $trace = $this->trace;
        $date = date('Y-m-d H:i:s');

        $log_message = "<h3>Exception information:</h3>
         <p>
            <strong>Date:</strong> {$date}
         </p>

         <p>
            <strong>Message:</strong> {$message}
         </p>

         <p>
            <strong>Code:</strong> {$code}
         </p>

         <p>
            <strong>File:</strong> {$file}
         </p>

         <p>
            <strong>Line:</strong> {$line}
         </p>";
        if(!is_null($this->trace)){
            $log_message .= "
            <p>
            <strong>Trace:</strong><pre> {$trace}</pre>
             </p>
            ";
        }
        $log_message .= "
         <br />
         <hr /><br /><br />";

        $this->logMessage = $log_message;
        $this->logRecord = "{$_SERVER['HTTP_HOST']} - {$date} - {$code} - {$message} - {$file} - {$line}";
    }
}
