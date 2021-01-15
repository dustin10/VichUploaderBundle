<?php

namespace Vich\UploaderBundle\Tests\Functional;

final class UploadTest extends WebTestCase
{
    public function testFileIsUploadedWithFileType(): void
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        $crawler = $client->request('GET', '/upload/vich_file');
        self::assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->selectButton('form_save')->form();
        $image = $this->getUploadedFile($client, 'symfony_black_03.png');

        $client->submit($form, [
            'form' => [
                'title' => 'Test image',
                'imageFile' => ['file' => $image],
            ],
        ]);

        // we should be redirected to the "view" page
        self::assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();

        self::assertTrue($client->getResponse()->isSuccessful());
        self::assertFileExists($this->getUploadsDir($client).'/symfony_black_03.png', 'The file is uploaded');

        // test the delete feature
        self::assertCount(1, $crawler->filter('input[type=checkbox]'), 'the delete checkbox is here');
        $form = $crawler->selectButton('form_save')->form();
        $client->submit($form, [
            'form' => [
                'title' => 'Test image',
                'imageFile' => ['delete' => true],
            ],
        ]);
        self::assertTrue($client->getResponse()->isRedirect());
        self::assertFileDoesNotExist($this->getUploadsDir($client).'/symfony_black_03.png', 'The file is deleted');
    }

    public function testFileIsUploadedWithImageType(): void
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        $crawler = $client->request('GET', '/upload/vich_image');
        self::assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->selectButton('form_save')->form();
        $image = $this->getUploadedFile($client, 'symfony_black_03.png');

        $crawler = $client->submit($form, [
            'form' => [
                'title' => 'Test image',
                'imageFile' => ['file' => $image],
            ],
        ]);

        // we should be redirected to the "view" page
        self::assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();

        self::assertTrue($client->getResponse()->isSuccessful());
        self::assertFileExists($this->getUploadsDir($client).'/symfony_black_03.png', 'The file is uploaded');

        // test the delete feature
        self::assertCount(1, $crawler->filter('input[type=checkbox]'), 'the delete checkbox is here');
        $form = $crawler->selectButton('form_save')->form();
        $client->submit($form, [
            'form' => [
                'title' => 'Test image',
                'imageFile' => ['delete' => true],
            ],
        ]);
        self::assertTrue($client->getResponse()->isRedirect());
        self::assertFileDoesNotExist($this->getUploadsDir($client).'/symfony_black_03.png', 'The file is deleted');
    }
}
