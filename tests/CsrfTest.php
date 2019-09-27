<?php

use PHPUnit\Framework\TestCase;
use framework\Csrf;

class CsrfTest extends TestCase
{
    public function test()
    {
        static::assertFalse(Csrf::validate());

        $token = Csrf::getToken();
        static::assertNotEmpty($token);
        static::assertTrue(strlen($token) >= 20);

        static::assertTrue(Csrf::validate($token));

        static::assertSame(
            Csrf::getHiddenInputString(),
            '<input type="hidden" name="_csrf_token" value="' . $token . '"/>'
        );

        static::assertSame(
            Csrf::getQueryString(),
            '_csrf_token=' . $token
        );
    }
}
