<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

$loader = require __DIR__.'/../vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

// dirty hack to autoload vich annotations
new Vich\Uploadable();
new Vich\UploadableField(array(
    'mapping'          => 'foo',
    'fileNameProperty' => 'bar',
));

return $loader;
