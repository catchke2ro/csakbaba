<?php
namespace Fandepay\Api\Exceptions;

class ApiErrorException extends \Exception
{
    private $details;

    public function __construct($error, $details = null, $previous = null)
    {
        parent::__construct($error, 500, $previous);

        $this->details = $details;
    }

    /**
     * Hiba részletek, ha vannak
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }
}
