<?php
namespace Fandepay\Api\Model;

use Fandepay\Api\Utils;

abstract class Base
{
    public function __construct($data = null)
    {
        if (is_array($data)) {
            $this->fromArray($data);
        }
    }

    public function getDefaultValues()
    {
        $ref = new \ReflectionClass($this);

        return $ref->getDefaultProperties();
    }

    public function toArray()
    {
        $data = array();

        foreach (array_keys($this->getDefaultValues()) as $prop) {
            $data[$prop] = $this->$prop;
        }

        return $this->convertToArray($data);
    }

    public function fromArray(array $data, $ignoreAdditional = false)
    {
        foreach ($data as $key => $val) {
            if ($val === '') {
                $val = null;
            }

            $setter = 'set'.Utils::camelcase($key);
            if (method_exists($this, $setter)) {
                call_user_func(array($this, $setter), $val);
            } elseif (!$ignoreAdditional) {
                throw new \InvalidArgumentException(sprintf('Class %s does not accept data: %s', get_class($this), $key));
            }
        }

        return $this;
    }

    protected function convertToArray(array $data)
    {
        $ret = array();
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $ret[$key] = $this->convertToArray($val);
            } else {
                if ($val instanceof \DateTime) {
                    $val = $val->format('Y-m-d H:i:s');
                }
                $ret[$key] = $val instanceof Base ? $val->toArray() : $val;
            }
        }

        return $ret;
    }

    protected function getDateTime(\DateTime $dt = null, $format = null)
    {
        if (is_null($dt)) {
            return null;
        }

        return !is_null($format) ? $dt->format($format) : $dt;
    }

    protected function setDateTime($input)
    {
        if (empty($input)) {
            return null;
        }

        if ($input instanceof \DateTime) {
            return $input;
        }

        try {
            return new \DateTime((is_numeric($input) ? '@' : '') . $input);
        } catch (\Exception $e) {
            $trace = debug_backtrace();
            $caller = $trace[0]['function'];

            throw new \InvalidArgumentException(
                sprintf('Invalid date parameter (%s) for %s::%s()',
                    (string)$input, get_class($this), $caller),
                500,
                $e
            );
        }
    }
}
