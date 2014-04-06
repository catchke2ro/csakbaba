<?php
namespace Fandepay\Api\Webhooks;

use Fandepay\Api\Model\Customer;
use Fandepay\Api\Endpoints\CustomerSearch;

class Backref extends WebhookBase
{
    public function getData()
    {
        return $this->data = $this->requireParams(array(
            'status',
            'customer_id',
            'payment_id'
        ));
    }

    /**
     * Fizetés státusza
     * @return string
     */
    public function getStatus()
    {
        return $this->getDataParam('status');
    }

    /**
     * Customer egyedi azonosítója
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getDataParam('customer_id');
    }

    /**
     * Fizetési azonosító
     * @return string
     */
    public function getPaymentId()
    {
        return $this->getDataParam('payment_id');
    }

    /**
     * Api kérés a Customer adatainak lekérdezéséhez
     * @param bool $exceptionOnNotfound
     * @return \Fandepay\Api\Model\Customer
     */
    public function getCustomer($exceptionOnNotfound = true)
    {
        $customer = new Customer();
        $customer->setId($this->getCustomerId());

        $endPoint = new CustomerSearch($customer);
        $result = $endPoint->getResult(null, $exceptionOnNotfound);

        return $result['customer'];
    }
}
