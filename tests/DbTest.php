<?php

use framework\db\Db;

class DbTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        Db::_init([
            'dsn' => 'mysql:dbname=test;host=127.0.0.1;charset=utf8',
            'username' => 'root',
            'password' => 'password',
        ]);

        Db::query('DROP TABLE IF EXISTS `user`');
        Db::query('
            CREATE TABLE `user` (
              `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `username` varchar(32) NOT NULL,
              `password` varchar(255) NOT NULL,
              `create_date` datetime
            ) ENGINE=\'InnoDB\';
        ');
        Db::insert('user', ['username' => 'johndoe', 'password' => md5('password'), 'create_date' => date('Y-m-d H:i:s')]);
    }

    protected function setUp()
    {
        Db::update('DELETE FROM user WHERE username IN (?, ?)', 'test', 'foo');
    }

    public function testGetRow()
    {
        self::assertNull(Db::getRow('SELECT * FROM user WHERE id = ?', 42));
        self::assertTrue(is_array(Db::getRow('SELECT * FROM user WHERE username = ?', 'johndoe')));
    }

    public function testGetValue()
    {
        self::assertTrue(Db::getValue('SELECT username FROM user WHERE username = ?', 'johndoe') === 'johndoe');
        self::assertTrue(Db::getValue('SELECT username FROM user WHERE id = 0') === null);

        self::assertTrue(Db::getValue('SELECT COUNT(1) FROM user WHERE username = ?', 'johndoe') === 1);
        self::assertTrue(Db::getValue('SELECT COUNT(1) FROM user WHERE id = 0') === 0);
    }

    public function testTransaction()
    {
        try {
            Db::begin();
            $this->insertTestUser(true);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
        }

        self::assertNull(Db::getRow('SELECT * FROM user WHERE username = ?', 'test'));

        try {
            Db::transaction(function () {
                $this->insertTestUser(true);
            });
        } catch (\Exception $ignore) {
        }

        self::assertNull(Db::getRow('SELECT * FROM user WHERE username = ?', 'test'));

        try {
            Db::transaction(function () {
                $this->insertTestUser(false);
            });
        } catch (\Exception $ignore) {
        }

        self::assertNotNull(Db::getRow('SELECT * FROM user WHERE username = ?', 'test'));
    }

    public function testMultipleTransactions()
    {
        Db::begin();
        Db::begin();

        $this->insertTestUser(false);

        Db::commit();
        Db::commit();

        self::assertNotNull(Db::getRow('SELECT * FROM user WHERE username = ?', 'test'));
        self::assertNotNull(Db::getRow('SELECT * FROM user WHERE username = ?', 'foo'));
    }

    private function insertTestUser($withError)
    {
        Db::insert('user', ['username' => 'test', 'password' => md5('password'), 'create_date' => date('Y-m-d H:i:s')]);
        if ($withError) {
            Db::insert('INSERT INTO user SET username = ?, password = ?', 'foo');
        } else {
            Db::insert('INSERT INTO user SET username = ?, password = ?', 'foo', 'password');
        }
    }
}
