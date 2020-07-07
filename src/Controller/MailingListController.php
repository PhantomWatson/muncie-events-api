<?php

namespace App\Controller;

use App\Model\Entity\MailingList;
use App\Model\Table\CategoriesTable;
use App\Model\Table\MailingListTable;
use Cake\Datasource\EntityInterface;
use Cake\Http\Response;
use Exception;

/**
 * MailingLists Controller
 *
 * @property MailingListTable $MailingList
 * @property CategoriesTable $Categories
 */
class MailingListController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     * @throws Exception
     */
    public function initialize()
    {
        parent::initialize();

        $this->Auth->allow([
            'index',
            'sendDaily',
            'sendWeekly',
        ]);
    }

    /**
     * Page for subscribing to the mailing list or updating an existing subscription
     *
     * @param int|null $subscriberId ID of record in mailing_list table
     * @param string|null $hash Hash for verifying that the user making this request is the subscriber
     * @return Response|null
     */
    public function index($subscriberId = null, $hash = null)
    {
        if ($subscriberId) {
            $redirect = $this->validateIdAndHash($subscriberId, $hash);
            if ($redirect) {
                return $redirect;
            }

            $subscription = $this->MailingList->get($subscriberId);
        } else {
            $subscription = $this->getCurrentUserSubscription() ?? $this->MailingList->newEntity();
        }

        if ($this->request->is('post')) {
            $subscription = $this->updateSubscriptionFromRequest($subscription);
            if ($this->MailingList->save($subscription)) {
                $this->Flash->success(
                    $subscriberId ? 'Subscription updated' : 'Thanks for joining the Muncie Events mailing list!'
                );

                return $this->redirect('/');
            }

            $this->Flash->error(
                'There was an error ' .
                ($subscriberId ? 'updating your subscription' : 'subscribing you to the mailing list') .
                '. Please try again, or contact an administrator for assistance.'
            );
        }

        $this->loadModel('Categories');
        $this->set([
            'categories' => $this->Categories->find()->all(),
            'days' => $this->MailingList->getDays(),
            'pageTitle' => $subscriberId ? 'Update Subscription' : 'Join Muncie Events Mailing List',
            'subscription' => $subscription,
        ]);

        return null;
    }

    /**
     * Uses form data to update a MailingList entity, but does not update the database
     *
     * @param MailingList $subscription MailingList entity
     * @return MailingList
     */
    private function updateSubscriptionFromRequest($subscription)
    {
        $subscription->email = strtolower(trim($this->request->getData('email')));
        $subscription->new_subscriber = !$this->MailingList->exists(['email' => $subscription->email]);

        // User is joining with default settings
        if ($this->request->getData('settings') == 'default') {
            $subscription->weekly = 1;
            $subscription->all_categories = 1;

            return $subscription;
        }

        // "All categories" is selected
        if ($this->request->getData('event_categories') == 'all') {
            $subscription->all_categories = 1;

            // "Custom categories" is selected
        } else {
            // If the user individually selected every category, set 'all_categories' to true
            $selectedCategoryCount = count($this->request->getData('selected_categories'));
            $this->loadModel('Categories');
            $totalCategoryCount = $this->Categories->find()->count();
            $subscription->all_categories = $selectedCategoryCount == $totalCategoryCount;
        }

        if (!$subscription->all_categories) {
            $subscription->categories['_ids'] = $this->request->getData('selected_categories');
        }

        // "Weekly" is selected
        $subscription->weekly = $this->request->getData('frequency') == 'weekly';

        // "Daily" is selected
        $days = $this->MailingList->getDays();
        $frequency = $this->request->getData('frequency');
        if ($frequency == 'daily') {
            foreach ($days as $abbreviation => $day) {
                $subscription->{"daily_$abbreviation"} = 1;
            }
        }

        // Custom frequency is selected
        if ($frequency == 'custom') {
            foreach ($days as $abbreviation => $day) {
                $subscription->{"daily_$abbreviation"} = $this->request->getData("daily_$abbreviation");
            }

            $subscription->weekly = $this->request->getData('weekly');
        }

        return $subscription;
    }

    /**
     * Returns the mailing list record associated with the currently logged-in user, or null if no such record is found
     *
     * @return MailingList|EntityInterface|null
     */
    private function getCurrentUserSubscription()
    {
        if (!$this->Auth->user()) {
            return null;
        }

        $email = $this->Auth->user('email');

        return $this->MailingList->getFromEmail($email);
    }

    /**
     * Page for unsubscribing from the mailing list
     *
     * @param int|null $subscriberId ID of record in mailing_list table
     * @param string|null $hash Hash for verifying that the user making this request is the subscriber
     * @return Response|null
     */
    public function unsubscribe($subscriberId = null, $hash = null)
    {
        $redirect = $this->validateIdAndHash($subscriberId, $hash);
        if ($redirect) {
            return $redirect;
        }

        $subscription = $this->MailingList->get($subscriberId);

        if ($this->MailingList->delete($subscription)) {
            $this->Flash->success('You have been removed from the mailing list');
        } else {
            $this->Flash->error(
                'Sorry, but there was an error trying to remove you from the mailing list. ' .
                'Please try again, or contact an administrator for assistance.'
            );
        }

        return $this->redirect('/');
    }

    /**
     * Generates a flash message and returns a redirect response if the subscriber ID and/or hash are invalid
     *
     * @param int|null $subscriberId ID of record in the mailing list table
     * @param string|null $hash Security hash for this subscriber
     * @return bool|Response|null
     */
    private function validateIdAndHash(?int $subscriberId, ?string $hash)
    {
        $subscriberIsValid = $this->MailingList->exists(['id' => $subscriberId]);
        if (!$subscriberIsValid) {
            $this->Flash->error(
                'It looks like you\'re trying to change settings for a user who is no longer subscribed. ' .
                'Please contact an administrator if you need assistance. '
            );

            return $this->redirect('/');
        }
        $subscription = $this->MailingList->get($subscriberId);
        if (!$hash == $subscription->hash) {
            $this->Flash->error(
                'It appears that you clicked on a broken link. If you copied and pasted a URL to get ' .
                'here, you may not have copied the whole address. Please contact an administrator if you need ' .
                'assistance.'
            );

            return $this->redirect('/');
        }

        return false;
    }
}
