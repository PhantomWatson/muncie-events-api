<?php
namespace App\View\Schema;

use App\Model\Entity\Page;
use JsonApi\View\Schema\EntitySchema;

class PageSchema extends EntitySchema
{
    /**
     * Returns the title of the page, which is effectively an ID
     *
     * @param Page $page Page entity
     * @return int
     */
    public function getId($page)
    {
        return $page->id;
    }

    /**
     * Returns the attributes for this entity for API output
     *
     * @param Page $page Entity
     * @return array
     */
    public function getAttributes($page)
    {
        return [
            'title' => $page->title,
            'body' => $page->body
        ];
    }

    /**
     * Returns the relationships that this entity has with any other API-gettable entities
     *
     * @param \Cake\ORM\Entity $entity Entity
     * @param array $includeRelationships Names of relationships to include
     * @return array
     */
    public function getRelationships($entity, array $includeRelationships = [])
    {
        return [];
    }
}
