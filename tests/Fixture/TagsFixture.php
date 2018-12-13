<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * TagsFixture
 *
 */
class TagsFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 10, 'unsigned' => true, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'parent_id' => ['type' => 'integer', 'length' => 10, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'lft' => ['type' => 'integer', 'length' => 10, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'rght' => ['type' => 'integer', 'length' => 10, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'name' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'listed' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'selectable' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1', 'comment' => '', 'precision' => null],
        'user_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'MyISAM',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    const TAG_NAME = 'test tag';
    const TAG_NAME_ALTERNATE = 'another tag';
    const TAG_NAME_CHILD = 'child tag';
    const TAG_NAME_UNLISTED = 'unlisted tag';

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => 1,
            'parent_id' => null,
            'lft' => 1,
            'rght' => 4,
            'name' => self::TAG_NAME,
            'listed' => 1,
            'selectable' => 1,
            'user_id' => 1,
            'created' => '2017-11-20 22:39:12'
        ],
        [
            'id' => 2,
            'parent_id' => null,
            'lft' => 5,
            'rght' => 6,
            'name' => self::TAG_NAME_ALTERNATE,
            'listed' => 1,
            'selectable' => 1,
            'user_id' => 1,
            'created' => '2017-11-20 22:39:12'
        ],
        [
            'id' => 3,
            'parent_id' => 1,
            'lft' => 2,
            'rght' => 3,
            'name' => self::TAG_NAME_CHILD,
            'listed' => 1,
            'selectable' => 1,
            'user_id' => 1,
            'created' => '2017-11-20 22:39:12'
        ],
        [
            'id' => 4,
            'parent_id' => null,
            'lft' => 7,
            'rght' => 8,
            'name' => self::TAG_NAME_UNLISTED,
            'listed' => 0,
            'selectable' => 1,
            'user_id' => 1,
            'created' => '2017-11-20 22:39:12'
        ],
    ];
}
