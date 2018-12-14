<?php
namespace App\Test\TestCase\Controller\V1;

use App\Test\Fixture\TagsFixture;
use App\Test\TestCase\ApplicationTest;
use Cake\Utility\Hash;

/**
 * TagsControllerTest class
 */
class TagsControllerTest extends ApplicationTest
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.api_calls',
        'app.categories',
        'app.event_series',
        'app.events',
        'app.events_images',
        'app.events_tags',
        'app.images',
        'app.tags',
        'app.users'
    ];

    /**
     * Tests that /tags/tree returns the correct results
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testTree()
    {
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Tags',
            'action' => 'tree',
            '?' => ['apikey' => $this->getApiKey()]
        ]);
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
     * Tests that /tags/upcoming returns the correct results
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testUpcoming()
    {
        $this->get([
            'prefix' => 'v1',
            'controller' => 'Tags',
            'action' => 'upcoming',
            '?' => ['apikey' => $this->getApiKey()]
        ]);
        $this->assertResponseOk();

        $response = (array)json_decode($this->_response->getBody());
        $this->assertNotEmpty($response['data']);

        $counts = Hash::extract($response['data'], '{n}.attributes.upcomingEventCount');
        $lowestCount = min($counts);
        $this->assertTrue($lowestCount > 0, 'Tag returned with upcomingEventCount <= 0');
    }
}
