<?php
namespace App\View\Schema;

use App\Model\Entity\Image;
use Cake\Core\Configure;
use Cake\ORM\Entity;
use JsonApi\View\Schema\EntitySchema;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

class ImageSchema extends EntitySchema
{
    public function getType(): string
    {
        return 'images';
    }

    /**
     * Returns the image's ID
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
     * @param Image $resource Image entity
     * @param array|ContextInterface|null $context Field keys filter
     * @return array
     */
    public function getAttributes($resource, array|ContextInterface $context = null): array
    {
        $baseUrl = Configure::read('eventImageBaseUrl');

        return [
            'tiny_url' => $baseUrl . 'tiny/' . $resource->filename,
            'small_url' => $baseUrl . 'small/' . $resource->filename,
            'full_url' => $baseUrl . 'full/' . $resource->filename,
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
