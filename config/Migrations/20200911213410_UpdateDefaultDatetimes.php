<?php
// @codingStandardsIgnoreFile
use Migrations\AbstractMigration;

class UpdateDefaultDatetimes extends AbstractMigration
{
    private function getDatetimeColNames()
    {
        return [
            'event_series',
            'events',
            'events_images',
            'images',
            'mailing_list',
            'mailing_list_log',
            'tags',
            'users',
        ];
    }

    /**
     * Up Method.
     *
     * @return void
     */
    public function up()
    {
        $table = $this->table('events');
        $table->changeColumn(
            'date',
            'date',
            [
                'default' => null,
                'null' => true,
            ]
        );
        $table->update();

        foreach ($this->getDatetimeColNames() as $tableName) {
            $table = $this->table($tableName);
            foreach (['created', 'modified'] as $column) {
                // Skip 'modified' column for the two tables that don't have that column
                if ($column == 'modified' && in_array($tableName, ['mailing_list_log', 'tags'])) {
                    continue;
                }

                $table->changeColumn(
                    $column,
                    'datetime',
                    [
                        'default' => null,
                        'null' => true,
                    ]
                );
            }
            $table->update();
        }
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down()
    {
        $table = $this->table('events');
        $table->changeColumn(
            'date',
            'date',
            [
                'default' => '1969-12-31',
                'null' => false,
            ]
        );
        $table->update();

        foreach ($this->getDatetimeColNames() as $tableName) {
            $table = $this->table($tableName);
            foreach (['created', 'modified'] as $column) {
                // Skip 'modified' column for the two tables that don't have that column
                if ($column == 'modified' && in_array($tableName, ['mailing_list_log', 'tags'])) {
                    continue;
                }

                $table->changeColumn(
                    $column,
                    'datetime',
                    [
                        'default' => '1969-12-31 23:59:59',
                        'null' => false,
                    ]
                );
            }
            $table->update();
        }
    }
}
