<?php

use PHPUnit\Framework\TestCase;
use framework\web\Request;

class RequestTest extends TestCase
{
    public function testGetParameters()
    {
        $_REQUEST['id'] = '42';
        $_REQUEST['action'] = 'delete';

        list($id, $action) = Request::getParameters('id', 'action');

        static::assertEquals($id, '42');
        static::assertEquals($action, 'delete');

        list($id, $param) = Request::getParameters('id', 'param');

        static::assertEquals($id, '42');
        static::assertNull($param);
    }

    public function testGetAttributes()
    {
        static::assertTrue(is_array(Request::getAttributes()));
    }
}
