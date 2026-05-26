<?php
namespace App\View\Schema;

use App\Model\Entity\User;
use JsonApi\View\Schema\EntitySchema;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

class UserSchema extends EntitySchema
{
    public function getType(): string
    {
        return 'users';
    }

    /**
     * Returns the user's ID
     *
     * @param User $resource User entity
     * @return string
     */
    public function getId($resource): string
    {
        return (string)$resource->get('id');
    }

    /**
     * Returns the attributes for this entity for API output
     *
     * @param User $resource User entity
     * @param ContextInterface $context
     * @return array
     */
    public function getAttributes($resource, ContextInterface $context): iterable
    {
        $attributes = ['name' => $resource->name];
        if ($resource->email !== null) {
            $attributes['email'] = $resource->email;
        }
        if ($resource->token !== null) {
            $attributes['token'] = $resource->token;
        }

        return $attributes;
    }

    /**
     * Returns the relationships that this entity has with any other API-gettable entities
     *
     * @param User $resource User entity
     * @param ContextInterface $context
     * @return array
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        return [];
    }
}
