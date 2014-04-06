<?php
namespace Fandepay\Api\Model;

class Customer extends Base
{
    const TYPE_PERSON = 'PERSON';
    const TYPE_COMPANY = 'COMPANY';

    protected $id;

    protected $type;

    protected $name;

    protected $email;

    protected $fiscalcode;

    protected $fiscalcode_eu;

    protected $registry_number;

    protected $bankaccountnumber;

    protected $addresses = array();

    protected $contacts = array();

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id ? (int)$id : null;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getFiscalcode()
    {
        return $this->fiscalcode;
    }

    public function setFiscalcode($fiscalcode)
    {
        $this->fiscalcode = $fiscalcode;
        return $this;
    }

    public function getFiscalcodeEu()
    {
        return $this->fiscalcode_eu;
    }

    public function setFiscalcodeEu($fiscalcode_eu)
    {
        $this->fiscalcode_eu = $fiscalcode_eu;
        return $this;
    }

    public function getRegistryNumber()
    {
        return $this->registry_number;
    }

    public function setRegistryNumber($registry_number)
    {
        $this->registry_number = $registry_number;
        return $this;
    }

    public function getBankaccountnumber()
    {
        return $this->bankaccountnumber;
    }

    public function setBankaccountnumber($bankaccountnumber)
    {
        $this->bankaccountnumber = $bankaccountnumber;
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

    public function getAddresses()
    {
        return $this->addresses;
    }

    public function setAddresses(array $addresses)
    {
        $this->addresses = array();
        foreach ($addresses as $address) {
            if (is_array($address)) {
                $address = new Address($address);
            } elseif (!($address instanceof Address)) {
                throw new \InvalidArgumentException('address must be instance of \Fandepay\Api\Model\Address or array, '.gettype($address).' given');
            }

            $this->addAddress($address);
        }

        return $this;
    }

    public function addAddress(Address $address)
    {
        $this->addresses[] = $address;
        return $this;
    }

    public function removeAddress(Address $address)
    {
        foreach ($this->addresses as $key => $a) {
            if ($a === $address) {
                unset($this->addresses[$key]);
                return true;
            }
        }

        return false;
    }

    public function getContacts()
    {
        return $this->contacts;
    }

    public function setContacts(array $contacts)
    {
        $this->contacts = array();
        foreach ($contacts as $contact) {
            if (is_array($contact)) {
                $contact = new Contact($contact);
            } elseif (!($contact instanceof Contact)) {
                throw new \InvalidArgumentException('contact must be instance of \Fandepay\Api\Model\Contact or array, '.gettype($contact).' given');
            }

            $this->addContact($contact);
        }
        return $this;
    }

    public function addContact(Contact $contact)
    {
        $this->contacts[] = $contact;
        return $this;
    }

    public function removeContact(Contact $contact)
    {
        foreach ($this->contacts as $key => $c) {
            if ($c === $contact) {
                unset($this->contacts[$key]);
                return true;
            }
        }

        return false;
    }

    public static function getTypes()
    {
        return array(
            self::TYPE_PERSON,
            self::TYPE_COMPANY
        );
    }
}
