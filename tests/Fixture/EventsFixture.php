<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Cake\Utility\Hash;

/**
 * EventsFixture
 *
 */
class EventsFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'title' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'description' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null],
        'location' => ['type' => 'string', 'length' => 50, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'location_details' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'address' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'user_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => '0', 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'category_id' => ['type' => 'smallinteger', 'length' => 6, 'unsigned' => false, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'series_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'date' => ['type' => 'date', 'length' => null, 'null' => false, 'default' => '0000-00-00', 'comment' => '', 'precision' => null],
        'time_start' => ['type' => 'time', 'length' => null, 'null' => false, 'default' => '00:00:00', 'comment' => '', 'precision' => null],
        'time_end' => ['type' => 'time', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'age_restriction' => ['type' => 'string', 'length' => 30, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'cost' => ['type' => 'string', 'length' => 200, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'source' => ['type' => 'string', 'length' => 200, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'published' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'approved_by' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_indexes' => [
            'person_id' => ['type' => 'index', 'columns' => ['user_id'], 'length' => []],
            'category_id' => ['type' => 'index', 'columns' => ['category_id'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'MyISAM',
            'collation' => 'latin1_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [];

    /**
     * The event ID that is associated with a tag
     *
     * @var int
     */
    const EVENT_WITH_TAG = 100;

    public function init()
    {
        $this->addEventsByCategory();
        $this->addEventsByTag();

        parent::init();
    }

    /**
     * Returns a set of arbitrary default data for events
     *
     * @return array
     */
    private function getDefaultEventData()
    {
        $categoriesFixture = new CategoriesFixture();
        $categories = $categoriesFixture->getCategories();

        return [
            'id' => 1,
            'title' => 'Event title',
            'description' => 'Event description.',
            'location' => 'Location name',
            'location_details' => 'Location details',
            'address' => 'Location address',
            'user_id' => 1,
            'category_id' => array_keys($categories)[0],
            'series_id' => 1,
            'date' => '2018-01-01',
            'time_start' => '22:38:43',
            'time_end' => '22:38:43',
            'age_restriction' => null,
            'cost' => null,
            'source' => 'Event info source',
            'published' => 1,
            'approved_by' => 1,
            'created' => '2017-11-20 22:38:43',
            'modified' => '2017-11-20 22:38:43'
        ];
    }

    /**
     * Adds events in different tag states (with and without tags)
     *
     * @return void
     */
    private function addEventsByTag()
    {
        $defaultEvent = $this->getDefaultEventData();
        $eventId = self::EVENT_WITH_TAG;
        $this->records[] = array_merge($defaultEvent, [
            'id' => $eventId,
            'date' => date('Y-m-d', strtotime('tomorrow')),
            'title' => 'event with tag'
        ]);

        $eventId++;
        $this->records[] = array_merge($defaultEvent, [
            'id' => $eventId,
            'date' => date('Y-m-d', strtotime('tomorrow')),
            'title' => 'event without tag',
        ]);
    }

    /**
     * Adds events in all categories, dated yesterday, today, and tomorrow
     *
     * @return void
     */
    private function addEventsByCategory()
    {
        $categoriesFixture = new CategoriesFixture();
        $categories = $categoriesFixture->getCategories();
        $defaultEvent = $this->getDefaultEventData();

        $eventId = 1;
        $dates = ['yesterday', 'today', 'tomorrow'];
        foreach ($dates as $date) {
            foreach ($categories as $categoryId => $categoryName) {
                $this->records[] = array_merge($defaultEvent, [
                    'id' => $eventId,
                    'date' => date('Y-m-d', strtotime($date)),
                    'title' => $categoryName . ' event ' . $date,
                    'category_id' => $categoryId
                ]);
                $eventId++;
            }
        }
    }
}
