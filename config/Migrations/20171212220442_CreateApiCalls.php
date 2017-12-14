<?php
namespace App;

use Migrations\AbstractMigration;

class CreateApiCalls extends AbstractMigration
{
    /**
     * Up method
     *
     * @return void
     */
    public function up()
    {
        $this->table('api_calls')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('url', 'text', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->create();
    }

    /**
     * Down method
     *
     * @return void
     */
    public function down()
    {
        $this->dropTable('api_calls');
    }
}
