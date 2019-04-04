<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Entity\Category;
use App\Model\Entity\MailingList;
use App\Model\Table\MailingListTable;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\ORM\TableRegistry;

/**
 * Class MailingListController
 * @package App\Controller\V1
 * @property MailingListTable $MailingList
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

        $this->checkForExistingSubscription($email);
        $this->checkForMissingParams();

        // Set up entity data
        $entityData = [
            'email' => $email,
            'new_subscriber' => true,
            'all_categories' => (bool)$this->request->getData('all_categories'),
            'categories' => $this->getSelectedCategories(),
            'weekly' => (bool)$this->request->getData('weekly')
        ];
        $daily = (bool)$this->request->getData('daily');
        $days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        foreach ($days as $day) {
            $key = 'daily_' . $day;
            $entityData[$key] = $daily || (bool)$this->request->getData($key);
        }

        // Save
        $mailingListTable = TableRegistry::getTableLocator()->get('MailingList');
        /** @var MailingList $newSubscription */
        $newSubscription = $mailingListTable->newEntity($entityData);
        if (!$mailingListTable->save($newSubscription)) {
            throw new BadRequestException(
                'There was an error subscribing you to the mailing list. ' .
                'Please try again or contact an administrator for assistance.'
            );
        }

        $this->associateUserWithSubscription($newSubscription);

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

    /**
     * Throws errors if required parameters are missing
     *
     * @return void
     */
    private function checkForMissingParams()
    {
        if (empty($this->request->getData('email'))) {
            throw new BadRequestException('Email address must be provided');
        }
        $allCategories = $this->request->getData('all_categories');
        $categoryIds = $this->request->getData('category_ids');
        if ($allCategories === null && $categoryIds === null) {
            throw new BadRequestException('Either "all categories" or at least one individual category must be selected.');
        }
        $weekly = (bool)$this->request->getData('weekly');
        $daily = (bool)$this->request->getData('daily');
        $days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        if (!$weekly && !$daily) {
            $individualDaysSelected = false;
            foreach ($days as $day) {
                $isSelected = (bool)$this->request->getData('daily_' . $day);
                $individualDaysSelected = $individualDaysSelected || $isSelected;
            }
            if (!$individualDaysSelected) {
                throw new BadRequestException('Either weekly, daily, or at least one individual day must be selected');
            }
        }
    }

    /**
     * Throws an exception if the provided email address is already subscribed
     *
     * @param string $email Email address
     * @return void
     * @throws ForbiddenException
     */
    private function checkForExistingSubscription($email)
    {
        $mailingListTable = TableRegistry::getTableLocator()->get('MailingList');
        $subscriptionExists = $mailingListTable->exists(['email' => $email]);
        if ($subscriptionExists) {
            throw new ForbiddenException(sprintf(
                'The email address %s is already subscribed to the mailing list.',
                $email
            ));
        }
    }

    /**
     * Associates the current user or user with matching email address with the provided subscription
     *
     * @param MailingList $newSubscription Subscription record
     * @return void
     */
    private function associateUserWithSubscription($newSubscription)
    {
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $user = $this->tokenUser
            ? $this->tokenUser
            : $usersTable
                ->find()
                ->where(['email' => $newSubscription->email])
                ->first();
        if ($user) {
            $usersTable->patchEntity($user, ['mailing_list_id' => $newSubscription->id]);
            $usersTable->save($user);
        }
    }

    /**
     * GET /mailing-list/subscription endpoint
     *
     * @return void
     * @throws ForbiddenException
     * @throws MethodNotAllowedException
     */
    public function subscriptionStatus()
    {
        $this->request->allowMethod('get');

        if (!$this->tokenUser) {
            throw new ForbiddenException('User token missing. You must be logged in to view subscription status');
        }

        $condition = $this->tokenUser->mailing_list_id
            ? ['id' => $this->tokenUser->mailing_list_id]
            : ['email' => $this->tokenUser->email];
        $subscription = $this->MailingList
            ->find()
            ->where($condition)
            ->contain(['Categories'])
            ->first();

        $this->set([
            '_entities' => ['Category', 'MailingList'],
            '_serialize' => ['subscription'],
            'subscription' => $subscription
        ]);
    }
}
