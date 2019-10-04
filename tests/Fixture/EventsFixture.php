<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

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
        'title' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => '', 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'description' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'comment' => '', 'precision' => null],
        'location' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => '', 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'location_details' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => '', 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'address' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => '', 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'user_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => '0', 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'category_id' => ['type' => 'smallinteger', 'length' => 6, 'unsigned' => false, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'series_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'date' => ['type' => 'date', 'length' => null, 'null' => false, 'default' => '1969-12-31', 'comment' => '', 'precision' => null],
        'time_start' => ['type' => 'time', 'length' => null, 'null' => false, 'default' => '00:00:00', 'comment' => '', 'precision' => null],
        'time_end' => ['type' => 'time', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'age_restriction' => ['type' => 'string', 'length' => 30, 'null' => false, 'default' => '', 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'cost' => ['type' => 'string', 'length' => 200, 'null' => false, 'default' => '', 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'source' => ['type' => 'string', 'length' => 200, 'null' => false, 'default' => '', 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'published' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'approved_by' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => '1969-12-31 23:59:59', 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => '1969-12-31 23:59:59', 'comment' => '', 'precision' => null],
        '_indexes' => [
            'person_id' => ['type' => 'index', 'columns' => ['user_id'], 'length' => []],
            'category_id' => ['type' => 'index', 'columns' => ['category_id'], 'length' => []],
        ],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci'
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

    /**
     * The event ID that is associated with another tag
     *
     * @var int
     */
    const EVENT_WITH_DIFFERENT_TAG = 101;

    const EVENT_WITH_SEARCHABLE_TITLE = 110;
    const SEARCHABLE_TITLE = 'Searchable title';
    const EVENT_WITH_SEARCHABLE_DESCRIPTION = 111;
    const SEARCHABLE_DESCRIPTION = 'Searchable description';
    const EVENT_WITH_SEARCHABLE_LOCATION = 112;
    const SEARCHABLE_LOCATION = 'Searchable location';
    const PAST_EVENT_WITH_SEARCHABLE_TITLE = 113;
    const PAST_EVENT_WITH_SEARCHABLE_DESCRIPTION = 114;
    const PAST_EVENT_WITH_SEARCHABLE_LOCATION = 115;
    const EVENT_WITH_SEARCHABLE_TITLE_ALT_CATEGORY = 116;
    const EVENT_WITH_SEARCHABLE_DESCRIPTION_ALT_CATEGORY = 117;
    const EVENT_WITH_SEARCHABLE_LOCATION_ALT_CATEGORY = 118;
    const PAST_EVENT_WITH_SEARCHABLE_TITLE_ALT_CATEGORY = 119;
    const PAST_EVENT_WITH_SEARCHABLE_DESCRIPTION_ALT_CATEGORY = 120;
    const PAST_EVENT_WITH_SEARCHABLE_LOCATION_ALT_CATEGORY = 121;

    public function init()
    {
        $this->addEventsByCategory();
        $this->addEventsByTag();
        $this->addSearchableEvents();

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

        $eventId = self::EVENT_WITH_DIFFERENT_TAG;
        $this->records[] = array_merge($defaultEvent, [
            'id' => $eventId,
            'date' => date('Y-m-d', strtotime('tomorrow')),
            'title' => 'event with different tag'
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

    /**
     * Adds events with unique strings in their title, description, and location fields to test search functionality
     *
     * @return void
     */
    private function addSearchableEvents()
    {
        $defaultEvent = $this->getDefaultEventData();

        // Future events in first category
        $this->records[] = array_merge($defaultEvent, [
            'id' => self::EVENT_WITH_SEARCHABLE_TITLE,
            'date' => date('Y-m-d', strtotime('tomorrow')),
            'title' => self::SEARCHABLE_TITLE
        ]);
        $this->records[] = array_merge($defaultEvent, [
            'id' => self::EVENT_WITH_SEARCHABLE_DESCRIPTION,
            'date' => date('Y-m-d', strtotime('tomorrow')),
            'description' => self::SEARCHABLE_DESCRIPTION
        ]);
        $this->records[] = array_merge($defaultEvent, [
            'id' => self::EVENT_WITH_SEARCHABLE_LOCATION,
            'date' => date('Y-m-d', strtotime('tomorrow')),
            'location' => self::SEARCHABLE_LOCATION
        ]);

        // Past events in first category
        $this->records[] = array_merge($defaultEvent, [
            'id' => self::PAST_EVENT_WITH_SEARCHABLE_TITLE,
            'date' => date('Y-m-d', strtotime('yesterday')),
            'title' => self::SEARCHABLE_TITLE
        ]);
        $this->records[] = array_merge($defaultEvent, [
            'id' => self::PAST_EVENT_WITH_SEARCHABLE_DESCRIPTION,
            'date' => date('Y-m-d', strtotime('yesterday')),
            'description' => self::SEARCHABLE_DESCRIPTION
        ]);
        $this->records[] = array_merge($defaultEvent, [
            'id' => self::PAST_EVENT_WITH_SEARCHABLE_LOCATION,
            'date' => date('Y-m-d', strtotime('yesterday')),
            'location' => self::SEARCHABLE_LOCATION
        ]);

        // Future events in second category
        $categoriesFixture = new CategoriesFixture();
        $categories = $categoriesFixture->getCategories();
        $altCategoryId = array_keys($categories)[1];
        $this->records[] = array_merge($defaultEvent, [
            'id' => self::EVENT_WITH_SEARCHABLE_TITLE_ALT_CATEGORY,
            'date' => date('Y-m-d', strtotime('tomorrow')),
            'title' => self::SEARCHABLE_TITLE,
            'category_id' => $altCategoryId
        ]);
        $this->records[] = array_merge($defaultEvent, [
            'id' => self::EVENT_WITH_SEARCHABLE_DESCRIPTION_ALT_CATEGORY,
            'date' => date('Y-m-d', strtotime('tomorrow')),
            'description' => self::SEARCHABLE_DESCRIPTION,
            'category_id' => $altCategoryId
        ]);
        $this->records[] = array_merge($defaultEvent, [
            'id' => self::EVENT_WITH_SEARCHABLE_LOCATION_ALT_CATEGORY,
            'date' => date('Y-m-d', strtotime('tomorrow')),
            'location' => self::SEARCHABLE_LOCATION,
            'category_id' => $altCategoryId
        ]);

        // Past events in second category
        $this->records[] = array_merge($defaultEvent, [
            'id' => self::PAST_EVENT_WITH_SEARCHABLE_TITLE_ALT_CATEGORY,
            'date' => date('Y-m-d', strtotime('yesterday')),
            'title' => self::SEARCHABLE_TITLE,
            'category_id' => $altCategoryId
        ]);
        $this->records[] = array_merge($defaultEvent, [
            'id' => self::PAST_EVENT_WITH_SEARCHABLE_DESCRIPTION_ALT_CATEGORY,
            'date' => date('Y-m-d', strtotime('yesterday')),
            'description' => self::SEARCHABLE_DESCRIPTION,
            'category_id' => $altCategoryId
        ]);
        $this->records[] = array_merge($defaultEvent, [
            'id' => self::PAST_EVENT_WITH_SEARCHABLE_LOCATION_ALT_CATEGORY,
            'date' => date('Y-m-d', strtotime('yesterday')),
            'location' => self::SEARCHABLE_LOCATION,
            'category_id' => $altCategoryId
        ]);
    }
}
