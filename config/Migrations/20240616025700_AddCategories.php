<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class AddCategories extends AbstractMigration
{
    public function up(): void
    {
        $categoriesTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Categories');

        // IDs may not actually be saved
        $categories = [
            ['id' => '8','name' => 'Music','slug' => 'music','weight' => '1'],
            ['id' => '9','name' => 'Art','slug' => 'art','weight' => '2'],
            ['id' => '10','name' => 'Theater','slug' => 'theater','weight' => '4'],
            ['id' => '11','name' => 'Film','slug' => 'film','weight' => '5'],
            ['id' => '12','name' => 'Activism','slug' => 'activism','weight' => '3'],
            ['id' => '13','name' => 'General Events','slug' => 'general','weight' => '-1'],
            ['id' => '24','name' => 'Education','slug' => 'education','weight' => '7'],
            ['id' => '25','name' => 'Government','slug' => 'government','weight' => '8'],
            ['id' => '26','name' => 'Sports','slug' => 'sports','weight' => '6'],
            ['id' => '27','name' => 'Religion','slug' => 'religion','weight' => '9']
        ];
        foreach ($categories as $categoryData) {
            $categoryData['id'] += 100;
            if ($categoriesTable->exists(['name' => $categoryData['name']])) {
                echo "{$categoryData['name']} already added" . PHP_EOL;
                continue;
            }
            $category = $categoriesTable->newEntity($categoryData);
            if ($categoriesTable->save($category)) {
                echo "Added {$category->name}" . PHP_EOL;
            } else {
                echo "Error adding {$category->name}" . PHP_EOL;
            }
        }
        echo 'Done' . PHP_EOL;
    }

    public function down(): void
    {

    }
}
