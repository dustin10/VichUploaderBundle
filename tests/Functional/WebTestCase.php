<?php

namespace Vich\UploaderBundle\Tests\Functional;

use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Metadata\MetadataReader;

abstract class WebTestCase extends BaseWebTestCase
{
    protected static function getKernelClass(): string
    {
        require_once __DIR__.'/../Fixtures/App/app/AppKernel.php';

        return 'AppKernel';
    }

    protected function getUploadedFile(KernelBrowser $client, string $name, string $mimeType = 'image/png'): UploadedFile
    {
        return new UploadedFile(
            $this->getImagesDir($client).\DIRECTORY_SEPARATOR.$name,
            $name,
            $mimeType,
        );
    }

    protected function getUploadsDir(KernelBrowser $client): string
    {
        return $client->getKernel()->getCacheDir().'/images';
    }

    protected function getImagesDir(KernelBrowser $client): string
    {
        return $client->getKernel()->getProjectDir().'/app/Resources/images';
    }

    protected static function getKernelContainer(KernelBrowser $client): ContainerInterface
    {
        return $client->getKernel()->getContainer();
    }

    protected function loadFixtures(KernelBrowser $client): void
    {
        $container = self::getKernelContainer($client);
        $registry = $container->get('doctrine');
        if ($registry instanceof ManagerRegistry) {
            $om = $registry->getManager();
        } else {
            $om = $registry->getEntityManager();
        }

        $connection = $om->getConnection();
        $params = $connection->getParams();
        $name = $params['path'] ?? $params['dbname'] ?? false;

        if (!$name) {
            throw new \InvalidArgumentException("Connection does not contain a 'path' or 'dbname' parameter and cannot be dropped.");
        }

        $metadata = $om->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($om);
        $schemaTool->dropDatabase();
        if (!empty($metadata)) {
            $schemaTool->createSchema($metadata);
        }
    }

    protected function mockMetadataReader(): MetadataReader|MockObject
    {
        return $this->createMock(MetadataReader::class);
    }
}
