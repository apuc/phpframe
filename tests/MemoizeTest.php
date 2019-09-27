<?php

use framework\Memoize;

class MemoizeTest extends PHPUnit_Framework_TestCase
{
    const ANSWER = 42;

    private $callsCount = 0;

    public function test()
    {
        static::assertEquals($this->callsCount, 0);

        static::assertEquals($this->getValue(), self::ANSWER);
        static::assertEquals($this->callsCount, 1);

        static::assertEquals($this->getValue(), self::ANSWER);
        static::assertEquals($this->getValue(), self::ANSWER);
        static::assertEquals($this->callsCount, 1);
    }

    private function getValue()
    {
        return Memoize::memoize('value', function () {
            $this->callsCount++;
            return self::ANSWER;
        });
    }
}
