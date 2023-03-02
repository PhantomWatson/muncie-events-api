<?php
namespace App\View\Schema;

use App\Model\Entity\MailingList;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use JsonApi\View\Schema\EntitySchema;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

class MailingListSchema extends EntitySchema
{
    protected $resourceType = 'subscriptions';

    public function getType(): string
    {
        return 'subscriptions';
    }

    /**
     * Returns the subscription's ID
     *
     * @param MailingList $subscription Mailing list subscription entity
     * @return string
     */
    public function getId($subscription): string
    {
        return (string)$subscription->get('id');
    }

    /**
     * Returns the attributes for this entity for API output
     *
     * @param MailingList $subscription Mailing list subscription entity
     * @param array|null $fieldKeysFilter Field keys filter
     * @return array
     */
    public function getAttributes($subscription, array $fieldKeysFilter = null): array
    {
        $retval = [
            'email' => $subscription->email,
            'all_categories' => $subscription->all_categories,
            'weekly' => $subscription->weekly,
            'daily_sun' => $subscription->daily_sun,
            'daily_mon' => $subscription->daily_mon,
            'daily_tue' => $subscription->daily_tue,
            'daily_wed' => $subscription->daily_wed,
            'daily_thu' => $subscription->daily_thu,
            'daily_fri' => $subscription->daily_fri,
            'daily_sat' => $subscription->daily_sat,
        ];

        return $retval;
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
        // If "all_categories" is true, display associations with every category
        if ($subscription->all_categories) {
            $categoriesTable = TableRegistry::getTableLocator()->get('Categories');
            $categories = $categoriesTable->find()->all();
        } else {
            $categories = $subscription->categories;
        }

        return ['categories' => [self::DATA => $categories]];
    }
}
