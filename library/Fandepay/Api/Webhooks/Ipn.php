<?php
namespace Fandepay\Api\Webhooks;

use Fandepay\Api\Model\Invoice;
use Fandepay\Api\Endpoints\InvoiceSearch;

class Ipn extends Backref
{
    public function getData()
    {
        $data = parent::getData();

        return $this->data = array_merge($data, $this->requireParams(array(
            'invoice_number',
            'invoice_type',
            'pdf_url'
        )));
    }

    /**
     * Számla száma
     * @return string
     */
    public function getInvoiceNumber()
    {
        return $this->getDataParam('invoice_number');
    }

    /**
     * Számla típusa
     * @return string
     */
    public function getInvoiceType()
    {
        return $this->getDataParam('invoice_type');
    }

    /**
     * Pdf url
     * @return string
     */
    public function getPdfUrl()
    {
        return $this->getDataParam('pdf_url');
    }

    /**
     * Számla összes adtának lekérdezése
     * @param bool $exceptionOnNotfound
     * @return \Fandepay\Api\Model\Invoice
     */
    public function getInvoice($exceptionOnNotfound = true)
    {
        $invoice = new Invoice();
        $invoice
            ->setNumber($this->getInvoiceNumber())
            ->setType($this->getInvoiceType())
        ;

        $endpoint = new InvoiceSearch($invoice);

        $result = $endpoint->getResult(null, $exceptionOnNotfound);

        return $result['invoice'];
    }
}
