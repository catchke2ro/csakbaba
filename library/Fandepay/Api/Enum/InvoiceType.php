<?php
namespace Fandepay\Api\Enum;

class InvoiceType extends Base
{
    const PREINVOICE = 'PREINVOICE';
    const INVOICE = 'INVOICE';

    const DONATION_REQUEST = 'DONATION_REQUEST';
    const DONATION_INVOICE = 'DONATION_INVOICE';

    /**
     * "pre" típusok, mint pl díjbekérő
     * @return array
     */
    public static function getPreTypes()
    {
        return array(
            self::PREINVOICE,
            self::DONATION_REQUEST
        );
    }

    /**
     * Számla típusok
     * @return array
     */
    public static function getInvoiceTypes()
    {
        return array(
            self::INVOICE,
            self::DONATION_INVOICE
        );
    }
}
