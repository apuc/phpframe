<?php

namespace framework\web;

abstract class Controller
{
    protected function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }

    public function handle()
    {
        return null;
    }
}
