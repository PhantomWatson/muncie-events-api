<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EventsTagsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\EventsTagsTable Test Case
 */
class EventsTagsTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \App\Model\Table\EventsTagsTable
     */
    public $EventsTags;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.events_tags',
        'app.events',
        'app.users',
        'app.categories',
        'app.event_series',
        'app.images',
        'app.events_images',
        'app.tags'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::exists('EventsTags') ? [] : ['className' => EventsTagsTable::class];
        $this->EventsTags = TableRegistry::get('EventsTags', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->EventsTags);

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
}
