<?php

namespace Vich\UploaderBundle\Tests\Functional;

final class UploadTest extends WebTestCase
{
    /**
     * @dataProvider uploadTypeDataProvider
     */
    public function testFileIsUploadedWithFileType(string $uploadType, string $imageFieldName): void
    {
        if (headers_sent()) {
            self::markTestSkipped();
        }

        $client = self::createClient();
        $this->loadFixtures($client);

        $crawler = $client->request('GET', \sprintf('/%s/vich_file', $uploadType));
        self::assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->selectButton('form_save')->form();
        $image = $this->getUploadedFile($client, 'symfony_black_03.png');

        $client->submit($form, [
            'form' => [
                'title' => 'Test image',
                $imageFieldName => ['file' => $image],
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
                $imageFieldName => ['delete' => true],
            ],
        ]);
        self::assertTrue($client->getResponse()->isRedirect());
        self::assertFileDoesNotExist($this->getUploadsDir($client).'/symfony_black_03.png', 'The file is deleted');
    }

    /**
     * @dataProvider uploadTypeDataProvider
     */
    public function testFileIsUploadedWithImageType(string $uploadType, string $imageFieldName): void
    {
        if (headers_sent()) {
            self::markTestSkipped();
        }

        $client = self::createClient();
        $this->loadFixtures($client);

        $crawler = $client->request('GET', \sprintf('/%s/vich_image', $uploadType));
        self::assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->selectButton('form_save')->form();
        $image = $this->getUploadedFile($client, 'symfony_black_03.png');

        $client->submit($form, [
            'form' => [
                'title' => 'Test image',
                $imageFieldName => ['file' => $image],
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
                $imageFieldName => ['delete' => true],
            ],
        ]);
        self::assertTrue($client->getResponse()->isRedirect());
        self::assertFileDoesNotExist($this->getUploadsDir($client).'/symfony_black_03.png', 'The file is deleted');
    }

    /**
     * @return array<array{string, string}>
     */
    public static function uploadTypeDataProvider(): array
    {
        return [
            ['upload', 'imageFile'],
            ['upload_with_property_path', 'image_file'],
        ];
    }
}
