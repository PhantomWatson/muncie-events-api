<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;
use Cake\Core\Configure;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\IntegrationTestTrait;
use PHPUnit\Exception;

/**
 * PagesControllerTest class
 */
class PagesControllerTest extends ApplicationTest
{
    use EmailTrait;
    use IntegrationTestTrait;

    /**
     * Sets up this set of tests
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->configRequest([
            'environment' => ['HTTPS' => 'on'],
        ]);
    }

    /**
     * testMultipleGet method
     *
     * @return void
     * @throws Exception
     */
    public function testMultipleGet()
    {
        $this->get('/');
        $this->assertResponseOk();
        $this->get('/');
        $this->assertResponseOk();
    }

    /**
     * Test that the contact page loads
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testContactPageGetSuccess()
    {
        $this->get([
            'controller' => 'Pages',
            'action' => 'contact',
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('site administrator');
        $this->assertResponseContains('</html>');
        $this->assertNoMailSent();
    }

    /**
     * Test that the contact page sends emails
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testContactPagePostSuccess()
    {
        $data = [
            'category' => 'General',
            'name' => 'Sender name',
            'email' => 'sender@example.com',
            'body' => 'Message body',
        ];
        $this->post([
            'controller' => 'Pages',
            'action' => 'contact',
        ], $data);
        $this->assertResponseContains('Thank you for contacting us.');
        $this->assertResponseOk();
        $this->assertMailSentFrom($data['email']);
        $this->assertMailSentTo(Configure::read('adminEmail'));
        $this->assertMailContains($data['body']);
    }

    /**
     * Tests HTTP requests being redirected to HTTPS
     *
     * @return void
     * @throws Exception
     */
    public function testRedirectToHttps()
    {
        $this->configRequest([
            'environment' => ['HTTPS' => 'off'],
        ]);
        $this->get('/');
        $this->assertRedirect();

        // Test redirection SPECIFICALLY to HTTPS
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Tests that /api returns a successful response
     *
     * @return void
     * @throws Exception
     */
    public function testApi()
    {
        $this->get([
            'controller' => 'Pages',
            'action' => 'api',
        ]);
        $this->assertResponseOk();
    }
}
