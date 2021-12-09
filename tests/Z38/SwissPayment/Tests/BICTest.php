<?php

namespace Z38\SwissPayment\Tests;

use InvalidArgumentException;
use Z38\SwissPayment\BIC;

/**
 * @coversDefaultClass \Z38\SwissPayment\BIC
 */
class BICTest extends TestCase
{
    /**
     * @dataProvider validSamples
     * @covers \Z38\SwissPayment\BIC::__construct
     */
    public function testValid($bic)
    {
        $this->check($bic, true);
    }

    /**
     * @covers \Z38\SwissPayment\BIC::__construct
     */
    public function testInvalidLength()
    {
        $this->check('AABAFI22F', false);
        $this->check('HANDFIHH00', false);
    }

    /**
     * @covers \Z38\SwissPayment\BIC::__construct
     */
    public function testInvalidChars()
    {
        $this->check('HAND-FIHH', false);
        $this->check('HAND FIHH', false);
    }

    /**
     * @dataProvider validSamples
     * @covers \Z38\SwissPayment\BIC::format
     */
    public function testFormat($bic)
    {
        $instance = new BIC($bic);
        self::assertEquals($bic, $instance->format());
    }

    /**
     * @return string[][]
     */
    public function validSamples()
    {
        return [
            ['AABAFI22'],
            ['HANDFIHH'],
            ['DEUTDEFF500'],
        ];
    }

    /**
     * @param $iban
     * @param $valid
     * @return void
     */
    protected function check($iban, $valid)
    {
        $exception = false;
        try {
            new BIC($iban);
        } catch (InvalidArgumentException $e) {
            $exception = true;
        }
        self::assertTrue($exception != $valid);
    }
}
