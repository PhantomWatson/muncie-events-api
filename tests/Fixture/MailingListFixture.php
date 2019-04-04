<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * MailingListFixture
 *
 */
class MailingListFixture extends TestFixture
{

    /**
     * Table name
     *
     * @var string
     */
    public $table = 'mailing_list';

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'email' => ['type' => 'string', 'length' => 200, 'null' => false, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'all_categories' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1', 'comment' => '', 'precision' => null],
        'weekly' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'daily_sun' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'daily_mon' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'daily_tue' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'daily_wed' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'daily_thu' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'daily_fri' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'daily_sat' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'new_subscriber' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1', 'comment' => '', 'precision' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => '1969-12-31 23:59:59', 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => '1969-12-31 23:59:59', 'comment' => '', 'precision' => null],
        'processed_daily' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'processed_weekly' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
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
     * Init method
     *
     * @return void
     */
    public function init()
    {
        $this->records = [
            [
                'id' => 1,
                'email' => 'subscriber@example.com',
                'all_categories' => 1,
                'weekly' => 1,
                'daily_sun' => 1,
                'daily_mon' => 1,
                'daily_tue' => 1,
                'daily_wed' => 1,
                'daily_thu' => 1,
                'daily_fri' => 1,
                'daily_sat' => 1,
                'new_subscriber' => 1,
                'created' => '2019-03-27 20:42:23',
                'modified' => '2019-03-27 20:42:23',
                'processed_daily' => '2019-03-27 20:42:23',
                'processed_weekly' => '2019-03-27 20:42:23'
            ],
            [
                'id' => 2,
                'email' => 'user1@example.com',
                'all_categories' => 0,
                'weekly' => 0,
                'daily_sun' => 1,
                'daily_mon' => 0,
                'daily_tue' => 0,
                'daily_wed' => 0,
                'daily_thu' => 0,
                'daily_fri' => 0,
                'daily_sat' => 0,
                'new_subscriber' => 1,
                'created' => '2019-03-27 20:42:23',
                'modified' => '2019-03-27 20:42:23',
                'processed_daily' => '2019-03-27 20:42:23',
                'processed_weekly' => '2019-03-27 20:42:23'
            ]
        ];
        parent::init();
    }
}
