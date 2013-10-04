<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('SclWhois', __DIR__.'/../vendor/sclinternet/scl-whois/src');
$loader->add('SclSocket', __DIR__.'/../vendor/sclinternet/scl-socket/src');
$loader->add('JSomerstone', __DIR__.'/../vendor/jsomerstone/dayswithout/src');

// intl
if (!function_exists('intl_get_error_code')) {
    require_once __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs/functions.php';
}

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
