<?php
namespace Fandepay\Api\Enum;

class Paymode extends Base
{
    const CREDIT_CARD = 'CREDIT_CARD';
    const BANK_TRANSFER = 'BANK_TRANSFER';
    const BANK_PAYMENT = 'BANK_PAYMENT';
    const CASH = 'CASH';
    const POSTAL_CHECK = 'POSTAL_CHECK';
    const PAYPAL = 'PAYPAL';
    const COUPON = 'COUPON';
    const CASH_ON_DELIVERY = 'CASH_ON_DELIVERY';
    const OTHER = 'OTHER';
}
