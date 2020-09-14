<?php
// @codingStandardsIgnoreFile
use Migrations\AbstractMigration;

class AddDefaultValuesToEventsImages extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change()
    {
        $table = $this->table('events_images');
        $table->changeColumn('image_id', 'integer', ['default' => 0]);
        $table->changeColumn('event_id', 'integer', ['default' => 0]);
        $table->update();
    }
}
