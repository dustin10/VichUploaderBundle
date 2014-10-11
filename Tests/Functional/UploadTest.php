<?php

namespace Vich\UploaderBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadTest extends WebTestCase
{
    public function testFileIsUploaded()
    {
        $client = static::createClient();
        $this->loadFixtures($client);

        $crawler = $client->request('GET', '/upload');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->selectButton('form_save')->form();
        $image = new UploadedFile(
            $this->getImagesDir($client) . '/symfony_black_03.png',
            'symfony_black_03.png',
            'image/png',
            123
        );

        $crawler = $client->submit($form, array(
            'form' => array(
                'title'     => 'Test image',
                'imageFile' => $image,
            ),
        ));

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertFileExists($this->getUploadsDir($client) . '/symfony_black_03.png', 'The file is uploaded');
    }
}
