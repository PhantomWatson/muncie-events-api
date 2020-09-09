<?php
// @codingStandardsIgnoreFile
use Migrations\AbstractMigration;

class AddDefaultValuesToCategoriesMailingList extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change()
    {
        $table = $this->table('categories_mailing_list');
        $table->changeColumn('mailing_list_id', 'integer', ['default' => 0]);
        $table->changeColumn('category_id', 'integer', ['default' => 0]);
        $table->update();
    }
}
