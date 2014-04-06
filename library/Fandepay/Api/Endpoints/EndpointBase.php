<?php
namespace Fandepay\Api\Endpoints;

use Fandepay\Api\Entity\Base;
use Fandepay\Api\Config;
use Fandepay\Api\Exceptions\ApiErrorException;
use Fandepay\Api\Utils;

abstract class EndpointBase
{
    /**
     * @var \Fandepay\Api\Config\Handler
     */
    private $config;

    /**
     * Az endpointnak átadandó adatok
     * @return array
     */
    abstract protected function getData();

    /**
     * @param string $configPath A defaulton kívüli config
     */
    public function __construct()
    {
        $this->config = new Config\Handler();
    }

    /**
     * A felhasználóhoz tartozó api kulcs
     * @return string
     */
    public function getKey()
    {
        return $this->config['key'];
    }

    /**
     * A felhasználóhoz tartozó api secret
     * @return string
     */
    public function getSecret()
    {
        return $this->config['secret'];
    }

    /**
     * @return \Fandepay\Api\Config\Handler
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Az elküldendő adatok alapján készülő hmac hash
     * @return string
     */
    public function getToken()
    {
        $data = $this->getData();
        $aryData = array();
        foreach ($data as $key => $val) {
            $aryData[$key] = $val instanceof Base ? $val->toArray() : (array)$val;
        }

        $hashdata = Utils::flatArray($aryData);
        $str = Utils::createStrWithLengths($hashdata);

        return Utils::hmach($str, $this->getSecret());
    }

    /**
     * Az endpoint teljes url-e
     * @return string
     */
    public function getUrl()
    {
        return rtrim($this->config['api_baseurl'], '/ ') . $this->getUrlPath();
    }

    /**
     * Minden adat, amit az apinak át kell adnia az endpoint felé
     * @return array
     */
    public function getPostData()
    {
        return array_merge(array(
            'api_key' => $this->getKey(),
            'token'   => $this->getToken()
        ), $this->getData());
    }

    /**
     * Api hívást intéz curl segítségével, vagy a már meglévő választ feldolgozza
     * @param string $result
     * @param bool $throwException Dobjon exceptiont, ha hiba jött vissza?
     * @throws Fandepay\Api\Exceptions\ApiErrorException
     * @return array
     */
    public function getResult($result = null, $throwException = false)
    {
        if (is_null($result)) {
            $result = $this->curl();
        }

        if (!is_array($result)) {
            $result = json_decode($result, true);

            if ($result === null) {
                throw new ApiErrorException('invalid result, the result should be a valid json string', array(
                    'response' => $result
                ));
            }
        }

        if ($throwException && (empty($result) || isset($result['error']))) {
            throw new ApiErrorException(isset($result['error']) ? $result['error'] : 'no data received', isset($result['error_details']) ? $result['error_details'] : null);
        }

        return $this->parseResult($result);
    }

    /**
     * Visszakapott értékek feldolgozása, tömbökből objektumok készítése
     * @param array $result
     * @return array
     */
    protected function parseResult(array $result)
    {
        return $result;
    }

    /**
     * Az endpoint url path része
     * @return string
     */
    public function getUrlPath()
    {
        $ref = new \ReflectionClass(get_class($this));

        return '/'.strtolower(preg_replace('~(?<=\\w)([A-Z])~', '/$1', $ref->getShortName()));
    }

    /**
     * Api kérés curl segítségével
     * @throws \Fandepay\Api\Exceptions\ApiErrorException
     * @return string
     */
    public function curl()
    {
        $ch = curl_init($this->getUrl());

        curl_setopt_array($ch, array(
            CURLOPT_POST            => true,
            CURLOPT_POSTFIELDS      => $this->createQueryString($this->getPostData()),
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HEADER          => false,
        ));

        $result = curl_exec($ch);

        $errno = curl_errno($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if ($result === false || $errno != 0) {
            throw new ApiErrorException('CURL error: ' . $errno . ' ' . ($error ?: 'unkown error'), array(
                'curl_errno'    => $errno,
                'curl_errormsg' => $error
            ));
        }

        return $result;
    }

    /**
     * Form, amiben minden szükséges adat megtalálható hidden mezőkben
     * @param string $formId
     * @param string $submitBtn
     * @param string $tags
     * @return string
     */
    public function getForm($formId = 'fandepay_payform', $submitBtn = true, $tags = true)
    {
        $output = '';
        if ($tags) {
            $output .= sprintf('<form id="%s" action="%s" method="POST">%s', $formId, $this->getUrl(), "\n");
        }

        $output .= $this->createFormFields($this->getData());

        if ($submitBtn) {
            if ($submitBtn === true) {
                $submitBtn = '<input type="submit" value="Fizetés">';
            }

            $output .= $submitBtn;
        }

        if ($tags) {
            $output .= '</form>';
        }

        return $output;
    }

    /**
     * Hidden input mezők készítése többdimenziós tömbből
     * @param array $data
     * @param string $parent
     * @return string
     */
    protected function createFormFields(array $data, $parent = null)
    {
        $str = '';
        foreach ($data as $name => $val)
        {
            if ($parent !== null) {
                $name = $parent.'['.$name.']';
            }

            if (is_array($val)) {
                $str .= $this->createFormFields($val, $name);
            }

            $str .= sprintf('<input type="hidden" name="%s" value="%s">%s', $name, $val, "\n");
        }

        return $str;
    }

    /**
     * Query string készítése curl hívás számára többdimenziós tömbből
     * @param array $data
     * @param string $parent
     * @param bool $returnArray
     * @return string|array
     */
    protected function createQueryString(array $data, $parent = null, $returnArray = false)
    {
        $query = array();
        foreach ($data as $name => $val) {
            if ($parent !== null) {
                $name = $parent.'['.$name.']';
            }

            if (is_array($val)) {
                $query = array_merge($query, $this->createQueryString($val, $name, true));
            } else {
                $query[] = $name.'='.urlencode($val);
            }
        }

        return $returnArray ? $query : implode('&', $query);
    }
}
