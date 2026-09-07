<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EventsTable;
use App\Test\Fixture\EventsFixture;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\EventsTable Test Case
 */
class EventsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var EventsTable
     */
    public $Events;

    /**
     * Fixtures
     *
     * @var array
     */
    public array $fixtures = [
        'app.Events',
        'app.Users',
        'app.Categories',
        'app.EventSeries',
        'app.Images',
        'app.EventsImages',
        'app.Tags',
        'app.EventsTags',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Events') ? [] : ['className' => EventsTable::class];
        $this->Events = TableRegistry::getTableLocator()->get('Events', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Events);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getUniqueLocationNames method
     *
     * @return void
     */
    public function testGetUniqueLocationNames()
    {
        $locationNames = $this->Events->getUniqueLocationNames();

        $this->assertContains(EventsFixture::MERGE_LOCATION_A, $locationNames);
        $this->assertContains(EventsFixture::MERGE_LOCATION_B, $locationNames);
        $this->assertSame($locationNames, array_unique($locationNames));

        $sorted = $locationNames;
        sort($sorted);
        $this->assertSame($sorted, $locationNames);
    }

    /**
     * Test mergeLocations method
     *
     * @return void
     */
    public function testMergeLocations()
    {
        $count = $this->Events->mergeLocations(
            EventsFixture::MERGE_LOCATION_A,
            EventsFixture::MERGE_LOCATION_B
        );
        $this->assertEquals(2, $count);

        foreach ([EventsFixture::EVENT_AT_MERGE_LOCATION_A, EventsFixture::EVENT_AT_MERGE_LOCATION_A_2] as $eventId) {
            $event = $this->Events->get($eventId);
            $this->assertEquals(EventsFixture::MERGE_LOCATION_B, $event->location);
            $this->assertEquals(EventsFixture::MERGE_LOCATION_B_SLUG, $event->location_slug);
        }

        $untouchedEvent = $this->Events->get(EventsFixture::EVENT_AT_MERGE_LOCATION_B);
        $this->assertEquals(EventsFixture::MERGE_LOCATION_B, $untouchedEvent->location);
        $this->assertEquals(EventsFixture::MERGE_LOCATION_B_SLUG, $untouchedEvent->location_slug);
    }
}
