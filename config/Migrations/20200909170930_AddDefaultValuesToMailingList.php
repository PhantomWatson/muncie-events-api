<?php
// @codingStandardsIgnoreFile
use Migrations\AbstractMigration;

class AddDefaultValuesToMailingList extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change()
    {
        $table = $this->table('mailing_list');
        $table->changeColumn('email', 'string', ['default' => '']);
        $table->update();
    }
}
