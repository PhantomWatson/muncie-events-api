<?php
namespace App\View\Schema;

use App\Model\Entity\Tag;
use JsonApi\View\Schema\EntitySchema;

class TagSchema extends EntitySchema
{
    /**
     * Returns the tag's ID
     *
     * @param \Cake\ORM\Entity $entity Tag entity
     * @return int
     */
    public function getId($entity)
    {
        return $entity->get('id');
    }

    /**
     * Returns the attributes for this entity for API output
     *
     * @param Tag $tag Tag entity
     * @return array
     */
    public function getAttributes($tag)
    {
        $retval = [
            'name' => $tag->name,
            'selectable' => $tag->selectable
        ];

        if (isset($tag->count)) {
            $retval['upcomingEventCount'] = $tag->count;
        }

        return $retval;
    }

    /**
     * Returns the relationships that this entity has with any other API-gettable entities
     *
     * @param Tag $tag Tag entity
     * @param array $includeRelationships Names of relationships to include
     * @return array
     */
    public function getRelationships($tag, array $includeRelationships = [])
    {
        if (isset($tag->children)) {
            return ['children' => [self::DATA => $tag->children]];
        }

        return [];
    }
}
