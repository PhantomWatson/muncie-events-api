<?php
namespace App\Test\TestCase\Controller\V1;

use App\Test\TestCase\ApplicationTest;
use Cake\ORM\TableRegistry;
use PHPUnit\Exception;

/**
 * PagesControllerTest class
 */
class PagesControllerTest extends ApplicationTest
{
    private $aboutUrl;
    private $rulesEventsUrl;
    private $rulesImagesUrl;
    private $rulesTagsUrl;
    private $widgetsUrl;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.ApiCalls',
        'app.Events',
        'app.Users',
    ];

    /**
     * Sets up this set of tests
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->configRequest([
            'environment' => ['HTTPS' => 'on'],
        ]);
        $this->aboutUrl = [
            'prefix' => 'v1',
            'controller' => 'Pages',
            'action' => 'about',
        ];
        $this->rulesEventsUrl = [
            'prefix' => 'v1',
            'controller' => 'Pages',
            'action' => 'rulesEvents',
        ];
        $this->rulesImagesUrl = [
            'prefix' => 'v1',
            'controller' => 'Pages',
            'action' => 'rulesImages',
        ];
        $this->rulesTagsUrl = [
            'prefix' => 'v1',
            'controller' => 'Pages',
            'action' => 'rulesTags',
        ];
        $this->widgetsUrl = [
            'prefix' => 'v1',
            'controller' => 'Pages',
            'action' => 'widgets',
        ];
    }

    /**
     * Tests /pages/about endpoint
     *
     * @return void
     * @throws Exception
     */
    public function testAboutSuccess()
    {
        $this->get($this->aboutUrl);
        $this->assertPageHasTitleAndBody();

        // Assert that there's no error in displaying the current number of events
        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        $eventsCount = $eventsTable->find()->count();
        $response = (array)json_decode($this->_response->getBody());
        $page = $response['data']->attributes;

        $needle = sprintf('for %s local events', number_format($eventsCount));
        $this->assertContains($needle, $page->body, 'Page does not contain expected event count string');
    }

    /**
     * Tests that /pages/about fails for non-GET requests
     *
     * @return void
     * @throws Exception
     */
    public function testAboutFailBadMethod()
    {
        $this->assertDisallowedMethods($this->aboutUrl, ['post', 'put', 'patch', 'delete']);
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
        $this->assertTrue(isset($page->title), 'Page is missing a title attribute');
        $this->assertTrue(isset($page->body), 'Page is missing a body attribute');
        $this->assertNotEmpty($page->title, 'Page has a blank title');
        $this->assertNotEmpty($page->title, 'Page has a blank body');
    }

    /**
     * Tests /pages/rules-events endpoint
     *
     * @return void
     * @throws Exception
     */
    public function testRulesEventsSuccess()
    {
        $this->get($this->rulesEventsUrl);
        $this->assertPageHasTitleAndBody();
    }

    /**
     * Tests that /pages/rules-events fails for non-GET requests
     *
     * @return void
     * @throws Exception
     */
    public function testRulesEventsFailBadMethod()
    {
        $this->assertDisallowedMethods($this->rulesEventsUrl, ['post', 'put', 'patch', 'delete']);
    }

    /**
     * Tests /pages/rules-tags endpoint
     *
     * @return void
     * @throws Exception
     */
    public function testRulesTagsSuccess()
    {
        $this->get($this->rulesTagsUrl);
        $this->assertPageHasTitleAndBody();
    }

    /**
     * Tests that /pages/rules-tags fails for non-GET requests
     *
     * @return void
     * @throws Exception
     */
    public function testRulesTagsFailBadMethod()
    {
        $this->assertDisallowedMethods($this->rulesTagsUrl, ['post', 'put', 'patch', 'delete']);
    }

    /**
     * Tests /pages/rules-images endpoint
     *
     * @return void
     * @throws Exception
     */
    public function testRulesImagesSuccess()
    {
        $this->get($this->rulesImagesUrl);
        $this->assertPageHasTitleAndBody();
    }

    /**
     * Tests that /pages/rules-images fails for non-GET requests
     *
     * @return void
     * @throws Exception
     */
    public function testRulesImagesFailBadMethod()
    {
        $this->assertDisallowedMethods($this->rulesImagesUrl, ['post', 'put', 'patch', 'delete']);
    }

    /**
     * Tests /pages/widgets endpoint
     *
     * @return void
     * @throws Exception
     */
    public function testWidgetsSuccess()
    {
        $this->get($this->widgetsUrl);
        $this->assertPageHasTitleAndBody();
    }

    /**
     * Tests that /pages/widgets fails for non-GET requests
     *
     * @return void
     * @throws Exception
     */
    public function testWidgetsFailBadMethod()
    {
        $this->assertDisallowedMethods($this->widgetsUrl, ['post', 'put', 'patch', 'delete']);
    }
}
