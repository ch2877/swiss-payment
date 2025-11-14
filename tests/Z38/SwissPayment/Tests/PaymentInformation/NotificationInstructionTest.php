<?php

namespace Z38\SwissPayment\Tests\PaymentInformation;

use DOMDocument;
use InvalidArgumentException;
use Z38\SwissPayment\PaymentInformation\NotificationInstruction;
use Z38\SwissPayment\Tests\TestCase;

/**
 * @coversDefaultClass \Z38\SwissPayment\PaymentInformation\NotificationInstruction
 */
class NotificationInstructionTest extends TestCase
{
    /**
     * @dataProvider validSamples
     * @covers ::__construct
     */
    public function testValid($instruction)
    {
        self::assertInstanceOf('Z38\SwissPayment\PaymentInformation\NotificationInstruction', new NotificationInstruction($instruction));
    }

    /**
     * @return string[][]
     */
    public function validSamples()
    {
        return [
            ['NOA'], // No Advice
            ['SIA'], // Single Advice
            ['CND'], // Collective Advice No Details
            ['CWD'], // Collective Advice With Details
        ];
    }

    /**
     * @dataProvider invalidSamples
     * @covers ::__construct
     */
    public function testInvalid($instruction)
    {
        $this->expectException(InvalidArgumentException::class);
        new NotificationInstruction($instruction);
    }

    /**
     * @return string[][]
     */
    public function invalidSamples()
    {
        return [
            [''],
            ['noa'],
            ['something-else'],
            [' CWD'],
            ['CWD '],
        ];
    }

    /**
     * @covers ::asDom
     */
    public function testAsDom()
    {
        $doc = new DOMDocument();
        $iid = new NotificationInstruction('CWD');

        $xml = $iid->asDom($doc);

        self::assertSame('Prtry', $xml->nodeName);
        self::assertSame('CWD', $xml->textContent);
    }

    /**
     * @dataProvider invalidCheckAgainstBatchBooking
     * @covers ::checkAgainstBatchBooking
     */
    public function testInvalidCheckAgainstBatchBooking($instruction, $batchBooking)
    {
        $notificationInstruction = new NotificationInstruction($instruction);
        self::assertFalse($notificationInstruction->checkAgainstBatchBooking($batchBooking));
    }

    /**
     * @dataProvider validCheckAgainstBatchBooking
     * @covers ::checkAgainstBatchBooking
     */
    public function testCheckAgainstBatchBooking($instruction, $batchBooking)
    {
        $notificationInstruction = new NotificationInstruction($instruction);
        self::assertTrue($notificationInstruction->checkAgainstBatchBooking($batchBooking));
    }

    /**
     * @return array[]
     */
    public function invalidCheckAgainstBatchBooking()
    {
        return [
            ['CWD', false],
            ['CND', false],
            ['SIA', true],
        ];
    }

    /**
     * @return array[]
     */
    public function validCheckAgainstBatchBooking()
    {
        return [
            ['NOA', false],
            ['SIA', false],
            ['NOA', true],
            ['CND', true],
            ['CWD', true],
        ];
    }

}
