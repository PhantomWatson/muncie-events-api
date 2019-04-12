<?php
namespace App\Test\TestCase\Controller\V1;

use App\Test\TestCase\ApplicationTest;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestEmailTransport;
use PHPUnit\Exception;

/**
 * ContactControllerTest class
 */
class ContactControllerTest extends ApplicationTest
{
    use EmailTrait;

    private $contactUrl;
    private $formData;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.ApiCalls',
        'app.Users'
    ];

    /**
     * Sets up this set of tests
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->contactUrl = [
            'prefix' => 'v1',
            'controller' => 'Contact',
            'action' => 'index',
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $this->formData = [
            'name' => 'Test name',
            'email' => 'test@example.com',
            'body' => 'Lorem ipsum...'
        ];
    }

    /**
     * Method for cleaning up after each test
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        // Clean up previously sent emails for the next test
        TestEmailTransport::clearEmails();
    }

    /**
     * Tests that /v1/contact returns the correct success status code
     *
     * @return void
     * @throws Exception
     */
    public function testContactSuccess()
    {
        $this->post($this->contactUrl, $this->formData);

        $this->assertResponseCode(204);
        $this->assertMailSentTo(Configure::read('adminEmail'));
        $this->assertMailSentFrom($this->formData['email']);
        $this->assertMailContains($this->formData['body']);
    }

    /**
     * Tests that /v1/contact fails when user uses non-POST methods
     *
     * @return void
     * @throws Exception
     */
    public function testContactFailBadMethod()
    {
        $this->assertDisallowedMethods($this->contactUrl, ['get', 'put', 'patch', 'delete']);
        $this->assertNoMailSent();
    }

    /**
     * Tests that /v1/contact fails when user uses GET method
     *
     * @return void
     * @throws Exception
     */
    public function testContactFailMissingParam()
    {
        foreach ($this->formData as $field => $value) {
            $incompleteData = $this->formData;
            $incompleteData[$field] = '';
            $this->post($this->contactUrl, $incompleteData);
            $this->assertResponseError();
            $this->assertNoMailSent();
        }
    }
}
