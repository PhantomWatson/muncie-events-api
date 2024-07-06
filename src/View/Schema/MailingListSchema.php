<?php
namespace App\View\Schema;

use App\Model\Entity\MailingList;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use JsonApi\View\Schema\EntitySchema;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

class MailingListSchema extends EntitySchema
{
    public $resourceType = 'subscriptions';

    public function getType(): string
    {
        return 'subscriptions';
    }

    /**
     * Returns the subscription's ID
     *
     * @param MailingList $resource Mailing list subscription entity
     * @return string
     */
    public function getId($resource): string
    {
        return (string)$resource->get('id');
    }

    /**
     * Returns the attributes for this entity for API output
     *
     * @param MailingList $resource Mailing list subscription entity
     * @param array|ContextInterface|null $context Field keys filter
     * @return array
     */
    public function getAttributes($resource, array|ContextInterface $context = null): array
    {
        $retval = [
            'email' => $resource->email,
            'all_categories' => $resource->all_categories,
            'weekly' => $resource->weekly,
            'daily_sun' => $resource->daily_sun,
            'daily_mon' => $resource->daily_mon,
            'daily_tue' => $resource->daily_tue,
            'daily_wed' => $resource->daily_wed,
            'daily_thu' => $resource->daily_thu,
            'daily_fri' => $resource->daily_fri,
            'daily_sat' => $resource->daily_sat,
        ];

        return $retval;
    }

    /**
     * Returns the relationships that this entity has with any other API-gettable entities
     *
     * @param MailingList $resource Entity
     * @param ContextInterface $context
     * @return array
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        // If "all_categories" is true, display associations with every category
        if ($resource->all_categories) {
            $categoriesTable = TableRegistry::getTableLocator()->get('Categories');
            $categories = $categoriesTable->find()->all();
        } else {
            $categories = $resource->categories;
        }

        return ['categories' => ['data' => $categories]];
    }
}
