<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Entity\MailingList;
use App\Model\Table\MailingListTable;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\InternalErrorException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * Class MailingListController
 * @package App\Controller\V1
 * @property \App\Model\Table\MailingListTable $MailingList
 */
class MailingListController extends ApiController
{
    private $days = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

    /**
     * Initialization hook method
     *
     * @return void
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Auth->allow(['subscribe']);
    }

    /**
     * /mailing-list/subscribe endpoint
     *
     * @return void
     * @throws Exception
     * @throws BadRequestException
     * @throws ForbiddenException
     */
    public function subscribe()
    {
        $this->request->allowMethod('post');

        $email = $this->getCleanEmail();
        $this->checkForExistingSubscription($email);
        $this->checkForMissingParams();

        // Save
        $entityData = $this->getSubscriptionDataFromRequest();
        $entityData['new_subscriber'] = true;
        /** @var MailingList $newSubscription */
        $newSubscription = $this->MailingList->newEntity($entityData);
        if (!$this->MailingList->save($newSubscription)) {
            throw new BadRequestException(
                'There was an error subscribing you to the mailing list. ' .
                'Please try again or contact an administrator for assistance.'
            );
        }

        $this->associateUserWithSubscription($newSubscription);

        $this->set204Response();
    }

    /**
     * Returns an array of Category IDs selected in the current request, or an empty array if "all" is selected
     *
     * Explanation: If a user selects "all categories", that bypasses category filters entirely instead of individually
     * associating their subscription with each category.
     *
     * @return array
     * @throws \Cake\Http\Exception\BadRequestException
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

        // Check that all category IDs are valid
        $categoriesTable = TableRegistry::getTableLocator()->get('Categories');
        foreach ($categoryIds as $categoryId) {
            if (!$categoriesTable->exists(['id' => $categoryId])) {
                throw new BadRequestException("Invalid event category ID: $categoryId");
            }
        }

        return ['_ids' => $categoryIds];
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
     * @param MailingList $subscription Subscription record
     * @return void
     */
    private function associateUserWithSubscription($subscription)
    {
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $user = $this->tokenUser
            ? $this->tokenUser
            : $usersTable
                ->find()
                ->where(['email' => $subscription->email])
                ->first();
        if ($user) {
            $usersTable->patchEntity($user, ['mailing_list_id' => $subscription->id]);
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
            throw new ForbiddenException('User token missing. You must be logged in to view subscription status.');
        }

        $this->set([
            '_entities' => ['Category', 'MailingList'],
            '_serialize' => ['subscription'],
            'subscription' => $this->getCurrentUserSubscription(),
        ]);
    }

    /**
     * PUT /mailing-list/subscription endpoint
     *
     * @return void
     * @throws ForbiddenException
     * @throws MethodNotAllowedException
     */
    public function subscriptionUpdate()
    {
        $this->request->allowMethod('put');

        if (!$this->tokenUser) {
            throw new ForbiddenException('User token missing. You must be logged in to update subscription status.');
        }

        $subscription = $this->getCurrentUserSubscription();
        if (!$subscription) {
            throw new ForbiddenException('Cannot update subscription: You are not currently subscribed');
        }

        $this->checkForMissingParams();

        // Save
        $entityData = $this->getSubscriptionDataFromRequest();
        $entityData['enabled'] = true;
        $this->MailingList->patchEntity($subscription, $entityData);
        if (!$this->MailingList->save($subscription)) {
            throw new BadRequestException(
                'There was an error subscribing you to the mailing list. ' .
                'Please try again or contact an administrator for assistance.'
            );
        }

        $this->associateUserWithSubscription($subscription);

        $this->set204Response();
    }

    /**
     * Returns the subscription associated (by ID or email) with the current user, or NULL if none is found
     *
     * @return MailingList|null
     */
    private function getCurrentUserSubscription()
    {
        $condition = $this->tokenUser->mailing_list_id
            ? ['id' => $this->tokenUser->mailing_list_id]
            : ['email' => $this->tokenUser->email];

        /** @var MailingList $subscription */
        $subscription = $this->MailingList
            ->find()
            ->where($condition)
            ->contain(['Categories'])
            ->first();

        return $subscription;
    }

    /**
     * Returns a trimmed and lowercased version of the provided email address
     *
     * @return string
     */
    private function getCleanEmail()
    {
        $email = (string)$this->request->getData('email');
        $email = trim($email);

        return mb_strtolower($email);
    }

    /**
     * Returns an array of data derived from the request to be saved to a new or updated mailing list subscription
     *
     * @return array
     */
    private function getSubscriptionDataFromRequest()
    {
        $data = [
            'email' => $this->getCleanEmail(),
            'all_categories' => (bool)$this->request->getData('all_categories'),
            'categories' => $this->getSelectedCategories(),
            'weekly' => (bool)$this->request->getData('weekly'),
        ];
        $daily = (bool)$this->request->getData('daily');
        foreach ($this->days as $day) {
            $key = 'daily_' . $day;
            $data[$key] = $daily || (bool)$this->request->getData($key);
        }

        return $data;
    }

    /**
     * DELETE /v1/mailing-list/subscription endpoint
     *
     * @return void
     * @throws ForbiddenException
     * @throws InternalErrorException
     */
    public function unsubscribe()
    {
        $this->request->allowMethod('delete');

        if (!$this->tokenUser) {
            throw new ForbiddenException('User token missing. You must be logged in to update subscription status.');
        }

        $subscription = $this->getCurrentUserSubscription();
        if (!$subscription) {
            throw new ForbiddenException('Cannot unsubscribe: You are not currently subscribed');
        }

        if (!$this->MailingList->delete($subscription)) {
            throw new InternalErrorException(
                'There was an error unsubscribing you. Please try again or contact an administrator for assistance.'
            );
        }

        // Remove subscription association with user
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $usersTable->patchEntity($this->tokenUser, ['mailing_list_id' => null]);
        $usersTable->save($this->tokenUser);

        $this->set204Response();
    }
}
