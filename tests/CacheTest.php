<?php

use framework\App;
use framework\cache;
use framework\db\Db;

class CacheTest extends PHPUnit_Framework_TestCase
{
    const KEY1 = 'key1';
    const KEY2 = 'key2';

    public static function setUpBeforeClass()
    {
        Db::_init([
            'dsn' => 'mysql:dbname=test;host=127.0.0.1;charset=utf8',
            'username' => 'root',
            'password' => 'password',
        ]);

        App::$config = [
            'cache' => [
                'storage' => \framework\cache\DbStorage::class
            ]
        ];

        Db::query('CREATE TABLE IF NOT EXISTS cache (id VARCHAR(255) PRIMARY KEY NOT NULL, value BLOB, expiration INTEGER)');
        Db::update('DELETE FROM cache');
    }

    public static function tearDownAfterClass()
    {
        Db::query('DROP TABLE IF EXISTS cache');
    }

    public function testCache()
    {
        self::assertNull(Cache::get(self::KEY1));

        $value1 = [1, 2, 4];
        $value2 = ['a', 'b', 'c'];

        Cache::put(self::KEY1, $value1, 300);
        Cache::put(self::KEY2, $value2, 300);

        self::assertEquals(Cache::get(self::KEY1), $value1);
        self::assertEquals(Cache::get(self::KEY2), $value2);

        /*Cache::put(self::KEY1, $value1, 1);
        sleep(1);
        self::assertEquals(Cache::get(self::KEY1), null);*/

        Cache::remove(self::KEY2);
        self::assertEquals(Cache::get(self::KEY2), null);

        $value = Cache::cache(self::KEY2, 300, function () use ($value2) {
            return $value2;
        });
        self::assertEquals($value, $value2);
        self::assertEquals(Cache::get(self::KEY2), $value2);
    }
}
