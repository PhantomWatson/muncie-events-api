<?php
namespace App\View\Schema;

use App\Model\Entity\Category;
use App\Model\Table\EventsTable;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use JsonApi\View\Schema\EntitySchema;

class CategorySchema extends EntitySchema
{
    /**
     * Returns the category's ID
     *
     * @param \Cake\ORM\Entity $entity Category entity
     * @return string
     */
    public function getId($entity): string
    {
        return (string)$entity->get('id');
    }

    /**
     * Returns the attributes for this entity for API output
     *
     * @param Category $entity Entity
     * @param array|null $fieldKeysFilter Field keys filter
     * @return array
     */
    public function getAttributes($entity, array $fieldKeysFilter = null): array
    {
        /** @var EventsTable $eventsTable */
        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        $categoryId = $entity->id;
        $upcomingEventCount = $eventsTable->getCategoryUpcomingEventCount($categoryId);
        $baseUrl = Configure::read('mainSiteBaseUrl');

        return [
            'name' => $entity->name,
            'upcomingEventCount' => $upcomingEventCount,
            'url' => $baseUrl . '/category/' . $entity->slug
        ];
    }

    /**
     * Returns the relationships that this entity has with any other API-gettable entities
     *
     * @param \Cake\ORM\Entity $entity Entity
     * @param bool $isPrimary Is primary flag
     * @param array $includeRelationships Names of relationships to include
     * @return array
     */
    public function getRelationships($entity, bool $isPrimary, array $includeRelationships): ?array
    {
        return [];
    }
}
