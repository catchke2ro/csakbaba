<?php
namespace Fandepay\Api\Endpoints;

use Fandepay\Api\Model\Invoice;

class InvoiceSearch extends EndpointBase
{
    private $invoice;

    public function __construct(Invoice $invoice = null)
    {
        $this->invoice = $invoice;

        parent::__construct();
    }

    public function getInvoice()
    {
        return $this->invoice;
    }

    public function setInvoice(Invoice $invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }

    protected function getData()
    {
        if (is_null($this->invoice)) {
            throw new \LogicException('invoice input is required');
        }

        return array(
            'invoice' => $this->invoice->toArray()
        );
    }

    protected function parseResult(array $result)
    {
        if (!isset($result['invoice'])) {
            $result['invoice'] = null;
        } else {
            $result['invoice'] = new Invoice($result['invoice']);
        }

        return $result;
    }
}
