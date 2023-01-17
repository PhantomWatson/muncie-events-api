<?php
use Migrations\AbstractMigration;

class AddEnabledToMailingList extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('mailing_list');
        $table->addColumn(
            'enabled',
            'boolean',
            [
                'after' => 'email',
                'default' => true,
            ]
        )->update();
    }
}
