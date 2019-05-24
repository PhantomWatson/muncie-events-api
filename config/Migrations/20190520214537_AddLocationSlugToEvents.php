<?php
// @codingStandardsIgnoreFile

use Migrations\AbstractMigration;

class AddLocationSlugToEvents extends AbstractMigration
{
    /**
     * Adds the location_slug column to the events table
     *
     * @return void
     */
    public function change()
    {
        $table = $this->table('events');

        $table
            ->addColumn(
                'location_slug',
                'string',
                [
                    'after' => 'location_details',
                    'limit' => 50,
                    'null' => false
                ]
            )
            ->update();
    }
}
