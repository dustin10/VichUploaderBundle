<?php

namespace Vich\UploaderBundle\Tests\Functional;

class UploadTest extends WebTestCase
{
    public function testFileIsUploadedWithFileType()
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        $crawler = $client->request('GET', '/upload/vich_file');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->selectButton('form_save')->form();
        $image = $this->getUploadedFile($client, 'symfony_black_03.png');

        $crawler = $client->submit($form, [
            'form' => [
                'title' => 'Test image',
                'imageFile' => ['file' => $image],
            ],
        ]);

        // we should be redirected to the "view" page
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertFileExists($this->getUploadsDir($client).'/symfony_black_03.png', 'The file is uploaded');

        // test the delete feature
        $this->assertCount(1, $crawler->filter('input[type=checkbox]'), 'the delete checkbox is here');
        $form = $crawler->selectButton('form_save')->form();
        $crawler = $client->submit($form, [
            'form' => [
                'title' => 'Test image',
                'imageFile' => ['delete' => true],
            ],
        ]);
        $this->assertTrue($client->getResponse()->isRedirect());
        $this->assertFileNotExists($this->getUploadsDir($client).'/symfony_black_03.png', 'The file is deleted');
    }

    public function testFileIsUploadedWithImageType()
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        $crawler = $client->request('GET', '/upload/vich_image');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->selectButton('form_save')->form();
        $image = $this->getUploadedFile($client, 'symfony_black_03.png');

        $crawler = $client->submit($form, [
            'form' => [
                'title' => 'Test image',
                'imageFile' => ['file' => $image],
            ],
        ]);

        // we should be redirected to the "view" page
        $this->assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertFileExists($this->getUploadsDir($client).'/symfony_black_03.png', 'The file is uploaded');

        // test the delete feature
        $this->assertCount(1, $crawler->filter('input[type=checkbox]'), 'the delete checkbox is here');
        $form = $crawler->selectButton('form_save')->form();
        $crawler = $client->submit($form, [
            'form' => [
                'title' => 'Test image',
                'imageFile' => ['delete' => true],
            ],
        ]);
        $this->assertTrue($client->getResponse()->isRedirect());
        $this->assertFileNotExists($this->getUploadsDir($client).'/symfony_black_03.png', 'The file is deleted');
    }
}
