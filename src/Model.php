<?php

namespace framework;

abstract class Model
{
    private static $reflectionClassMap = [];

    private $errors;

    /**
     * @param $data
     * @throws \ReflectionException
     */
    public function load($data, $fromSnakeCase = true)
    {
        $reflection = $this->getReflectionClass(get_class($this));
        foreach ($reflection->getProperties() as $property) {
            if (!$property->isStatic()) {
                $name = $fromSnakeCase ? Str::toSnakeCase($property->getName()) : $property->getName();
                if (array_key_exists($name, $data)) {
                    $value = is_string($data[$name]) ? trim($data[$name]) : $data[$name];
                    if (!is_string($value) || $value !== '') {
                        $property->setValue($this, $value);
                    }
                }
            }
        }
    }

    /**
     * @return bool
     * @throws \ReflectionException
     */
    public function loadFromPost()
    {
        if (is_array($_POST) && App::$request->isMethodPost()) {
            $this->load($_POST);
            return true;
        }

        return false;
    }

    public function validate($scenario = null)
    {
        $validator = $this->getValidator($scenario);
        if ($validator === null) {
            return true;
        }

        if ($validator->validate()) {
            return true;
        }

        $this->errors = $validator->getErrors();
        return false;
    }

    /**
     * @param null $scenario
     * @return bool
     * @throws \ReflectionException
     */
    public function loadFromPostAndValidate($scenario = null)
    {
        return $this->loadFromPost() && $this->validate($scenario);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param $scenario
     * @return Validator|null
     */
    protected function getValidator($scenario)
    {
        return null;
    }

    protected function getReflectionClass($className): \ReflectionClass
    {
        if (!isset(self::$reflectionClassMap[$className])) {
            self::$reflectionClassMap[$className] = new \ReflectionClass($className);
        }
        return self::$reflectionClassMap[$className];
    }
}
