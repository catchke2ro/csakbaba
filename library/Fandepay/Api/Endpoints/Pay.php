<?php
namespace Fandepay\Api\Endpoints;

use Fandepay\Api\Model\Customer;
use Fandepay\Api\Model\Invoice;

class Pay extends EndpointBase
{
    /**
     * Fizetési azonosító
     * @var string
     */
    private $paymentId;

    /**
     * @var \Fandepay\Api\Model\Customer
     */
    private $customer;

    /**
     * @var \Fandepay\Api\Model\Invoice
     */
    private $invoice;

    public function __construct($paymentId, Customer $customer, Invoice $invoice)
    {
        $this->paymentId = $paymentId;
        $this->customer = $customer;
        $this->invoice = $invoice;

        parent::__construct();
    }

    public function getPaymentId()
    {
        return $this->paymentId;
    }

    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    /**
     * @param \Fandepay\Api\Model\Customer $customer
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return \Fandepay\Api\Model\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param \Fandepay\Api\Model\Invoice $invoice
     */
    public function setInvoice(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return \Fandepay\Api\Model\Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    protected function getData()
    {
        return array(
            'payment_id' => $this->paymentId,
            'customer' => $this->customer->toArray(),
            'invoice'  => $this->invoice->toArray()
        );
    }
}
