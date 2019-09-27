<?php

use PHPUnit\Framework\TestCase;
use framework\Model;
use framework\Validator;

class ModelTest extends TestCase
{
    public function testModel()
    {
        $model = new UserModel();

        $_SERVER['REQUEST_METHOD'] = 'GET';

        self::assertFalse($model->loadFromPost());

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['username'] = ' root ';
        $_POST['password'] = ' password ';
        $_POST['first_name'] = 'John';
        $_POST['last_name'] = 'Doe';

        $items = [1, 2, 3, 5, 8];
        $_POST['items'] = $items;

        static::assertTrue($model->loadFromPost());

        static::assertEquals($model->username, 'root');
        static::assertEquals($model->password, 'password');
        static::assertEquals($model->firstName, 'John');
        static::assertEquals($model->getLastName(), 'Doe');
        static::assertEquals($model->items, $items);

        static::assertTrue($model->validate());

        $_POST['username'] = '';
        static::assertFalse($model->loadFromPostAndValidate());

        $errors = $model->getErrors();
        static::assertTrue(is_array($errors));
        static::assertCount(1, $errors);
        static::assertArrayHasKey('username', $errors);
        static::assertSame($errors['username'], 'Fill a username');
    }

    public function testBooleanField()
    {
        $model = new UserModel();
        $model->load(['is_active' => true]);
        static::assertSame($model->isActive, true);

        print json_encode($model);
    }
}

class UserModel extends Model
{
    public $username;
    public $password;
    public $firstName;
    private $lastName;
    public $items;
    public $isActive = false;

    protected function getValidator($scenario)
    {
        $validator = new Validator();
        $validator->add('username')->required('Fill a username');

        return $validator;
    }

    public function getLastName()
    {
        return $this->lastName;
    }
}
