<?php
namespace App\View\Schema;

use App\Model\Entity\Tag;
use Cake\ORM\Entity;
use JsonApi\View\Schema\EntitySchema;

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
     * @param Tag $tag Tag entity
     * @param array|null $fieldKeysFilter Field keys filter
     * @return array
     */
    public function getAttributes($tag, array $fieldKeysFilter = null): array
    {
        $retval = ['name' => $tag->name];
        if (isset($tag->selectable)) {
            $retval['selectable'] = $tag->selectable;
        }

        if (isset($tag->count)) {
            $retval['upcomingEventCount'] = $tag->count;
        }

        return $retval;
    }

    /**
     * Returns the relationships that this entity has with any other API-gettable entities
     *
     * @param Tag $tag Tag entity
     * @param bool $isPrimary Is primary flag
     * @param array $includeRelationships Names of relationships to include
     * @return array
     */
    public function getRelationships($tag, bool $isPrimary, array $includeRelationships): ?array
    {
        if (isset($tag->children)) {
            return ['children' => [self::DATA => $tag->children]];
        }

        if (isset($tag->events)) {
            return ['events' => [self::DATA => $tag->events]];
        }

        return [];
    }
}
