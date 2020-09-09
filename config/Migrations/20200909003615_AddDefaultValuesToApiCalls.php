<?php
// @codingStandardsIgnoreFile
use Migrations\AbstractMigration;

class AddDefaultValuesToApiCalls extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change()
    {
        $table = $this->table('api_calls');
        $table->changeColumn('user_id', 'integer', ['default' => 0]);
        $table->changeColumn('url', 'string', ['default' => '']);
        $table->changeColumn('created', 'datetime', [
            'default' => null,
            'null' => true,
        ]);
        $table->update();
    }
}
