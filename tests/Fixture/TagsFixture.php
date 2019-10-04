<?php
namespace App\Test\Fixture;

use App\Model\Table\TagsTable;
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
        'name' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => '', 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'listed' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'selectable' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '1', 'comment' => '', 'precision' => null],
        'user_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => '1969-12-31 23:59:59', 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    const TAG_NAME = 'test tag';
    const TAG_NAME_ALTERNATE = 'another tag';
    const TAG_NAME_CHILD = 'child tag';
    const TAG_NAME_UNLISTED = 'unlisted tag';
    const TAG_WITH_EVENT = 1;
    const TAG_WITH_DIFFERENT_EVENT = 2;
    const TAG_ID_CHILD = 3;
    const TAG_ID_UNLISTED = 4;

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => self::TAG_WITH_EVENT,
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
            'id' => self::TAG_WITH_DIFFERENT_EVENT,
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
            'id' => self::TAG_ID_CHILD,
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
            'id' => self::TAG_ID_UNLISTED,
            'parent_id' => null,
            'lft' => 7,
            'rght' => 8,
            'name' => self::TAG_NAME_UNLISTED,
            'listed' => 0,
            'selectable' => 1,
            'user_id' => 1,
            'created' => '2017-11-20 22:39:12'
        ],
        [
            'id' => TagsTable::UNLISTED_GROUP_ID,
            'parent_id' => null,
            'lft' => 9,
            'rght' => 10,
            'name' => 'unlisted',
            'listed' => 0,
            'selectable' => 0,
            'user_id' => 1,
            'created' => '2017-11-20 22:39:12'
        ]
    ];

    /**
     * Returns only the tags that are in the tag tree root (i.e. have a null parent)
     *
     * @return array
     */
    public function getRootTags()
    {
        $retval = [];
        foreach ($this->records as $tag) {
            if ($tag['parent_id'] === null) {
                $retval[] = $tag;
            }
        }

        return $retval;
    }

    /**
     * Returns only the tags that are NOT in the tag tree root (i.e. have a non-null parent)
     *
     * @return array
     */
    public function getNonRootTags()
    {
        $retval = [];
        foreach ($this->records as $tag) {
            if ($tag['parent_id'] !== null) {
                $retval[] = $tag;
            }
        }

        return $retval;
    }
}
