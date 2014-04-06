<?php
namespace Fandepay\Api\Webhooks;

use Fandepay\Api\Config;
use Fandepay\Api\Utils;
use Fandepay\Api\Exceptions\ApiErrorException;

abstract class WebhookBase
{
    /**
     * @var \Fandepay\Api\Config\Handler
     */
    private $config;

    /**
     * @var array
     */
    protected $postData;

    /**
     * Ellenőrzött post adatok
     * @var array
     */
    protected $cleanPostData;

    /**
     * Válasz adatok
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $token;

    public function __construct()
    {
        $this->config = new Config\Handler();
    }

    abstract public function getData();

    public function getConfig()
    {
        return $this->config;
    }

    public function setPostData(array $data)
    {
        $this->postData = $data;

        return $this;
    }

    public function getPostData()
    {
        if (!is_null($this->cleanPostData)) {
            return $this->cleanPostData;
        }

        if (is_null($this->postData)) {
            if (empty($_POST)) {
                throw new \LogicException('nincs http POST adat');
            }

            $this->postData = $_POST;
        }

        $token = isset($this->postData['token']) ? $this->postData['token'] : null;
        if (is_null($token)) {
            throw new ApiErrorException('invalid response');
        }
        unset($this->postData['token']);

        $data = Utils::flatArray($this->postData);
        $this->token = Utils::hmach(Utils::createStrWithLengths($this->postData), $this->config['secret']);

        if ($this->token !== $token) {
            throw new ApiErrorException('invalid token');
        }

        $this->cleanPostData = $this->postData;

        return $this->postData;
    }

    public function getConfirmation()
    {
        try {
            $this->getPostData();

            return 'SUCCESS ' . $this->getToken();
        }  catch (\Exception $e) {
            return null;
        }
    }

    protected function requireParams(array $params)
    {
        $data = $this->getPostData();

        foreach ($params as $param) {
            if (!isset($data[$param])) {
                throw new ApiErrorException('invalid response: ' . $param . ' is missing');
            }
        }

        return $data;
    }

    protected function getDataParam($param)
    {
        if (is_null($this->data)) {
            $this->data = $this->getData();
        }

        return isset($this->data[$param]) ? $this->data[$param] : null;
    }

    /**
     * A hmac hashelt token a bejövő adatok alapján
     * @return string
     */
    public function getToken()
    {
        $this->getPostData();

        return $this->token;
    }

    /**
     * API kulcs beállítása
     * @param string $key
     */
    public function setApiKey($key)
    {
        $this->getConfig()->offsetSet('key', $key);

        return $this;
    }

    /**
     * API secret beállítása
     * @param string $secret
     */
    public function setApiSecret($secret)
    {
        $this->getConfig()->offsetSet('secret', $secret);

        return $this;
    }

    /**
     * API base url beállítása
     * @param string $baseUrl
     * @return \Fandepay\Api\Webhooks\WebhookBase
     */
    public function setApiBaseUrl($baseUrl)
    {
        $this->getConfig()->offsetSet('api_baseurl', $baseUrl);

        return $this;
    }
}
