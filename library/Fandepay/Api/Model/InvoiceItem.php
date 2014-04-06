<?php
namespace Fandepay\Api\Model;

class InvoiceItem extends Base
{
    /**
     * Név
     * @var string
     */
    protected $name;

    /**
     * Mennyiség
     * @var float
     */
    protected $quantity;

    /**
     * Mennyiségi egység (pl db)
     * @var string
     */
    protected $unit;

    /**
     * Áfa kulcs (pl 27)
     * @var float
     */
    protected $vat_key;

    /**
     * Egységár
     * @var float
     */
    protected $amount_unit;

    /**
     * @var string
     */
    protected $comment;

    /**
     * @var string
     */
    protected $classification;

    /**
     * @param float $amount_unit
     */
    public function setAmountUnit($amount_unit)
    {
        $this->amount_unit = $amount_unit !== 0 && empty($amount_unit) ? null : (float)$amount_unit;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmountUnit()
    {
        return $this->amount_unit;
    }

    /**
     * @param string $classification
     */
    public function setClassification($classification)
    {
        $this->classification = $classification;

        return $this;
    }

    /**
     * @return string
     */
    public function getClassification()
    {
        return $this->classification;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param float $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity !== 0 && empty($quantity) ? null : (float)$quantity;

        return $this;
    }

    /**
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param string $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param float $vat_key
     */
    public function setVatKey($vat_key)
    {
        $this->vat_key = $vat_key !== 0 && empty($vat_key) ? null : (float)$vat_key;

        return $this;
    }

    /**
     * @return float
     */
    public function getVatKey()
    {
        return $this->vat_key;
    }
}
