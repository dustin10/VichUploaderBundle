<?php

namespace Vich\UploaderBundle\Tests\Functional;

use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\TestBundle\Entity\Image;

final class DoctrineFlushTest extends WebTestCase
{
    /** @var list<string> */
    private array $createdFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $file) {
            if (\is_file($file)) {
                \unlink($file);
            }
        }

        parent::tearDown();
    }

    #[Test]
    public function fileIsUploadedWhenNoOtherMappedFieldChanges(): void
    {
        $client = self::createClient();
        $this->loadFixtures($client);
        $em = $this->getEntityManager($client);
        $image = new Image();
        $image->setTitle('Test image');
        $image->setImageFile($this->createUploadedFile($client, 'flush_first.png'));
        $em->persist($image);
        $em->flush();
        $updatedAt = new \ReflectionProperty(Image::class, 'updatedAt');
        $previousUpdatedAt = $updatedAt->getValue($image);
        $image->setImageFile($this->createUploadedFile($client, 'flush_second.png'));
        $updatedAt->setValue($image, $previousUpdatedAt);

        $em->flush();
        $em->clear();
        $reloaded = $em->find(Image::class, $image->getId());

        self::assertSame('flush_second.png', $reloaded?->getImageName());
        self::assertFileExists($this->getUploadsDir($client).'/flush_second.png');
        self::assertFileDoesNotExist($this->getUploadsDir($client).'/flush_first.png');
    }

    #[Test]
    public function fileSetBetweenPersistAndFlushIsUploaded(): void
    {
        $client = self::createClient();
        $this->loadFixtures($client);
        $em = $this->getEntityManager($client);
        $image = new Image();
        $image->setTitle('Test image');
        $em->persist($image);
        $image->setImageFile($this->createUploadedFile($client, 'flush_after_persist.png'));

        $em->flush();
        $em->clear();
        $reloaded = $em->find(Image::class, $image->getId());

        self::assertSame('flush_after_persist.png', $reloaded?->getImageName());
        self::assertFileExists($this->getUploadsDir($client).'/flush_after_persist.png');
    }

    #[Test]
    public function newEntityReusingAFileNameDoesNotRemoveThatFile(): void
    {
        $client = self::createClient();
        $this->loadFixtures($client);
        $em = $this->getEntityManager($client);
        $original = new Image();
        $original->setTitle('Original');
        $original->setImageFile($this->createUploadedFile($client, 'flush_original.png'));
        $em->persist($original);
        $em->flush();
        $copy = new Image();
        $copy->setTitle('Copy');
        $copy->setImageName($original->getImageName());
        $em->persist($copy);
        $copy->setImageFile($this->createUploadedFile($client, 'flush_copy.png'));

        $em->flush();

        self::assertFileExists($this->getUploadsDir($client).'/flush_original.png');
        self::assertFileExists($this->getUploadsDir($client).'/flush_copy.png');
    }

    #[Test]
    public function fileReplacedUnderTheSameNameIsKept(): void
    {
        $client = self::createClient();
        $this->loadFixtures($client);
        $em = $this->getEntityManager($client);
        $image = new Image();
        $image->setTitle('Test image');
        $image->setImageFile($this->createUploadedFile($client, 'flush_same_name.png'));
        $em->persist($image);
        $em->flush();
        $image->setImageFile($this->createUploadedFile($client, 'flush_same_name.png'));

        $em->flush();

        self::assertSame('flush_same_name.png', $image->getImageName());
        self::assertFileExists($this->getUploadsDir($client).'/flush_same_name.png');
    }

    #[Test]
    public function previousFileIsKeptWhenTheFlushFails(): void
    {
        $client = self::createClient();
        $this->loadFixtures($client);
        $em = $this->getEntityManager($client);
        $image = new Image();
        $image->setTitle('Test image');
        $image->setImageFile($this->createUploadedFile($client, 'flush_kept.png'));
        $em->persist($image);
        $em->flush();
        $image->setImageFile($this->createUploadedFile($client, 'flush_rolled_back.png'));
        $image->setTitle(null);

        try {
            $em->flush();
            self::fail('The flush should fail on the null title.');
        } catch (NotNullConstraintViolationException) {
        }

        self::assertFileExists($this->getUploadsDir($client).'/flush_kept.png');
    }

    #[Test]
    public function fileIsNotHandledForReadOnlyEntity(): void
    {
        $client = self::createClient();
        $this->loadFixtures($client);
        $em = $this->getEntityManager($client);
        $image = new Image();
        $image->setTitle('Test image');
        $image->setImageFile($this->createUploadedFile($client, 'flush_read_only.png'));
        $em->persist($image);
        $em->flush();
        $em->getUnitOfWork()->markReadOnly($image);
        $image->setImageFile($this->createUploadedFile($client, 'flush_read_only_new.png'));

        $em->flush();

        self::assertFileExists($this->getUploadsDir($client).'/flush_read_only.png');
        self::assertFileDoesNotExist($this->getUploadsDir($client).'/flush_read_only_new.png');
    }

    private function getEntityManager(KernelBrowser $client): EntityManagerInterface
    {
        return self::getKernelContainer($client)->get('doctrine')->getManager();
    }

    private function createUploadedFile(KernelBrowser $client, string $name): UploadedFile
    {
        $path = \sprintf('%s/%s', $client->getKernel()->getCacheDir(), $name);
        \copy($this->getImagesDir($client).'/symfony_black_03.png', $path);
        $this->createdFiles[] = $path;
        $this->createdFiles[] = \sprintf('%s/%s', $this->getUploadsDir($client), $name);

        return new UploadedFile($path, $name, 'image/png', test: true);
    }
}
