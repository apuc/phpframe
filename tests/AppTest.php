<?php

use framework\App;
use framework\Logger;
use framework\Validator;
use PHPUnit\Framework\Assert;

class AppTest extends PHPUnit_Framework_TestCase
{
    private $routes;

    protected function setUp()
    {
        $this->routes = [
            ['^/$', 'Home'],
            ['^/charts', 'Charts'],
        ];

        App::$config = [
            'name' => 'default',
            'logging' => [
                'level' => Logger::ERROR,
                'deeper' => [
                    'much_deeper' => 42
                ]
            ]
        ];
    }

    public function testGetConfigValue()
    {
        self::assertEquals(App::getConfigValue('name'), 'default');
        self::assertEquals(App::getConfigValue(['logging', 'level']), Logger::ERROR);
        self::assertEquals(App::getConfigValue(['logging', 'deeper', 'much_deeper']), 42);
        self::assertEquals(App::getConfigValue(['logging', 'deeper', 'much_deeper_much'], 21), 21);
        self::assertEquals(App::getConfigValue(['logging_1', 'deeper', 'much_deeper_1'], 21), 21);
    }

    /**
     * @dataProvider routesProvider
     * @param $input
     * @param $expected
     */
    public function testRoute($input, $expected)
    {
        $result = App::route($input, $this->routes);
        self::assertTrue(is_array($result) && count($result) === 2);
        self::assertEquals($result[0], $expected);
    }

    public function routesProvider(): array
    {
        return [
            ['/', 'Home'],
            ['/charts', 'Charts'],
            ['/charts/42', 'Charts']
        ];
    }

    public function testAliases()
    {
        App::setAlias('@foo', '/path/to/foo');
        static::assertSame(App::getPath('@foo/index.php'), '/path/to/foo/index.php');

        static::assertSame(App::getPath('/path/to/foo/another.php'), '/path/to/foo/another.php');

        $this->expectException(InvalidArgumentException::class);
        App::getPath('@xyz/xyz.php');
    }

    public function testGet(): void
    {
        $service = App::get(Validator::class);
        Assert::assertNotNull($service, 'Not null');
        Assert::assertInstanceOf(Validator::class, $service, 'Instance of TestingService class');

        $serviceTwo = App::get(Validator::class);
        Assert::assertSame($service, $serviceTwo, 'One instance');
    }
}
