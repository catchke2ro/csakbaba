<?php
namespace Fandepay\Api\Endpoints;

use Fandepay\Api\Model\Invoice;

class InvoiceUpdatePaymentstatus extends InvoiceSearch
{
    private $payment_status;

    public function __construct(Invoice $invoice = null, $newPaymentStatus = null)
    {
        parent::__construct($invoice);

        $this->payment_status = $newPaymentStatus;
    }

    public function setNewPaymentStatus($paymentStatus)
    {
        $this->payment_status = $paymentStatus;

        return $this;
    }

    public function getNewPaymentStatus()
    {
        return $this->payment_status;
    }

    protected function getData()
    {
        $data = parent::getData();

        $data['status'] = $this->getNewPaymentStatus();

        return $data;
    }

    protected function parseResult(array $result)
    {
        return $result['result'] === 'ok';
    }
}
