<?php
namespace App\Test\Fixture;

use App\Auth\LegacyPasswordHasher;
use Cake\Auth\DefaultPasswordHasher;
use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 *
 */
class UsersFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    // @codingStandardsIgnoreStart
    public $fields = [
        'id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'name' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => '', 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'role' => ['type' => 'string', 'length' => 20, 'null' => false, 'default' => 'user', 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'bio' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'collate' => 'utf8_unicode_ci', 'comment' => '', 'precision' => null],
        'email' => ['type' => 'string', 'length' => 100, 'null' => false, 'default' => '', 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'password' => ['type' => 'string', 'length' => 64, 'null' => false, 'default' => '', 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'mailing_list_id' => ['type' => 'integer', 'length' => 11, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'facebook_id' => ['type' => 'biginteger', 'length' => 20, 'unsigned' => true, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'api_key' => ['type' => 'string', 'length' => 32, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'token' => ['type' => 'string', 'length' => 32, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'reset_password_hash' => ['type' => 'string', 'length' => 32, 'null' => true, 'default' => null, 'collate' => 'utf8mb4_unicode_ci', 'comment' => '', 'precision' => null, 'fixed' => null],
        'created' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => '1969-12-31 23:59:59', 'comment' => '', 'precision' => null],
        'modified' => ['type' => 'datetime', 'length' => null, 'null' => false, 'default' => '1969-12-31 23:59:59', 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'uq' => ['type' => 'unique', 'columns' => ['email'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'MyISAM',
            'collation' => 'utf8mb4_unicode_ci'
        ],
    ];
    // @codingStandardsIgnoreEnd

    const USER_WITHOUT_EVENTS = 2;
    const SUBSCRIBED_USER_WITH_ASSOCIATION = 2;
    const SUBSCRIBED_USER_WITHOUT_ASSOCIATION = 1;
    const USER_NOT_SUBSCRIBED = 3;

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => self::SUBSCRIBED_USER_WITHOUT_ASSOCIATION,
            'name' => 'User',
            'role' => 'Lorem ipsum dolor ',
            'bio' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'email' => 'user1@example.com',
            'password' => '',
            'mailing_list_id' => null,
            'facebook_id' => 1,
            'api_key' => 'KOsc08Hf1cOLUpbt1NHwoTwA2BnCIUSZ',
            'token' => 'xl0iV04yK5gtabGX4v9es6eWR93BxkTg',
            'created' => '2017-11-20 22:39:17',
            'modified' => '2017-11-20 22:39:17',
        ],
        [
            'id' => self::USER_WITHOUT_EVENTS, // and self::SUBSCRIBED_USER_WITH_ASSOCIATION
            'name' => 'User without API key',
            'role' => 'Lorem ipsum dolor ',
            'bio' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
            'email' => 'user2@example.com',
            'password' => '',
            'mailing_list_id' => 1,
            'facebook_id' => 1,
            'api_key' => null,
            'token' => '7xVY2FVjrf3v3TJMVS2u1ROajFTxfMIZ',
            'created' => '2017-11-20 22:39:17',
            'modified' => '2017-11-20 22:39:17',
        ],
        [
            'id' => self::USER_NOT_SUBSCRIBED,
            'name' => 'User with old-style password hash',
            'role' => 'user',
            'bio' => '',
            'email' => 'user3@example.com',
            'password' => '',
            'mailing_list_id' => null,
            'facebook_id' => 1,
            'api_key' => null,
            'token' => '17d861274d3fe22d7225e84e6d882ea2',
            'created' => '2017-11-20 22:39:17',
            'modified' => '2017-11-20 22:39:17',
        ],
    ];

    /**
     * Init method
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        $password = 'password';

        // Add default-style password hashes
        $hasher = new DefaultPasswordHasher();
        $hash = $hasher->hash($password);
        for ($n = 0; $n <= 1; $n++) {
            $this->records[$n]['password'] = $hash;
        }

        // Add old-style password hash
        $hasher = new LegacyPasswordHasher();
        $hash = $hasher->hash($password);
        $this->records[2]['password'] = $hash;
    }
}
