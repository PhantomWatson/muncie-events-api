<?php
namespace App\View\Schema;

use App\Model\Entity\Category;
use App\Model\Table\EventsTable;
use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use JsonApi\View\Schema\EntitySchema;

class CategorySchema extends EntitySchema
{
    public function getType(): string
    {
        return 'categories';
    }

    /**
     * Returns the category's ID
     *
     * @param Entity $category Category entity
     * @return string
     */
    public function getId($category): string
    {
        return (string)$category->get('id');
    }

    /**
     * Returns the attributes for this entity for API output
     *
     * @param Category $category Category entity
     * @param array|null $fieldKeysFilter Field keys filter
     * @return array
     */
    public function getAttributes($category, array $fieldKeysFilter = null): array
    {
        $siteBaseUrl = Configure::read('mainSiteBaseUrl');
        $iconBaseUrl = Configure::read('categoryIconBaseUrl');
        $iconFilename = mb_strtolower(str_replace(' ', '_', $category->name)) . '.svg';
        $retval = [
            'name' => $category->name,
            'url' => $siteBaseUrl . '/' . $category->slug,
            'icon' => [
                'svg' => $iconBaseUrl . $iconFilename,
            ],
        ];

        if (!$category->noEventCount) {
            /** @var EventsTable $eventsTable */
            $eventsTable = TableRegistry::getTableLocator()->get('Events');
            $upcomingEventCount = $eventsTable->getCategoryUpcomingEventCount($category->id);
            $retval['upcomingEventCount'] = $upcomingEventCount;
        }

        return $retval;
    }

    /**
     * Returns the relationships that this entity has with any other API-gettable entities
     *
     * @param Entity $entity Entity
     * @param bool $isPrimary Is primary flag
     * @param array $includeRelationships Names of relationships to include
     * @return array
     */
    public function getRelationships($entity, bool $isPrimary, array $includeRelationships): ?array
    {
        return [];
    }
}
