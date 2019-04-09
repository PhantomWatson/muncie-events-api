<?php
namespace App\Test\TestCase\Controller\V1;

use App\Test\Fixture\EventsFixture;
use App\Test\Fixture\TagsFixture;
use App\Test\TestCase\ApplicationTest;
use Cake\Utility\Hash;

/**
 * TagsControllerTest class
 */
class TagsControllerTest extends ApplicationTest
{
    private $futureUrl;
    private $indexUrl;
    private $treeUrl;
    private $viewUrl;

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

    /**
     * Sets up this set of tests
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->treeUrl = [
            'prefix' => 'v1',
            'controller' => 'Tags',
            'action' => 'tree',
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $this->futureUrl = [
            'prefix' => 'v1',
            'controller' => 'Tags',
            'action' => 'future',
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $this->viewUrl = [
            'prefix' => 'v1',
            'controller' => 'Tags',
            'action' => 'view',
            TagsFixture::TAG_WITH_EVENT,
            '?' => ['apikey' => $this->getApiKey()]
        ];
        $this->indexUrl = [
            'prefix' => 'v1',
            'controller' => 'Tags',
            'action' => 'index',
            '?' => ['apikey' => $this->getApiKey()]
        ];
    }

    /**
     * Tests that /tags/tree returns the correct results
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testTreeSuccess()
    {
        $this->get($this->treeUrl);
        $this->assertResponseOk();

        $fixture = new TagsFixture();
        $fixtureRootTags = $fixture->getRootTags();

        $response = (array)json_decode($this->_response->getBody());
        $responseRootTagNames = Hash::extract($response['data'], '{n}.attributes.name');

        // Test that all root tags are included in the response's 'data' field (except listed=false tags)
        foreach ($fixtureRootTags as $tag) {
            if (!$tag['listed']) {
                continue;
            }
            $this->assertContains($tag['name'], $responseRootTagNames, 'Expected root tag not in results');
        }

        // Test that unlisted root tags are not included
        foreach ($fixtureRootTags as $tag) {
            if ($tag['listed']) {
                continue;
            }
            $this->assertNotContains($tag['name'], $responseRootTagNames, 'Unlisted tag included in results');
        }

        // Test that nonroot tags don't appear in the 'data' portion of the result (since they should be in 'included')
        $nonrootTags = $fixture->getNonRootTags();
        foreach ($nonrootTags as $tag) {
            $this->assertNotContains($tag['name'], $responseRootTagNames, 'Nonroot tag found in response root');
        }

        // Test that nonroot tags appear in the 'included' portion of the result
        $responseNonrootTagNames = Hash::extract($response['included'], '{n}.attributes.name');
        foreach ($nonrootTags as $tag) {
            $this->assertContains($tag['name'], $responseNonrootTagNames, 'Nonroot tag not found in "included"');
        }
    }

    /**
     * Tests that /tags/tree fails for non-GET requests
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testTreeFailBadMethod()
    {
        $this->assertDisallowedMethods($this->treeUrl, ['post', 'put', 'patch', 'delete']);
    }

    /**
     * Tests that /tags/future returns the correct results
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testFuture()
    {
        $this->get($this->futureUrl);
        $this->assertResponseOk();

        $response = (array)json_decode($this->_response->getBody());
        $this->assertNotEmpty($response['data']);

        $counts = Hash::extract($response['data'], '{n}.attributes.upcomingEventCount');
        $lowestCount = min($counts);
        $this->assertTrue($lowestCount > 0, 'Tag returned with upcomingEventCount <= 0');
    }

    /**
     * Tests that /tags/future fails for non-GET requests
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testFutureFailBadMethod()
    {
        $this->assertDisallowedMethods($this->futureUrl, ['post', 'put', 'patch', 'delete']);
    }

    /**
     * Tests that /tags/future returns the correct results
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testViewSuccess()
    {
        $this->get($this->viewUrl);
        $this->assertResponseOk();

        // Test tag data
        $response = (array)json_decode($this->_response->getBody());
        $responseTagId = $response['data']->id;
        $this->assertEquals(TagsFixture::TAG_WITH_EVENT, $responseTagId);

        // Test tag 'relationships'
        $responseEventIds = Hash::extract($response['data']->relationships->events->data, '{n}.id');
        $this->assertContains(EventsFixture::EVENT_WITH_TAG, $responseEventIds);
        $this->assertNotContains(EventsFixture::EVENT_WITH_DIFFERENT_TAG, $responseEventIds);

        // Test 'included' data
        $responseEventIds = Hash::extract($response['included'], '{n}.id');
        $this->assertContains(EventsFixture::EVENT_WITH_TAG, $responseEventIds);
        $this->assertNotContains(EventsFixture::EVENT_WITH_DIFFERENT_TAG, $responseEventIds);
    }

    /**
     * Tests that /tag/{tagId} fails for non-GET requests
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testViewFailBadMethod()
    {
        $this->assertDisallowedMethods($this->viewUrl, ['post', 'put', 'patch', 'delete']);
    }

    /**
     * Tests that GET /v1/tags/ returns the correct results
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testIndexSuccess()
    {
        $this->get($this->indexUrl);
        $this->assertResponseOk();

        $fixture = new TagsFixture();
        $response = (array)json_decode($this->_response->getBody());
        $responseTagIds = Hash::extract($response['data'], '{n}.id');
        foreach ($fixture->records as $tag) {
            if ($tag['listed'] && $tag['selectable']) {
                $this->assertContains($tag['id'], $responseTagIds, 'Expected tag not in results');
            } else {
                $this->assertNotContains($tag['id'], $responseTagIds, 'Unlisted/unselectable tag found in results');
            }
        }
    }

    /**
     * Tests that /v1/tags fails for non-GET requests
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testIndexFailBadMethod()
    {
        $this->assertDisallowedMethods($this->indexUrl, ['post', 'put', 'patch', 'delete']);
    }
}
