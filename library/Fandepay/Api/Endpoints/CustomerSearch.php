<?php
namespace Fandepay\Api\Endpoints;

use Fandepay\Api\Model\Customer;

class CustomerSearch extends EndpointBase
{
    public function __construct(Customer $customer = null)
    {
        $this->customer = $customer;

        parent::__construct();
    }

    public function searchByFiscalcode($fiscalCode, $fiscalcodeEu = null)
    {
        return $this->setCustomer(new Customer(array(
            'fiscalcode' => $fiscalCode,
            'fiscalcode_eu' => $fiscalcodeEu
        )));
    }

    public function searchByEmail($email)
    {
        return $this->setCustomer(new Customer(array(
            'email' => $email
        )));
    }

    public function getCustomer()
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    protected function getData()
    {
        if (is_null($this->customer)) {
            throw new \LogicException('input is required');
        }

        return array('customer' => $this->customer->toArray());
    }

    protected function parseResult(array $result)
    {
        $result['customer'] = isset($result['customer']) ? new Customer($result['customer']) : null;

        return $result;
    }
}
