<?php
namespace App\View\Schema;

use App\Model\Entity\Page;
use Cake\ORM\Entity;
use JsonApi\View\Schema\EntitySchema;

class PageSchema extends EntitySchema
{
    public function getType(): string
    {
        return 'pages';
    }

    /**
     * Returns the title of the page, which is effectively an ID
     *
     * @param Page $entity Page entity
     * @return string
     */
    public function getId($entity): string
    {
        return (string)$entity->id;
    }

    /**
     * Returns the attributes for this entity for API output
     *
     * @param Page $entity Page entity
     * @param array|null $fieldKeysFilter Field keys filter
     * @return array
     */
    public function getAttributes($entity, array $fieldKeysFilter = null): array
    {
        return [
            'title' => $entity->title,
            'body' => $entity->body,
        ];
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
