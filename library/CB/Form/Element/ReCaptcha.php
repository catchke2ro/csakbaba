<?php

class CB_Form_Element_ReCaptcha extends Zend_Form_Element_Xhtml {

	public $helper = 'formReCaptcha';

    private $_siteKey;

    private $_secretKey;

    public function __construct($spec, $options)
    {
        parent::__construct($spec, $options);

        $config = Zend_Registry::get('CsbConfig')->get('recaptcha');
        $this->setSiteKey($config->get('siteKey'));
        $this->setSecretKey($config->get('secretKey'));

        $this->setAttrib('data-sitekey', $this->getSiteKey());

    }


    /**
     * @return mixed
     */
    public function getSiteKey()
    {
        return $this->_siteKey;
    }

    /**
     * @param mixed $siteKey
     */
    public function setSiteKey($siteKey)
    {
        $this->_siteKey=$siteKey;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSecretKey()
    {
        return $this->_secretKey;
    }

    /**
     * @param mixed $secretKey
     */
    public function setSecretKey($secretKey)
    {
        $this->_secretKey=$secretKey;
        return $this;
    }
}