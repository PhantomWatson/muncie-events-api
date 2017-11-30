<?php
use Migrations\AbstractMigration;

class AddApiKeyToUsers extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('users');
        $table->addColumn('api_key', 'string', [
            'default' => null,
            'limit' => 32,
            'null' => true,
            'after' => 'facebook_id'
        ]);
        $table->update();
    }
}
