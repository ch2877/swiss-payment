<?php

namespace Z38\SwissPayment\Message;

use DateTime;
use DOMDocument;
use DOMElement;
use InvalidArgumentException;
use Z38\SwissPayment\Money;
use Z38\SwissPayment\PaymentInformation\PaymentInformation;
use Z38\SwissPayment\Text;

/**
 * CustomerCreditTransfer represents a Customer Credit Transfer Initiation (pain.001) message
 */
class CustomerCreditTransfer extends AbstractMessage
{
    // SPS-2021 version is supported until November 2024
    public const SPS_2021 = 'SPS-2021';
    public const SPS_2022 = 'SPS-2022';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $initiatingParty;

    /**
     * @var array
     */
    protected $payments;

    /**
     * @var DateTime
     */
    protected $creationTime;

    /**
     * @var string
     */
    protected $spsVersion;

    /**
     * @var string
     */
    protected $softwareName;

    /**
     * @var string
     */
    protected $softwareVersion;

    /**
     * @var string
     */
    protected $manufacturerName;

    /**
     * Constructor
     *
     * @param string $id              Identifier of the message (should usually be unique over a period of at least 90 days)
     * @param string $initiatingParty Name of the initiating party
     *
     * @throws InvalidArgumentException When any of the inputs contain invalid characters or are too long.
     */
    public function __construct($id, $initiatingParty, $spsVersion = self::SPS_2021, $softwareName = null, $softwareVersion = null, $manufacturerName = null)
    {
        $this->id = Text::assertIdentifier($id);
        $this->initiatingParty = Text::assert($initiatingParty, 70);
        $this->payments = [];
        $this->creationTime = new DateTime();
        $this->spsVersion = $spsVersion;
        $this->softwareName = $softwareName;
        $this->softwareVersion = $softwareVersion;
        $this->manufacturerName = $manufacturerName;
    }

    /**
     * Manually sets the creation time
     *
     * @param DateTime $creationTime The desired creation time
     *
     * @return CustomerCreditTransfer This message
     */
    public function setCreationTime(DateTime $creationTime)
    {
        $this->creationTime = $creationTime;

        return $this;
    }

    /**
     * Adds a payment instruction
     *
     * @param PaymentInformation $payment The payment to be added
     *
     * @return CustomerCreditTransfer This message
     */
    public function addPayment(PaymentInformation $payment)
    {
        $this->payments[] = $payment;

        return $this;
    }

    /**
     * Gets the number of payments
     *
     * @return int Number of payments
     */
    public function getPaymentCount()
    {
        return count($this->payments);
    }

    /**
     * Returns the name of the software used to create the message
     *
     * @return string
     */
    public function getSoftwareName()
    {
        return $this->softwareName;
    }

    /**
     * Returns the version of the software used to create the message
     *
     * @return string
     */
    public function getSoftwareVersion()
    {
        return $this->softwareVersion;
    }

    /**
     * Returns the sps version used to create the message
     *
     * @return string
     */
    public function getSpsVersion()
    {
        return $this->spsVersion;
    }

    /**
     * @return string
     */
    public function getSchemaName()
    {
        if ($this->spsVersion === self::SPS_2021){
            return 'http://www.six-interbank-clearing.com/de/pain.001.001.03.ch.02.xsd';
        } else {
            return 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.09';
        }
    }

    /**
     * @return string
     */
    public function getSchemaLocation()
    {
        if ($this->spsVersion === self::SPS_2021){
            return 'pain.001.001.03.ch.02.xsd';
        } else {
            return 'pain.001.001.09.ch.03.xsd';
        }
    }

    /**
     * Creates a DOM element which contains details about the software used to create the message
     *
     * @param DOMDocument $doc
     *
     * @return ?DOMElement
     */
    private function buildContactDetails(DOMDocument $doc)
    {
        // Software name is mandatory to build the contact details
        if (empty($this->softwareName)) {
            return null;
        }

        $root = $doc->createElement('CtctDtls');

        if ($this->spsVersion === self::SPS_2021) {
            $root->appendChild(Text::xml($doc, 'Nm', $this->softwareName));
            if (empty($this->softwareVersion) === false) {
                $root->appendChild(Text::xml($doc, 'Othr', $this->softwareVersion));
            }
        } else {
            if (empty($this->softwareName) === false) {
                $otherProductName = $doc->createElement('Othr');
                $otherProductName->appendChild($doc->createElement('ChanlTp', 'NAME'));
                $otherProductName->appendChild($doc->createElement('Id', $this->softwareName));
                $root->appendChild($otherProductName);
            }

            if (empty($this->softwareVersion) === false) {
                $otherSoftwareVersion = $doc->createElement('Othr');
                $otherSoftwareVersion->appendChild($doc->createElement('ChanlTp', 'VRSN'));
                $otherSoftwareVersion->appendChild($doc->createElement('Id', $this->softwareVersion));
                $root->appendChild($otherSoftwareVersion);
            }

            if (empty($this->manufacturerName) === false) {
                $otherManufacturerName = $doc->createElement('Othr');
                $otherManufacturerName->appendChild($doc->createElement('ChanlTp', 'PRVD'));
                $otherManufacturerName->appendChild($doc->createElement('Id', $this->manufacturerName));
                $root->appendChild($otherManufacturerName);
            }

            $otherSpsIgVersion = $doc->createElement('Othr');
            $otherSpsIgVersion->appendChild($doc->createElement('ChanlTp', 'SPSV'));
            $otherSpsIgVersion->appendChild($doc->createElement('Id', '0200'));
            $root->appendChild($otherSpsIgVersion);
        }

        return $root;
    }

    /**
     * {@inheritdoc}
     */
    protected function buildDom(DOMDocument $doc)
    {
        $transactionCount = 0;
        $transactionSum = new Money\MixedMoney(0);
        foreach ($this->payments as $payment) {
            $transactionCount += $payment->getTransactionCount();
            $transactionSum = $transactionSum->plus($payment->getTransactionSum());
        }

        $root = $doc->createElement('CstmrCdtTrfInitn');
        $header = $doc->createElement('GrpHdr');
        $header->appendChild(Text::xml($doc, 'MsgId', $this->id));
        $header->appendChild(Text::xml($doc, 'CreDtTm', $this->creationTime->format('Y-m-d\TH:i:sP')));
        $header->appendChild(Text::xml($doc, 'NbOfTxs', $transactionCount));
        $header->appendChild(Text::xml($doc, 'CtrlSum', $transactionSum->format()));
        $initgParty = $doc->createElement('InitgPty');
        $initgParty->appendChild(Text::xml($doc, 'Nm', $this->initiatingParty));
        $contactDetails = $this->buildContactDetails($doc);
        if (isset($contactDetails)) {
            $initgParty->appendChild($contactDetails);
        }
        $header->appendChild($initgParty);
        $root->appendChild($header);

        foreach ($this->payments as $payment) {
            $root->appendChild($payment->asDom($doc, $this->spsVersion));
        }

        return $root;
    }
}
