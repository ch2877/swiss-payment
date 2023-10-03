<?php

namespace Z38\SwissPayment\Tests\Message;

use Exception;
use DOMDocument;
use DOMXPath;
use Z38\SwissPayment\BIC;
use Z38\SwissPayment\FinancialInstitutionAddress;
use Z38\SwissPayment\GeneralAccount;
use Z38\SwissPayment\IBAN;
use Z38\SwissPayment\IID;
use Z38\SwissPayment\ISRParticipant;
use Z38\SwissPayment\Message\CustomerCreditTransfer;
use Z38\SwissPayment\Money;
use Z38\SwissPayment\PaymentInformation\CategoryPurposeCode;
use Z38\SwissPayment\PaymentInformation\PaymentInformation;
use Z38\SwissPayment\PaymentInformation\SEPAPaymentInformation;
use Z38\SwissPayment\PostalAccount;
use Z38\SwissPayment\StructuredPostalAddress;
use Z38\SwissPayment\Tests\TestCase;
use Z38\SwissPayment\TransactionInformation\BankCreditTransfer;
use Z38\SwissPayment\TransactionInformation\BankCreditTransferWithCreditorReference;
use Z38\SwissPayment\TransactionInformation\BankCreditTransferWithQRR;
use Z38\SwissPayment\TransactionInformation\ForeignCreditTransfer;
use Z38\SwissPayment\TransactionInformation\IS1CreditTransfer;
use Z38\SwissPayment\TransactionInformation\IS2CreditTransfer;
use Z38\SwissPayment\TransactionInformation\ISRCreditTransfer;
use Z38\SwissPayment\TransactionInformation\PurposeCode;
use Z38\SwissPayment\TransactionInformation\SEPACreditTransfer;
use Z38\SwissPayment\UnstructuredPostalAddress;

/**
 * @coversDefaultClass \Z38\SwissPayment\Message\CustomerCreditTransfer
 */
class CustomerCreditTransferTest extends TestCase
{
    /**
     * @return CustomerCreditTransfer
     */
    protected function buildMessageSPS2021()
    {
        $message = new CustomerCreditTransfer('message-000', 'InnoMuster AG', CustomerCreditTransfer::SPS_2021, 'softwareName', 'version');

        // Test payment-100 : IS1CreditTransfer (local instrument CH01) and IS2CreditTransfer (local instrument CH02)
        $payment = new PaymentInformation(
            'payment-100',
            'InnoMuster AG',
            new BIC('ZKBKCHZZ80A'),
            new IBAN('CH6600700110000204481')
        );
        $message->addPayment($payment);

        $transaction = new IS1CreditTransfer(
            'instr-101',
            'e2e-101',
            new Money\CHF(30000), // CHF 300.00
            'Finanzverwaltung Stadt Musterhausen',
            new StructuredPostalAddress('Altstadt', '1a', '4998', 'Muserhausen'),
            new PostalAccount('80-5928-4')
        );
        $payment->addTransaction($transaction);

        $transaction = new IS2CreditTransfer(
            'instr-102',
            'e2e-102',
            new Money\CHF(20000), // CHF 200.00
            'Druckerei Muster GmbH',
            new StructuredPostalAddress('Gartenstrasse', '61', '3000', 'Bern'),
            new IBAN('CH03 0900 0000 3054 1118 8'),
            'Musterbank AG',
            new PostalAccount('80-151-4')
        );
        $transaction->setRemittanceInformation("Test Remittance");
        $payment->addTransaction($transaction);

        // Test payment-110 : ISRCreditTransfer (local instrument CH03)
        $payment = new PaymentInformation(
            'payment-110',
            'InnoMuster AG',
            new BIC('POFICHBEXXX'),
            new IBAN('CH6309000000250097798')
        );
        $message->addPayment($payment);

        $transaction = new ISRCreditTransfer(
            'instr-110',
            'e2e-110',
            new Money\CHF(20000), // CHF 200.00
            new ISRParticipant('01-1439-8'),
            '210000000003139471430009017'
        );
        $payment->addTransaction($transaction);

        $transaction = new ISRCreditTransfer(
            'instr-111',
            'e2e-111',
            new Money\CHF(20000), // CHF 200.00
            new ISRParticipant('01-95106-8'),
            '6019701803969733825'
        );
        $transaction->setCreditorDetails(
            'Fritz Bischof',
            new StructuredPostalAddress('Dorfstrasse', '17', '9911', 'Musterwald')
        );
        $payment->addTransaction($transaction);

        // Test payment-120 : IS1CreditTransfer (local instrument CH03) with SALA category purpose
        $payment = new PaymentInformation(
            'payment-120',
            'InnoMuster AG',
            new BIC('POFICHBEXXX'),
            new IBAN('CH6309000000250097798')
        );
        $payment->setCategoryPurpose(new CategoryPurposeCode('SALA'));
        $message->addPayment($payment);

        $transaction = new IS1CreditTransfer(
            'instr-120',
            'e2e-120',
            new Money\CHF(50000), // CHF 500.00
            'Meier & Söhne AG',
            new StructuredPostalAddress('Dorfstrasse', '17', '9911', 'Musterwald'),
            new PostalAccount('60-9-9')
        );
        $transaction->setRemittanceInformation("Test Remittance");
        $payment->addTransaction($transaction);

        $this->buildCommonPayments($message);

        return $message;
    }

    /**
     * @return CustomerCreditTransfer
     */
    protected function buildMessageSPS2022()
    {
        $message = new CustomerCreditTransfer('message-000', 'InnoMuster AG', CustomerCreditTransfer::SPS_2022, 'softwareName', 'version', 'manufacturerName');
        $this->buildCommonPayments($message);

        return $message;
    }

    /**
     * @param CustomerCreditTransfer $message
     * @return CustomerCreditTransfer
     */
    protected function buildCommonPayments(CustomerCreditTransfer $message)
    {
        // Test payment-000 : BankCreditTransfer with new char allowed by SPS-2022 "€ȘșȚț"
        $payment = new PaymentInformation(
            'payment-000',
            'InnoMuster AG',
            new BIC('ZKBKCHZZ80A'),
            new IBAN('CH6600700110000204481')
        );
        $message->addPayment($payment);

        $transaction = new BankCreditTransfer(
            'instr-000',
            'e2e-000',
            new Money\CHF(130000), // CHF 1300.00
            'Muster Transport AG',
            new StructuredPostalAddress('Wiesenweg', '14b', '8058', 'Zürich-Flughafen'),
            new IBAN('CH51 0022 5225 9529 1301 C'),
            new BIC('UBSWCHZH80A')
        );
        $transaction->setRemittanceInformation("Test Remittance");
        $payment->addTransaction($transaction);

        $iban = new IBAN('CH51 0022 5225 9529 1301 C');
        $transaction = new BankCreditTransfer(
            'instr-001',
            'e2e-001',
            new Money\CHF(30000), // CHF 300.00
            'Muster Transport AG',
            null,
            $iban,
            IID::fromIBAN($iban)
        );
        $transaction->setRemittanceInformation("Test Remittance");
        $transaction->setPurpose(new PurposeCode('AIRB'));
        $payment->addTransaction($transaction);

        $transaction = new BankCreditTransfer(
            'instr-002',
            'e2e-002',
            new Money\CHF(30000), // CHF 300.00
            $message->getSpsVersion() === CustomerCreditTransfer::SPS_2021 ? 'InnoMuster AG' : 'New SPS-2022 chars €ȘșȚț',
            null,
            $iban,
            IID::fromIBAN($iban)
        );
        $transaction->setRemittanceInformation("Test Remittance");
        $transaction->setPurpose(new PurposeCode('AIRB'));
        $payment->addTransaction($transaction);

        // Test payment-001 : BankCreditTransfer with category purpose SALA
        $payment = new PaymentInformation(
            'payment-001',
            'InnoMuster AG',
            new BIC('ZKBKCHZZ80A'),
            new IBAN('CH6600700110000204481')
        );
        $payment->setCategoryPurpose(new CategoryPurposeCode('SALA'));
        $message->addPayment($payment);

        $transaction = new BankCreditTransfer(
            'instr-003',
            'e2e-003',
            new Money\CHF(130000), // CHF 1300.00
            'Muster Transport AG',
            new StructuredPostalAddress('Wiesenweg', '14b', '8058', 'Zürich-Flughafen'),
            new IBAN('CH51 0022 5225 9529 1301 C'),
            new BIC('UBSWCHZH80A')
        );
        $transaction->setRemittanceInformation("Test Remittance");
        $payment->addTransaction($transaction);

        // Test payment-010 : SEPACreditTransfer and ForeignCreditTransfer
        $payment = new PaymentInformation(
            'payment-010',
            'InnoMuster AG',
            new BIC('POFICHBEXXX'),
            new IBAN('CH6309000000250097798')
        );
        $message->addPayment($payment);

        $transaction = new SEPACreditTransfer(
            'instr-010',
            'e2e-010',
            new Money\EUR(70000), // EUR 700.00
            'Muster Immo AG',
            new UnstructuredPostalAddress('Musterstraße 35', '80333 München', 'DE'),
            new IBAN('DE89 3704 0044 0532 0130 00'),
            new BIC('COBADEFFXXX')
        );
        $payment->addTransaction($transaction);

        $transaction = new ForeignCreditTransfer(
            'instr-011',
            'e2e-011',
            new Money\GBP(6500), // GBP 65.00
            'United Development Ltd',
            new UnstructuredPostalAddress('George Street', 'BA1 2FJ Bath', 'GB'),
            new IBAN('GB29 NWBK 6016 1331 9268 19'),
            new BIC('NWBKGB2L')
        );
        $payment->addTransaction($transaction);

        $transaction = new ForeignCreditTransfer(
            'instr-012',
            'e2e-012',
            new Money\KWD(300001), // KWD 300.001
            'United Development Kuwait',
            new UnstructuredPostalAddress('P.O. Box 23954 Safat', '13100 Kuwait', 'KW'),
            new IBAN('BR97 0036 0305 0000 1000 9795 493P 1'),
            new FinancialInstitutionAddress('Caixa Economica Federal', new UnstructuredPostalAddress('Rua Sao Valentim, 620', '03446-040 Sao Paulo-SP', 'BR'))
        );
        $payment->addTransaction($transaction);

        $transaction = new ForeignCreditTransfer(
            'instr-013',
            'e2e-013',
            new Money\GBP(4500), // GBP 45.00
            'United Development Belgium SA/NV',
            new UnstructuredPostalAddress('Oostjachtpark 187', '6743 Buzenol', 'BE'),
            new GeneralAccount('123-4567890-78'),
            new FinancialInstitutionAddress('Belfius Bank', new UnstructuredPostalAddress('Pachecolaan 44', '1000 Brussel', 'BE'))
        );
        $transaction->setIntermediaryAgent(new BIC('SWHQBEBB'));
        $payment->addTransaction($transaction);

        // Test payment-020 : SEPACreditTransfer with SEPAPaymentInformation
        $payment = new SEPAPaymentInformation(
            'payment-020',
            'InnoMuster AG',
            new BIC('POFICHBEXXX'),
            new IBAN('CH6309000000250097798')
        );
        $message->addPayment($payment);

        $transaction = new SEPACreditTransfer(
            'instr-020',
            'e2e-020',
            new Money\EUR(10000), // EUR 100.00
            'Bau Muster AG',
            new UnstructuredPostalAddress('Musterallee 11', '10115 Berlin', 'DE'),
            new IBAN('DE22 2665 0001 9311 6826 12'),
            new BIC('NOLADE21EMS')
        );
        $payment->addTransaction($transaction);

        // Test payment-030 : BankCreditTransferWithQRR and BankCreditTransferWithCreditorReference
        $payment = new PaymentInformation(
            'payment-030',
            'InnoMuster AG',
            new BIC('ZKBKCHZZ80A'),
            new IBAN('CH6600700110000204481')
        );
        $message->addPayment($payment);

        $qrIban = new IBAN('CH44 3199 9123 0008 8901 2');
        $transaction = new BankCreditTransferWithQRR(
            'instr-030',
            'e2e-030',
            new Money\CHF(130000), // CHF 1300.00
            'Muster Transport AG',
            new StructuredPostalAddress('Wiesenweg', '14b', '8058', 'Zürich-Flughafen'),
            $qrIban,
            IID::fromIBAN($qrIban),
            '210000000003139471430009017'
        );
        $transaction->setRemittanceInformation("Test Remittance");
        $payment->addTransaction($transaction);

        $transaction = new BankCreditTransferWithCreditorReference(
            'instr-031',
            'e2e-031',
            new Money\CHF(130000), // CHF 1300.00
            'Muster Transport AG',
            new StructuredPostalAddress('Wiesenweg', '14b', '8058', 'Zürich-Flughafen'),
            $iban,
            IID::fromIBAN($iban),
            'RF 72 0191 2301 0040 5JSH 0438'
        );
        $transaction->setRemittanceInformation("Test Remittance");
        $payment->addTransaction($transaction);

        return $message;
    }

    public function testGroupHeader()
    {
        $message = $this->buildMessageSPS2021();
        $xml = $message->asXml();

        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('pain001', $message->getSchemaName());

        $nbOfTxs = $xpath->evaluate('string(//pain001:GrpHdr/pain001:NbOfTxs)');
        self::assertEquals('16', $nbOfTxs);

        $ctrlSum = $xpath->evaluate('string(//pain001:GrpHdr/pain001:CtrlSum)');
        self::assertEquals('8410.001', $ctrlSum);

        $message = $this->buildMessageSPS2022();
        $xml = $message->asXml();

        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('pain001', $message->getSchemaName());

        $nbOfTxs = $xpath->evaluate('string(//pain001:GrpHdr/pain001:NbOfTxs)');
        self::assertEquals('11', $nbOfTxs);

        $ctrlSum = $xpath->evaluate('string(//pain001:GrpHdr/pain001:CtrlSum)');
        self::assertEquals('7010.001', $ctrlSum);
    }

    public function testGetPaymentCount()
    {
        self::assertSame(8, $this->buildMessageSPS2021()->getPaymentCount());
        self::assertSame(5, $this->buildMessageSPS2022()->getPaymentCount());
    }

    /**
     * @return CustomerCreditTransfer[][]
     */
    public function messageProvider()
    {
        return [
            [$this->buildMessageSPS2021()],
            [$this->buildMessageSPS2022()]
        ];
    }

    /**
     * @dataProvider messageProvider
     */
    public function testSchemaValidation($message)
    {
        $xml = $message->asXml();
        $schemaPath = __DIR__.'/../../../../'.$message->getSchemaLocation();

        $doc = new DOMDocument();
        $doc->loadXML($xml);

        libxml_use_internal_errors(true);
        try {
            $valid = $doc->schemaValidate($schemaPath);
        } catch (Exception $e) {
            $valid = false;
        }
        if ($valid === false) {
            var_dump(libxml_get_errors());
            foreach (libxml_get_errors() as $error) {
                $this->fail($error->message);
            }
        }
        self::assertTrue($valid);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
    }

    public function testIS1PaymentException()
    {
        $message = $this->buildMessageSPS2022();

        $payment = new PaymentInformation(
            'payment-100',
            'InnoMuster AG',
            new BIC('ZKBKCHZZ80A'),
            new IBAN('CH6600700110000204481')
        );
        $message->addPayment($payment);

        $transaction = new IS1CreditTransfer(
            'instr-101',
            'e2e-101',
            new Money\CHF(30000), // CHF 300.00
            'Finanzverwaltung Stadt Musterhausen',
            new StructuredPostalAddress('Altstadt', '1a', '4998', 'Muserhausen'),
            new PostalAccount('80-5928-4')
        );
        $payment->addTransaction($transaction);

        $this->expectExceptionMessage("IS 2-stage payments can only be created until SPS 2021 version");
        $message->asXml();
    }

    public function testIS2PaymentException()
    {
        $message = $this->buildMessageSPS2022();

        $payment = new PaymentInformation(
            'payment-100',
            'InnoMuster AG',
            new BIC('ZKBKCHZZ80A'),
            new IBAN('CH6600700110000204481')
        );
        $message->addPayment($payment);

        $transaction = new IS2CreditTransfer(
            'instr-102',
            'e2e-102',
            new Money\CHF(20000), // CHF 200.00
            'Druckerei Muster GmbH',
            new StructuredPostalAddress('Gartenstrasse', '61', '3000', 'Bern'),
            new IBAN('CH03 0900 0000 3054 1118 8'),
            'Musterbank AG',
            new PostalAccount('80-151-4')
        );
        $transaction->setRemittanceInformation("Test Remittance");
        $payment->addTransaction($transaction);

        $this->expectExceptionMessage("IS 2-stage payments can only be created until SPS 2021 version");
        $message->asXml();
    }

    public function testISRPaymentException()
    {
        $message = $this->buildMessageSPS2022();

        $payment = new PaymentInformation(
            'payment-100',
            'InnoMuster AG',
            new BIC('ZKBKCHZZ80A'),
            new IBAN('CH6600700110000204481')
        );
        $message->addPayment($payment);

        $transaction = new ISRCreditTransfer(
            'instr-110',
            'e2e-110',
            new Money\CHF(20000), // CHF 200.00
            new ISRParticipant('01-1439-8'),
            '210000000003139471430009017'
        );
        $payment->addTransaction($transaction);

        $this->expectExceptionMessage("ISR payments can only be created until SPS 2021 version");
        $message->asXml();
    }
}

