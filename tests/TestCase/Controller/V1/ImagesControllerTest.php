<?php
namespace App\Test\TestCase\Controller\V1;

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

    private $imageToUpload = TESTS . 'Files' . DS . 'UploadSource' . DS . 'MuncieEventsLogo.png';
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
        // Manually set the contents of $_FILES instead of passing data into post() method
        $filename = array_reverse(explode(DS, $this->imageToUpload))[0];
        $_FILES = [
            'file' => [
                'error'    => UPLOAD_ERR_OK,
                'name'     => $filename,
                'size'     => filesize($this->imageToUpload),
                'tmp_name' => $this->imageToUpload,
                'type'     => 'image/png'
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
        $extension = strtolower(array_reverse(explode('.', $filename))[0]);
        foreach (['full', 'small', 'tiny'] as $size) {
            $expectedPath = Configure::read('eventImageBaseUrl') . $size . '/';
            $expectedFilename = $response->data->id . '.' . $extension;
            $this->assertEquals(
                $expectedPath . $expectedFilename,
                $response->data->attributes->{$size . '_url'}
            );
        }
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
            foreach($files as $file){
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
