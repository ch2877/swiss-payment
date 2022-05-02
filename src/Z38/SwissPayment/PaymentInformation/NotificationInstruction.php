<?php

namespace Z38\SwissPayment\PaymentInformation;

use DOMDocument;
use DOMElement;
use InvalidArgumentException;

/**
 * NotificationInstruction contains the instruction to control the debit advice
 */
class NotificationInstruction
{
    /**
     * @var string
     */
    protected $instruction;

    /**
     * Constructor
     *
     * @param string $instruction
     *
     * @throws InvalidArgumentException When the code is not valid
     */
    public function __construct($instruction)
    {
        $instruction = (string)$instruction;
        if (!in_array($instruction, ['NOA', 'SIA', 'CND', 'CWD'], true)) {
            throw new InvalidArgumentException('The notification instruction is not valid. It must be one of the following: NOA, SIA, CND or CWD');
        }

        $this->instruction = $instruction;
    }

    /**
     * @param string $instruction
     * @param bool $batchBooking
     * @return bool
     */
    public function checkAgainstBatchBooking($batchBooking)
    {
        if ($batchBooking === false && !in_array($this->instruction, ['NOA', 'SIA'], true)) {
            return false;
        }
        if ($batchBooking === true && !in_array($this->instruction, ['NOA', 'CND', 'CWD'], true)) {
            return false;
        }
        return true;
    }

    /**
     * Returns an XML representation of this purpose
     *
     * @param DOMDocument $doc
     *
     * @return DOMElement The built DOM element
     */
    public function asDom(DOMDocument $doc)
    {
        return $doc->createElement('Prtry', $this->instruction);
    }
}
