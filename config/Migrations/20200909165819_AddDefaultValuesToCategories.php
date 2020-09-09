<?php
// @codingStandardsIgnoreFile
use Migrations\AbstractMigration;

class AddDefaultValuesToCategories extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change()
    {
        $table = $this->table('categories');
        $table->changeColumn('slug', 'string', ['default' => '']);
        $table->changeColumn('weight', 'integer', ['default' => 0]);
        $table->update();
    }
}
