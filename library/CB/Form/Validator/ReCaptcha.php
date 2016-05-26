<?php

class CB_Form_Validator_ReCaptcha extends Zend_Validate_Abstract{

    const INVALID  = 'notEmptyInvalid';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID => "A CAPTCHA érvénytelen, vagy nincs bepipálva",
    );

    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
        $config = Zend_Registry::get('CsbConfig')->get('recaptcha');
        $secretKey = $config->get('secretKey');

        $client = new Zend_Http_Client();
        $client->setUri('https://www.google.com/recaptcha/api/siteverify')->setMethod('POST');
        $client->setParameterPost([
            'secret'=>$secretKey,
            'response'=>$value,
            'remoteip'=>$_SERVER['REMOTE_ADDR']
        ]);
        $response = $client->request();

        $responseArray = json_decode($response->getBody(), true);

        if(empty($responseArray['success'])){
            $this->_error(self::INVALID);
            return false;
        }
        return true;
    }

}