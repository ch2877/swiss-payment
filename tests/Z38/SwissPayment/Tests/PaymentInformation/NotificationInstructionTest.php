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
}
