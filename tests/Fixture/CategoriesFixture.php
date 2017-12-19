<?php
namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Cake\Utility\Hash;

/**
 * CategoriesFixture
 *
 */
class CategoriesFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'name' => ['type' => 'string', 'length' => 50, 'null' => false, 'default' => '', 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'slug' => ['type' => 'string', 'length' => 50, 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'weight' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'MyISAM',
            'collation' => 'utf8_general_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => 8,
            'name' => 'Music',
            'slug' => '',
            'weight' => 1
        ],
        [
            'id' => 9,
            'name' => 'Art',
            'slug' => '',
            'weight' => 1
        ],
        [
            'id' => 10,
            'name' => 'Theater',
            'slug' => '',
            'weight' => 1
        ],
        [
            'id' => 11,
            'name' => 'Film',
            'slug' => '',
            'weight' => 1
        ],
        [
            'id' => 12,
            'name' => 'Activism',
            'slug' => '',
            'weight' => 1
        ],
        [
            'id' => 13,
            'name' => 'General Events',
            'slug' => '',
            'weight' => 1
        ],
        [
            'id' => 24,
            'name' => 'Education',
            'slug' => '',
            'weight' => 1
        ],
        [
            'id' => 25,
            'name' => 'Government',
            'slug' => '',
            'weight' => 1
        ],
        [
            'id' => 26,
            'name' => 'Sports',
            'slug' => '',
            'weight' => 1
        ],
        [
            'id' => 27,
            'name' => 'Religion',
            'slug' => '',
            'weight' => 1
        ]
    ];

    /**
     * Returns a one-dimensional array of category names, keyed with category IDs
     *
     * @return array
     */
    public function getCategories()
    {
        return Hash::combine($this->records, '{n}.id', '{n}.name');
    }
}
