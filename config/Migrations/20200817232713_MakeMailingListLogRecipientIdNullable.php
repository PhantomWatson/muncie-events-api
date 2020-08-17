<?php
use Migrations\AbstractMigration;

class MakeMailingListLogRecipientIdNullable extends AbstractMigration
{
    /**
     * Up method
     *
     * @return void
     */
    public function up()
    {
        $table = $this->table('mailing_list_log');

        $table
            ->changeColumn(
                'recipient_id',
                'integer',
                ['null' => true]
            )
            ->update();
    }

    /**
     * Down method
     *
     * @return void
     */
    public function down()
    {
        $table = $this->table('mailing_list_log');

        $table
            ->changeColumn(
                'recipient_id',
                'integer',
                ['null' => false]
            )
            ->update();
    }
}
