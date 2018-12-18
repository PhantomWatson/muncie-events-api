<?php
namespace App\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * PagesControllerTest class
 */
class PagesControllerTest extends TestCase
{
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
            'environment' => ['HTTPS' => 'on']
        ]);
    }

    /**
     * testMultipleGet method
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testMultipleGet()
    {
        $this->get('/');
        $this->assertResponseOk();
        $this->get('/');
        $this->assertResponseOk();
    }

    /**
     * Tests HTTP requests being redirected to HTTPS
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testRedirectToHttps()
    {
        $this->configRequest([
            'environment' => ['HTTPS' => 'off']
        ]);
        $this->get('/');
        $this->assertRedirect();

        // Test redirection SPECIFICALLY to HTTPS
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Tests /docs/v1
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testDocsV1()
    {
        $this->get([
            'controller' => 'Pages',
            'action' => 'docsV1'
        ]);
        $this->assertResponseOk();
    }
}
