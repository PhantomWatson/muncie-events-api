<?php
namespace App\View\Schema;

use App\Model\Entity\Tag;
use Cake\ORM\Entity;
use JsonApi\View\Schema\EntitySchema;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

class TagSchema extends EntitySchema
{
    public function getType(): string
    {
        return 'tags';
    }

    /**
     * Returns the tag's ID
     *
     * @param Entity $entity Tag entity
     * @return string
     */
    public function getId($entity): string
    {
        return (string)$entity->get('id');
    }

    /**
     * Returns the attributes for this entity for API output
     *
     * @param Tag $resource Tag entity
     * @param ContextInterface $context
     * @return array
     */
    public function getAttributes($resource, ContextInterface $context): iterable
    {
        $retval = ['name' => $resource->name];
        if (isset($resource->selectable)) {
            $retval['selectable'] = $resource->selectable;
        }

        if (isset($resource->count)) {
            $retval['upcomingEventCount'] = $resource->count;
        }

        return $retval;
    }

    /**
     * Returns the relationships that this entity has with any other API-gettable entities
     *
     * @param Tag $resource Entity
     * @param ContextInterface $context
     * @return array
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        if (isset($resource->children)) {
            return ['children' => ['data' => $resource->children]];
        }

        if (isset($resource->events)) {
            return ['events' => ['data' => $resource->events]];
        }

        return [];
    }
}
