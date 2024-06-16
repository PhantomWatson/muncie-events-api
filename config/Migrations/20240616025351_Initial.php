<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Initial extends AbstractMigration
{
    public $autoId = false;

    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     * @return void
     */
    public function up(): void
    {
        $this->table('api_calls')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('user_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('url', 'string', [
                'default' => '',
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('categories')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('name', 'string', [
                'default' => '',
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('slug', 'string', [
                'default' => '',
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('weight', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('categories_mailing_list')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('mailing_list_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('category_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('event_series')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('title', 'string', [
                'default' => '',
                'limit' => 200,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('published', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('events')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('title', 'string', [
                'default' => '',
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => 16777215,
                'null' => true,
            ])
            ->addColumn('location', 'string', [
                'default' => '',
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('location_details', 'string', [
                'default' => '',
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('location_slug', 'string', [
                'default' => '',
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('address', 'string', [
                'default' => '',
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('category_id', 'smallinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('series_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('time_start', 'time', [
                'default' => '00:00:00',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('time_end', 'time', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('age_restriction', 'string', [
                'default' => '',
                'limit' => 30,
                'null' => false,
            ])
            ->addColumn('cost', 'string', [
                'default' => '',
                'limit' => 200,
                'null' => false,
            ])
            ->addColumn('source', 'string', [
                'default' => '',
                'limit' => 200,
                'null' => false,
            ])
            ->addColumn('published', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('approved_by', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'user_id',
                ],
                [
                    'name' => 'person_id',
                ]
            )
            ->addIndex(
                [
                    'category_id',
                ],
                [
                    'name' => 'category_id',
                ]
            )
            ->create();

        $this->table('events_images')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('image_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('event_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('weight', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('caption', 'string', [
                'default' => '',
                'limit' => 300,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('events_tags')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('event_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('tag_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('images')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('filename', 'string', [
                'default' => '',
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('is_flyer', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('mailing_list')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('email', 'string', [
                'default' => '',
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('enabled', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('all_categories', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('weekly', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('daily_sun', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('daily_mon', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('daily_tue', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('daily_wed', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('daily_thu', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('daily_fri', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('daily_sat', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('new_subscriber', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('processed_daily', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('processed_weekly', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('mailing_list_log')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('recipient_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('flavor', 'char', [
                'default' => '',
                'limit' => 1,
                'null' => false,
            ])
            ->addColumn('result', 'tinyinteger', [
                'default' => '0',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('testing', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('tags')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('parent_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('lft', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('rght', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => '',
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('listed', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('selectable', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('users')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('name', 'string', [
                'default' => '',
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('role', 'string', [
                'default' => 'user',
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('bio', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('email', 'string', [
                'default' => '',
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('password', 'string', [
                'default' => '',
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('mailing_list_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('facebook_id', 'biginteger', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('api_key', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('token', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('reset_password_hash', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'email',
                ],
                [
                    'name' => 'uq',
                    'unique' => true,
                ]
            )
            ->create();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     * @return void
     */
    public function down(): void
    {
        $this->table('api_calls')->drop()->save();
        $this->table('categories')->drop()->save();
        $this->table('categories_mailing_list')->drop()->save();
        $this->table('event_series')->drop()->save();
        $this->table('events')->drop()->save();
        $this->table('events_images')->drop()->save();
        $this->table('events_tags')->drop()->save();
        $this->table('images')->drop()->save();
        $this->table('mailing_list')->drop()->save();
        $this->table('mailing_list_log')->drop()->save();
        $this->table('tags')->drop()->save();
        $this->table('users')->drop()->save();
    }
}
