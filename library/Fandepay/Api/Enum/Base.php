<?php
namespace Fandepay\Api\Enum;

abstract class Base
{
    public static function toArray()
    {
        $r = new \ReflectionClass(get_called_class());

        return $r->getConstants();
    }
}
