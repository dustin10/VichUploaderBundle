<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

error_reporting(E_ALL & ~E_USER_DEPRECATED);

$loader = require __DIR__.'/../vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
