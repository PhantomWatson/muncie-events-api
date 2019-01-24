<?php
namespace App\Test\TestCase\Controller\V1;

use App\Model\Entity\Image;
use App\Test\TestCase\ApplicationTest;
use Cake\Core\Configure;

/**
 * ImagesControllerTest class
 */
class ImagesControllerTest extends ApplicationTest
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.ApiCalls',
        'app.Categories',
        'app.EventSeries',
        'app.Events',
        'app.EventsImages',
        'app.EventsTags',
        'app.Images',
        'app.Tags',
        'app.Users'
    ];

    // File extension is deliberately absent
    private $imageToUpload = TESTS . 'Files' . DS . 'UploadSource' . DS . 'MuncieEventsLogo';

    private $imagesDestination = TESTS . 'Files' . DS . 'UploadDestination';

    /**
     * Cleans up tests after they've completed
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        // Change the destination directory for uploaded files
        Configure::write('eventImagePath', $this->imagesDestination);

        $this->createUploadDirectories();
    }

    /**
     * Cleans up tests after they've completed
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        $this->deleteImages();
    }

    /**
     * Tests that a valid call to POST /v1/images returns the correct results
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testAddSuccess()
    {
        foreach (['.jpg', '.gif', '.png'] as $extension) {
            $this->checkUploadByExtension($extension);
        }
    }

    /**
     * Uploads an image specified by file type and asserts a successful result
     *
     * @param string $extension Extension with dot, e.g. '.jpg'
     * @throws \PHPUnit\Exception
     */
    public function checkUploadByExtension($extension)
    {
        $imagePath = $this->imageToUpload . $extension;

        // Manually set the contents of $_FILES instead of passing data into post() method
        $filename = array_reverse(explode(DS, $imagePath))[0];
        $_FILES = [
            'file' => [
                'error' => UPLOAD_ERR_OK,
                'name' => $filename,
                'size' => filesize($imagePath),
                'tmp_name' => $imagePath,
                'type' => 'image/png'
            ]
        ];

        $url = [
            'prefix' => 'v1',
            'controller' => 'Images',
            'action' => 'add',
            '?' => [
                'apikey' => $this->getApiKey(),
                'userToken' => $this->getUserToken()
            ]
        ];
        $this->post($url);
        $this->assertResponseOk();

        // Assert that an image ID gets returned
        $response = json_decode($this->_response->getBody());
        $this->assertNotEmpty($response->data->id);

        // Assert that image URLs are correct
        $expectedFilename = $response->data->id . $extension;
        foreach (['full', 'small', 'tiny'] as $size) {
            $expectedPath = Configure::read('eventImageBaseUrl') . $size . '/';
            $this->assertEquals(
                $expectedPath . $expectedFilename,
                $response->data->attributes->{$size . '_url'}
            );
        }

        // Assert that images are within allowed dimensions
        $path = $this->imagesDestination . DS . 'full' . DS . $expectedFilename;
        list($width, $height) = getimagesize($path);
        $this->assertLessThanOrEqual(Image::maxHeight, $height);
        $this->assertLessThanOrEqual(Image::maxWidth, $width);

        $path = $this->imagesDestination . DS . 'small' . DS . $expectedFilename;
        list($width) = getimagesize($path);
        $this->assertLessThanOrEqual(Image::smallWidth, $width);

        $path = $this->imagesDestination . DS . 'tiny' . DS . $expectedFilename;
        list($width, $height) = getimagesize($path);
        $this->assertLessThanOrEqual(Image::tinyHeight, $height);
        $this->assertLessThanOrEqual(Image::tinyWidth, $width);
    }

    /**
     * Deletes any files that have been uploaded during this testing session
     *
     * @return void
     */
    private function deleteImages()
    {
        foreach (['full', 'small', 'tiny'] as $subdir) {
            $files = glob($this->imagesDestination . DS . $subdir . DS . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Creates directories to which uploaded images will be saved
     *
     * @return void
     */
    private function createUploadDirectories()
    {
        if (!is_dir($this->imagesDestination)) {
            mkdir($this->imagesDestination);
        }
        foreach (['full', 'small', 'tiny'] as $subdir) {
            $dir = $this->imagesDestination . DS . $subdir;
            if (!is_dir($dir)) {
                mkdir($dir);
            }
        }
    }
}
