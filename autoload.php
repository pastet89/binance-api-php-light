<?php

$autoload = function ($class) {
    $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class);
    require_once $file . '.php';
};

spl_autoload_register($autoload);
