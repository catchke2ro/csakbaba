<?php
namespace Fandepay\Api\Webhooks;

use Fandepay\Api\Endpoints\InvoiceSearch;

class Invoice extends WebhookBase
{
    public function getData()
    {
        return $this->data = $this->requireParams(array(
            'invoice_number',
            'invoice_type',
            'payment_id',
            'payment_status',
            'pdf_url',
            'storno'
        ));
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
     * Fizetési státusz
     * @return array
     */
    public function getPaymentStatus()
    {
        return $this->getDataParam('payment_status');
    }

    /**
     * Számla számla
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
     * Számla pdf url
     * @return string
     */
    public function getPdfUrl()
    {
        return $this->getDataParam('pdf_url');
    }

    /**
     * A számla stornozott?
     * @return boolean
     */
    public function getStorno()
    {
        return (bool)$this->getDataParam('storno');
    }

    /**
     * Számla összes adtának lekérdezése
     * @param bool $exceptionOnNotfound
     * @return \Fandepay\Api\Model\Invoice
     */
    public function getInvoice($exceptionOnNotfound = true)
    {
        $invoice = new \Fandepay\Api\Model\Invoice();
        $invoice
            ->setNumber($this->getInvoiceNumber())
            ->setType($this->getInvoiceType())
        ;

        $endpoint = new InvoiceSearch($invoice);

        $result = $endpoint->getResult(null, $exceptionOnNotfound);

        return $result['invoice'];
    }
}
