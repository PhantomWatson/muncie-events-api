<?php
namespace App\Test\TestCase\Controller\V1;

use App\Model\Table\TagsTable;
use App\Test\Fixture\EventsFixture;
use App\Test\Fixture\TagsFixture;
use App\Test\TestCase\ApplicationTest;
use Cake\Utility\Hash;
use PHPUnit\Exception;

/**
 * TagsControllerTest class
 */
class TagsControllerTest extends ApplicationTest
{
    private $autocompleteUrl;
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
        'app.Users',
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
        ];
        $this->futureUrl = [
            'prefix' => 'v1',
            'controller' => 'Tags',
            'action' => 'future',
        ];
        $this->viewUrl = [
            'prefix' => 'v1',
            'controller' => 'Tags',
            'action' => 'view',
            TagsFixture::TAG_WITH_EVENT,
        ];
        $this->indexUrl = [
            'prefix' => 'v1',
            'controller' => 'Tags',
            'action' => 'index',
        ];
        $this->autocompleteUrl = [
            'prefix' => 'v1',
            'controller' => 'Tags',
            'action' => 'autocomplete',
        ];
    }

    /**
     * Tests that /tags/tree returns the correct results
     *
     * @return void
     * @throws Exception
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
     * @throws Exception
     */
    public function testTreeFailBadMethod()
    {
        $this->assertDisallowedMethods($this->treeUrl, ['post', 'put', 'patch', 'delete']);
    }

    /**
     * Tests that /tags/future returns the correct results
     *
     * @return void
     * @throws Exception
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
     * @throws Exception
     */
    public function testFutureFailBadMethod()
    {
        $this->assertDisallowedMethods($this->futureUrl, ['post', 'put', 'patch', 'delete']);
    }

    /**
     * Tests that /tags/future returns the correct results
     *
     * @return void
     * @throws Exception
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
     * @throws Exception
     */
    public function testViewFailBadMethod()
    {
        $this->assertDisallowedMethods($this->viewUrl, ['post', 'put', 'patch', 'delete']);
    }

    /**
     * Tests that GET /v1/tags/ returns the correct results
     *
     * @return void
     * @throws Exception
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
     * @throws Exception
     */
    public function testIndexFailBadMethod()
    {
        $this->assertDisallowedMethods($this->indexUrl, ['post', 'put', 'patch', 'delete']);
    }

    /**
     * Tests that GET /v1/tags/autocomplete returns a successful response
     *
     * @throws Exception
     * @return void
     */
    public function testAutocompleteSuccess()
    {
        $url = $this->autocompleteUrl;
        $url['?']['term'] = 'tag';

        $this->get($url);
        $this->assertResponseOk();

        $response = (array)json_decode($this->_response->getBody());
        $responseTagIds = Hash::extract($response['data'], '{n}.id');

        $tagIdsExpected = [
            TagsFixture::TAG_WITH_EVENT,
            TagsFixture::TAG_WITH_DIFFERENT_EVENT,
            TagsFixture::TAG_ID_CHILD,
        ];
        foreach ($tagIdsExpected as $tagId) {
            $this->assertContains($tagId, $responseTagIds);
        }

        $tagIdsNotExpected = [
            TagsFixture::TAG_ID_UNLISTED,
            TagsTable::UNLISTED_GROUP_ID,
        ];
        foreach ($tagIdsNotExpected as $tagId) {
            $this->assertNotContains($tagId, $responseTagIds);
        }
    }

    /**
     * Tests that GET /v1/tags/autocomplete respects the optional limit parameter
     *
     * @throws Exception
     * @return void
     */
    public function testAutocompleteLimit()
    {
        $url = $this->autocompleteUrl;
        $url['?']['term'] = 'tag'; // This term would return at least three results if limit is unspecified

        $this->get($url);
        $this->assertResponseOk();

        // Test that default limit is respected
        $response = (array)json_decode($this->_response->getBody());
        $responseTagCount = count($response['data']);
        $this->assertLessThanOrEqual(10, $responseTagCount);
        $this->assertGreaterThan(1, $responseTagCount, 'Not enough tags are returned to properly run test');

        $url['?']['limit'] = $responseTagCount - 1;

        $this->get($url);
        $this->assertResponseOk();

        // Test that manually specified limit is respected
        $response = (array)json_decode($this->_response->getBody());
        $this->assertEquals($url['?']['limit'], count($response['data']));
    }

    /**
     * Tests that GET /v1/tags/autocomplete returns a success response code if no matching tags are found
     *
     * @throws Exception
     * @return void
     */
    public function testAutocompleteEmptyResults()
    {
        $url = $this->autocompleteUrl;
        $url['?']['term'] = 'string not present in any tag names';

        $this->get($url);
        $this->assertResponseOk();

        $response = (array)json_decode($this->_response->getBody());
        $responseTagCount = count($response['data']);
        $this->assertEquals(0, $responseTagCount);
    }

    /**
     * Tests that /v1/tags/autocomplete fails for non-GET requests
     *
     * @return void
     * @throws Exception
     */
    public function testAutocompleteFailBadMethod()
    {
        $this->assertDisallowedMethods($this->autocompleteUrl, ['post', 'put', 'patch', 'delete']);
    }

    /**
     * Tests that GET /v1/tags/autocomplete fails if term is unspecified
     *
     * @throws Exception
     * @return void
     */
    public function testAutocompleteFailMissingTerm()
    {
        $this->get($this->autocompleteUrl);
        $this->assertResponseError();
    }

    /**
     * Tests that GET /v1/tags/autocomplete fails if term parameter is present but empty
     *
     * @throws Exception
     * @return void
     */
    public function testAutocompleteFailEmptyTerm()
    {
        $url = $this->autocompleteUrl;
        $url['?']['term'] = '';

        $this->get($url);
        $this->assertResponseError();
    }

    /**
     * Tests that GET /v1/tags/autocomplete fails if limit is non-numeric or less than one
     *
     * @throws Exception
     * @return void
     */
    public function testAutocompleteFailInvalidLimit()
    {
        $url = $this->autocompleteUrl;
        $url['?']['term'] = 'tag';

        $badLimits = [
            '',
            'non-numeric',
            '0',
            '-5',
        ];
        foreach ($badLimits as $badLimit) {
            $url['?']['limit'] = $badLimit;
            $this->get($url);
            $this->assertResponseError();
        }
    }
}
