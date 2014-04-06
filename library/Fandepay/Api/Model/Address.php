<?php
namespace Fandepay\Api\Model;

class Address extends Base
{
    const TYPE_HEAD = 'HEAD';
    const TYPE_MAILING = 'MAILING';
    const TYPE_BILLING = 'BILLING';

    protected $type = self::TYPE_BILLING;

    protected $label;

    protected $country_code;

    protected $region;

    protected $postal_code;

    protected $city;

    protected $address_line;

    protected $address_line2;

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    public function getCountryCode()
    {
        return $this->country_code;
    }

    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;
        return $this;
    }

    public function getRegion()
    {
        return $this->region;
    }

    public function setRegion($region)
    {
        $this->region = $region;
        return $this;
    }

    public function getPostalCode()
    {
        return $this->postal_code;
    }

    public function setPostalCode($postal_code)
    {
        $this->postal_code = $postal_code;
        return $this;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    public function getAddressLine()
    {
        return $this->address_line;
    }

    public function setAddressLine($address_line)
    {
        $this->address_line = $address_line;
        return $this;
    }

    public function getAddressLine2()
    {
        return $this->address_line2;
    }

    public function setAddressLine2($address_line2)
    {
        $this->address_line2 = $address_line2;
        return $this;
    }

    public static function getTypes()
    {
        return array(
            self::TYPE_BILLING,
            self::TYPE_HEAD,
            self::TYPE_MAILING
        );
    }
}
