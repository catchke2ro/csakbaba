<?php
namespace Fandepay\Api\Model;

class Contact extends Base
{
    protected $firstname;

    protected $lastname;

    protected $position;

    protected $email;

    protected $telephone;

    protected $is_default = false;

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getTelephone()
    {
        return $this->telephone;
    }

    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getIsDefault()
    {
        return $this->is_default;
    }

    public function setIsDefault($is_default)
    {
        $this->is_default = (bool) $is_default;
        return $this;
    }
}
