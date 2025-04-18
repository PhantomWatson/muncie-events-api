<?php
namespace App\View\Schema;

use App\Model\Entity\Page;
use Cake\ORM\Entity;
use JsonApi\View\Schema\EntitySchema;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

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
    public function getAttributes($entity, $fieldKeysFilter = null): array
    {
        return [
            'title' => $entity->title,
            'body' => $entity->body,
        ];
    }

    /**
     * Returns the relationships that this entity has with any other API-gettable entities
     *
     * @param Entity $resource Entity
     * @param ContextInterface $context
     * @return array
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        return [];
    }
}
