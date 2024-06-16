<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddTags extends AbstractMigration
{
    public function up(): void
    {
        $tagsTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Tags');
        if ($tagsTable->exists([])) {
            echo 'Tags table is not empty' . PHP_EOL;
            return;
        }

        $tagsQuery = file_get_contents(__DIR__ . '/tags.sql');
        $this->execute($tagsQuery);
    }

    public function down(): void
    {

    }
}
