<?php

/*
* Restrict usage to PHP7, parse user's settings.
*/

declare(strict_types = 1);

if (phpversion() < 7.2) {
    throw new Exception(
        "You need PHP 7.2+ to run this script. Please, upgrade your PHP version."
    );
}

$settings = parse_ini_file('settings.ini');
