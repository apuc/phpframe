<?php

use framework\Str;

class StrTest extends PHPUnit_Framework_TestCase
{
    public function testStartsWith()
    {
        self::assertTrue(Str::startsWith('12345', ''));
        self::assertTrue(Str::startsWith('12345', '123'));
        self::assertTrue(Str::startsWith('12345', '12345'));

        self::assertFalse(Str::startsWith('12345', '23'));
        self::assertFalse(Str::startsWith('', '23'));
    }

    public function testEndsWith()
    {
        self::assertTrue(Str::endsWith('12345', ''));
        self::assertTrue(Str::endsWith('12345', '345'));
        self::assertTrue(Str::endsWith('12345', '12345'));

        self::assertFalse(Str::startsWith('12345', '34'));
        self::assertFalse(Str::startsWith('', '34'));
    }

    public function testToSnakeCase()
    {
        self::assertEquals(Str::toSnakeCase('CamelCaseToSnakeCase'), 'camel_case_to_snake_case');
        self::assertEquals(Str::toSnakeCase('CamelCaseToSnakeCase', '-'), 'camel-case-to-snake-case');
        self::assertEquals(Str::toSnakeCase('camelCaseToSnakeCase'), 'camel_case_to_snake_case');
        self::assertEquals(Str::toSnakeCase('HTML'), 'html');
        self::assertEquals(Str::toSnakeCase('camel_case_to_snake_case'), 'camel_case_to_snake_case');
    }

    public function testToCamelCase()
    {
        self::assertEquals(Str::toCamelCase('camel_case_to_snake_case'), 'camelCaseToSnakeCase');
        self::assertEquals(Str::toCamelCase('camel_case_to_snake_case', true), 'CamelCaseToSnakeCase');

        self::assertEquals(Str::toCamelCase('camelCaseToSnakeCase'), 'camelCaseToSnakeCase');
        self::assertEquals(Str::toCamelCase('CamelCaseToSnakeCase'), 'CamelCaseToSnakeCase');
    }

    public function testZeroPad()
    {
        self::assertEquals(Str::zeroPad(42, 6), '000042');
        self::assertEquals(Str::zeroPad(42, 1), '42');
        self::assertEquals(Str::zeroPad(42, 2), '42');
    }
}
