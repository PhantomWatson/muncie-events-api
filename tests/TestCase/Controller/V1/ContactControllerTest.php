<?php
namespace App\Test\TestCase\Controller\V1;

use App\Test\TestCase\ApplicationTest;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestEmailTransport;

/**
 * ContactControllerTest class
 */
class ContactControllerTest extends ApplicationTest
{
    use EmailTrait;

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
     * @throws \PHPUnit\Exception
     */
    public function testContactSuccess()
    {
        $url = [
            'prefix' => 'v1',
            'controller' => 'Contact',
            'action' => 'index',
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $data = [
            'name' => 'Test name',
            'email' => 'test@example.com',
            'body' => 'Lorem ipsum...'
        ];
        $this->post($url, $data);

        $this->assertResponseCode(204);
    }

    /**
     * Tests that /v1/contact fails when user uses GET method
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testContactFailBadMethod()
    {
        $url = [
            'prefix' => 'v1',
            'controller' => 'Contact',
            'action' => 'index',
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $this->get($url);

        $this->assertResponseError();
    }

    /**
     * Tests that /v1/contact fails when user uses GET method
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testContactFailMissingParam()
    {
        $url = [
            'prefix' => 'v1',
            'controller' => 'Contact',
            'action' => 'index',
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $data = [
            'name' => 'Test name',
            'email' => 'test@example.com',
            'body' => 'Lorem ipsum...'
        ];
        foreach ($data as $field => $value) {
            $incompleteData = $data;
            $incompleteData[$field] = '';
            $this->post($url, $incompleteData);
            $this->assertResponseError();
        }
    }
}
