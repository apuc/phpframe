<?php

namespace framework\cache;

interface Storage
{
    public function get($key);

    public function put($key, $value, $expiration);

    public function remove($key);

    public function flush();
}
