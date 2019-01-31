<?php

/*
Restrict usage to PHP7, defines the autoload function and sets user's settings.
*/

declare(strict_types = 1);
if (phpversion() < 7) {
    throw new Exception(
        "You need PHP 7+ to run this script. Please, upgrade your PHP version."
    );
}

$autoload = function($class) {
    $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class);
    require_once $file . '.php';
};
spl_autoload_register($autoload);

$settings = parse_ini_file('settings.ini');
