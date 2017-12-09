<?php

namespace Vich\UploaderBundle\Tests\Functional;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class WebTestCase extends BaseWebTestCase
{
    protected static function getKernelClass()
    {
        require_once __DIR__.'/../Fixtures/App/app/AppKernel.php';

        return 'AppKernel';
    }

    protected function getUploadedFile($client, $name, $mimeType = 'image/png')
    {
        return new UploadedFile(
            $this->getImagesDir($client).DIRECTORY_SEPARATOR.$name,
            $name,
            $mimeType,
            123
        );
    }

    protected function getUploadsDir($client)
    {
        return $client->getKernel()->getCacheDir().'/images';
    }

    protected function getImagesDir($client)
    {
        return $client->getKernel()->getRootDir().'/Resources/images';
    }

    protected function getContainer($client)
    {
        return $client->getKernel()->getContainer();
    }

    protected function loadFixtures($client)
    {
        $container = $this->getContainer($client);
        $registry = $container->get('doctrine');
        if ($registry instanceof ManagerRegistry) {
            $om = $registry->getManager();
        } else {
            $om = $registry->getEntityManager();
        }

        $cacheDriver = $om->getMetadataFactory()->getCacheDriver();
        if ($cacheDriver) {
            $cacheDriver->deleteAll();
        }

        $connection = $om->getConnection();
        $params = $connection->getParams();
        $name = isset($params['path']) ? $params['path'] : (isset($params['dbname']) ? $params['dbname'] : false);

        if (!$name) {
            throw new \InvalidArgumentException("Connection does not contain a 'path' or 'dbname' parameter and cannot be dropped.");
        }

        $metadatas = $om->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($om);
        $schemaTool->dropDatabase($name);
        if (!empty($metadatas)) {
            $schemaTool->createSchema($metadatas);
        }
    }
}
