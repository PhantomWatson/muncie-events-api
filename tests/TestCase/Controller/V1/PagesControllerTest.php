<?php
namespace App\Test\TestCase\Controller\V1;

use App\Test\TestCase\ApplicationTest;

/**
 * PagesControllerTest class
 */
class PagesControllerTest extends ApplicationTest
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.api_calls',
        'app.users'
    ];

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
     * Tests /pages/about endpoint
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testAbout()
    {
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Pages',
            'action' => 'about',
            '?' => ['apikey' => $this->getApiKey()]
        ]);
        $this->assertPageHasTitleAndBody();
    }

    /**
     * Asserts that a response from a /pages endpoint contains a title and body
     *
     * @return void
     */
    private function assertPageHasTitleAndBody()
    {
        $this->assertResponseOk();
        $response = (array)json_decode($this->_response->getBody());
        $page = $response['data']->attributes;
        $this->assertTrue(isset($page->title), 'About page is missing a title');
        $this->assertTrue(isset($page->body), 'About page is missing a body');
    }

    /**
     * Tests /pages/rules-events endpoint
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testRulesEvents()
    {
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Pages',
            'action' => 'rulesEvents',
            '?' => ['apikey' => $this->getApiKey()]
        ]);
        $this->assertPageHasTitleAndBody();
    }
}
