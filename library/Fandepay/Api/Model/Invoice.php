<?php
namespace Fandepay\Api\Model;

use Fandepay\Api\Enum\Currency;
use Fandepay\Api\Enum\PaymentStatus;
use Fandepay\Api\Enum\Language;
use Fandepay\Api\Enum\InvoiceType;
use Fandepay\Api\Enum\Paymode;

class Invoice extends Base
{
    protected $type = InvoiceType::INVOICE;

    protected $number;

    protected $date;

    protected $fulfillment_date;

    protected $payment_deadline;

    protected $currency = Currency::HUF;

    protected $paymode = Paymode::CREDIT_CARD;

    protected $language = Language::HU;

    protected $send_email = true;

    protected $payment_status = PaymentStatus::NOT_PAID;

    protected $paid_at;

    protected $storno = false;

    protected $timed;

    protected $strict_number;

    protected $stricted_at;

    protected $subject;

    protected $comment;

    /**
     * @var \Fandepay\Api\Model\InvoiceItem[]
     */
    protected $items = array();

    /**
     * A számla pdf url
     * @var string
     * @since 1.1
     */
    protected $pdf_url;

    /**
     * Egyedi fizetési azonosító
     * @var string
     * @since 1.2
     */
    protected $payment_id;

    public function __construct($data = null)
    {
        $this->setDate('today');

        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param \DateTime|string|int $date
     */
    public function setDate($date = null)
    {
        $this->date = $this->setDateTime($date);

        return $this;
    }

    /**
     * @param string $format Pl "Y.m.d. H:i:s"
     * @return \DateTime|string
     */
    public function getDate($format = null)
    {
        return $this->getDateTime($this->date, $format);
    }

    /**
     * @param \DateTime|string|int $fulfillment_date
     */
    public function setFulfillmentDate($fulfillment_date)
    {
        $this->fulfillment_date = $this->setDateTime($fulfillment_date);

        return $this;
    }

    /**
     * @param string $format Pl "Y.m.d. H:i:s"
     * @return \DateTime|string
     */
    public function getFulfillmentDate($format = null)
    {
        return $this->getDateTime($this->fulfillment_date, $format);
    }

    /**
     * @param array $items
     */
    public function setItems(array $items)
    {
        $this->items = array();
        foreach ($items as $item) {
            if (is_array($item)) {
                $item = new InvoiceItem($item);
            } elseif (!($item instanceof InvoiceItem)) {
                throw new \InvalidArgumentException('invoice item must be instance of \Fandepay\Api\Model\InvoiceItem or array, '.gettype($item).' given');
            }

            $this->addItem($item);
        }

        return $this;
    }

    /**
     * @return \Fandepay\Api\Model\InvoiceItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    public function addItem(InvoiceItem $item)
    {
        $this->items[] = $item;

        return $this;
    }

    public function removeItem(InvoiceItem $item)
    {
        foreach ($this->items as $key => $i) {
            if ($i === $item) {
                unset($this->items[$key]);
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param \DateTime|string|int $paid_at
     */
    public function setPaidAt($paid_at)
    {
        $this->paid_at = $this->setDateTime($paid_at);

        if (!is_null($this->paid_at)) {
            $this->setPaymentStatus(PaymentStatus::PAID);
        }

        return $this;
    }

    /**
     * @param string $format Pl "Y.m.d. H:i:s"
     * @return \DateTime|string
     */
    public function getPaidAt($format = null)
    {
        return $this->getDateTime($this->paid_at, $format);
    }

    /**
     * @param \DateTime|string|int $payment_deadline
     */
    public function setPaymentDeadline($payment_deadline)
    {
        $this->payment_deadline = $this->setDateTime($payment_deadline);

        return $this;
    }

    /**
     * @param string $format Pl "Y.m.d. H:i:s"
     * @return \DateTime|string
     */
    public function getPaymentDeadline($format = null)
    {
        return $this->getDateTime($this->payment_deadline, $format);
    }

    /**
     * @param string $payment_status
     */
    public function setPaymentStatus($payment_status)
    {
        $this->payment_status = $payment_status;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentStatus()
    {
        return $this->payment_status;
    }

    /**
     * @param string $paymode
     */
    public function setPaymode($paymode)
    {
        $this->paymode = $paymode;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymode()
    {
        return $this->paymode;
    }

    /**
     * @param bool $send_email
     */
    public function setSendEmail($send_email)
    {
        $this->send_email = (int)$send_email;

        return $this;
    }

    /**
     * @return bool
     */
    public function getSendEmail()
    {
        return $this->send_email;
    }

    /**
     * @param bool $storno
     */
    public function setStorno($storno)
    {
        $this->storno = (bool)$storno;

        return $this;
    }

    /**
     * @return bool
     */
    public function getStorno()
    {
        return $this->storno;
    }

    /**
     * @param int $strict_number
     */
    public function setStrictNumber($strict_number)
    {
        $this->strict_number = $strict_number;

        return $this;
    }

    /**
     * @return int
     */
    public function getStrictNumber()
    {
        return $this->strict_number;
    }

    /**
     * @param \DateTime|string|int $stricted_at
     */
    public function setStrictedAt($stricted_at)
    {
        $this->stricted_at = $this->setDateTime($stricted_at);

        return $this;
    }

    /**
     * @param string $format Pl "Y.m.d. H:i:s"
     * @return \DateTime|string
     */
    public function getStrictedAt($format = null)
    {
        return $this->getDateTime($this->stricted_at, $format);
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param \DateTime|string|int $timed
     */
    public function setTimed($timed = null)
    {
        $this->timed = $this->setDateTime($timed);

        return $this;
    }

    /**
     * @param string $format Pl "Y.m.d. H:i:s"
     * @return \DateTime|string
     */
    public function getTimed($format = null)
    {
        return $this->getDateTime($this->timed, $format);
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param \DateTime|string|int $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $this->setDateTime($created_at);

        return $this;
    }

    /**
     * @param string $format Pl "Y.m.d. H:i:s"
     * @return \DateTime|string
     */
    public function getCreatedAt($format = null)
    {
        return $this->getDateTime($this->created_at, $format);
    }

    /**
     * @return string
     */
    public function getPdfUrl()
    {
        return $this->pdf_url;
    }

    /**
     * @param string $pdf_url
     */
    public function setPdfUrl($pdf_url)
    {
        $this->pdf_url = $pdf_url;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentId()
    {
        return $this->payment_id;
    }

    /**
     * @param string $payment_id
     */
    public function setPaymentId($payment_id)
    {
        $this->payment_id = $payment_id;

        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function isPaid()
    {
        return $this->getPaymentStatus() === PaymentStatus::PAID;
    }
}
