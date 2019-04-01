<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Entity\Category;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;

/**
 * Class MailingListController
 * @package App\Controller\V1
 */
class MailingListController extends ApiController
{
    /**
     * /mailing-list/subscribe endpoint
     *
     * @return void
     * @throws \Exception
     * @throws BadRequestException
     * @throws ForbiddenException
     */
    public function subscribe()
    {
        $this->request->allowMethod('post');

        // Clean up email address
        $email = $this->request->getData('email');
        $email = trim($email);
        $email = mb_strtolower($email);

        // Throw error if email address is already subscribed
        $mailingListTable = TableRegistry::getTableLocator()->get('MailingList');
        $subscriptionExists = $mailingListTable->exists(['email' => $email]);
        if ($subscriptionExists) {
            throw new ForbiddenException(sprintf(
                'The email address %s is already subscribed to the mailing list.',
                $email
            ));
        }

        // Throw errors for missing parameters
        if (empty($email)) {
            throw new BadRequestException('Email address must be provided');
        }
        $allCategories = $this->request->getData('all_categories');
        $categoryIds = $this->request->getData('category_ids');
        if ($allCategories === null && $categoryIds === null) {
            throw new BadRequestException('Either all_categories or category_ids must be provided.');
        }

        // Set up entity data
        $entityData = [
            'email' => $email,
            'new_subscriber' => true,
            'all_categories' => (bool)$this->request->getData('all_categories'),
            'categories' => $this->getSelectedCategories(),
            'weekly' => (bool)$this->request->getData('weekly')
        ];
        $days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        $isDailyAllDays = (bool)$this->request->getData('daily');
        foreach ($days as $day) {
            $key = 'daily_' . $day;
            $entityData[$key] = $isDailyAllDays || (bool)$this->request->getData($key);
        }

        // Save
        $newSubscription = $mailingListTable->newEntity($entityData);
        if (!$mailingListTable->save($newSubscription)) {
            throw new BadRequestException(
                'There was an error subscribing you to the mailing list. ' .
                'Please try again or contact an administrator for assistance.'
            );
        }

        // Associate current user if logged in, or user with matching email address if not logged in
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $user = $this->tokenUser
            ? $this->tokenUser
            : $usersTable
                ->find()
                ->where(['email' => $email])
                ->first();
        if ($user) {
            $usersTable->patchEntity($user, ['mailing_list_id' => $newSubscription->id]);
            $usersTable->save($user);
        }

        // Return response
        $this->response = $this->response->withStatus(204, 'No Content');

        /* Bypass JsonApi plugin to render blank response,
         * as required by the JSON API standard (https://jsonapi.org/format/#crud-creating-responses-204) */
        $this->viewBuilder()->setClassName('Json');
        $this->set('_serialize', true);
    }

    /**
     * Returns an array of Category entities selected in the current request, or an empty array if "all" is selected
     *
     * Explanation: If a user selects "all categories", that bypasses category filters entirely instead of individually
     * associating their subscription with each category.
     *
     * @return Category[]
     */
    private function getSelectedCategories()
    {
        $isAllCategories = (bool)$this->request->getData('all_categories');
        if ($isAllCategories) {
            return [];
        }

        $categoryIds = $this->request->getData('category_ids');
        if (!is_array($categoryIds)) {
            throw new BadRequestException('Invalid value provided for category_ids');
        }

        $categoriesTable = TableRegistry::getTableLocator()->get('Categories');
        $categories = [];
        foreach ($categoryIds as $categoryId) {
            if (!$categoriesTable->exists(['id' => $categoryId])) {
                throw new BadRequestException("Invalid event category ID: $categoryId");
            }
            $categories[] = $categoriesTable->get($categoryId);
        }

        return $categories;
    }
}
