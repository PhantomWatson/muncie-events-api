<?php

namespace App\Controller;

use App\Model\Entity\MailingList;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\EntityInterface;
use Cake\Http\Response;
use Exception;

/**
 * MailingLists Controller
 *
 * @property \App\Model\Table\CategoriesTable $Categories
 * @property \App\Model\Table\MailingListTable $MailingList
 * @property \App\Model\Table\UsersTable $Users
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

        $this->loadModel('Categories');
        $this->loadModel('Users');

        $this->Auth->allow([
            'index',
            'unsubscribe',
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
        // Redirect if hash is invalid
        if ($subscriberId) {
            $redirect = $this->validateIdAndHash($subscriberId, $hash);
            if ($redirect) {
                return $redirect;
            }
        }

        // Get subscription entity
        if ($subscriberId) {
            $subscription = $this->MailingList->get($subscriberId, ['contain' => ['Categories']]);
        } else {
            $subscription = $this->getCurrentUserSubscription();
        }
        if ($this->request->is('get') && !$subscription) {
            $subscription = $this->MailingList->newEntityWithDefaults();
        }

        // Update with post data
        if ($this->request->is(['post', 'put'])) {
            if (!$subscription) {
                $subscription = $this->MailingList->newEntity();
            }
            $subscription = $this->updateSubscriptionFromRequest($subscription);

            // Avoid saving category associations for all_categories subscriptions
            if ($subscription->all_categories) {
                $stashedCategorySelections = $subscription->categories;
                $subscription->categories = [];
            }

            $isNew = $subscription->isNew();
            if ($this->MailingList->save($subscription)) {
                if ($isNew) {
                    /** @var \App\Model\Entity\User $user */
                    $user = $this->Users
                        ->find()
                        ->where(['id' => $this->Auth->user('id')])
                        ->first();
                    if ($user) {
                        $user->mailing_list_id = $subscription->id;
                        $this->Users->save($user);
                    }
                    $this->Flash->success('Thanks for joining the Muncie Events mailing list!');

                    return $this->redirect('/');
                }

                $this->Flash->success('Subscription updated');

                return $this->redirect([
                    'controller' => 'Users',
                    'action' => 'account',
                ]);
            }

            // Recall previous category selections if bouncing back from error
            if ($subscription->all_categories) {
                $subscription->categories = $stashedCategorySelections;
            }

            $this->Flash->error(
                'There was an error ' .
                ($subscriberId ? 'updating your subscription' : 'subscribing you to the mailing list') .
                '. Please try again, or contact an administrator for assistance.'
            );
        }

        $this->set([
            'categories' => $this->Categories->find()->all(),
            'days' => $this->MailingList->getDays(),
            'pageTitle' => $subscription->isNew() ? 'Join Muncie Events Mailing List' : 'Update Subscription',
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
        $data = [
            'email' => $this->request->getData('email'),
            'new_subscriber' => $subscription->isNew(),
        ];
        $selectedCategories = $this->request->getData('selected_categories');

        // User is joining with default settings
        if ($this->request->getData('settings') == 'default') {
            $data['weekly'] = 1;
            $data['all_categories'] = 1;
        } else {
            $allCategoriesSelected = $this->request->getData('event_categories') == 'all';
            $selectedCategoryCount = $selectedCategories ? count($selectedCategories) : 0;
            $totalCategoryCount = $this->Categories->find()->count();
            $data['all_categories'] = $allCategoriesSelected || $selectedCategoryCount == $totalCategoryCount;

            $frequency = $this->request->getData('frequency');
            $data['weekly'] = $frequency == 'weekly';

            $days = $this->MailingList->getDays();
            if ($frequency == 'daily') {
                foreach ($days as $abbreviation => $day) {
                    $data["daily_$abbreviation"] = 1;
                }
            }

            if ($frequency == 'custom') {
                foreach ($days as $abbreviation => $day) {
                    $data["daily_$abbreviation"] = $this->request->getData("daily_$abbreviation");
                }

                $data['weekly'] = $this->request->getData('weekly');
            }
        }

        $this->MailingList->patchEntity($subscription, $data);

        /* Associated categories are always set to the $subscription->categories field so that the corresponding form
         * checkboxes can be generated as either checked or unchecked, but if all_categories is TRUE, we won't actually
         * save any of those category associations. */
        $selectedCategoryIds = $selectedCategories ? array_keys($selectedCategories) : [];
        if ($selectedCategoryIds) {
            $subscription->categories = $this->Categories
                ->find()
                ->where(function (QueryExpression $exp) use ($selectedCategoryIds) {
                    return $exp->in('id', $selectedCategoryIds);
                })
                ->toArray();
        } else {
            $subscription->categories = [];
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
        // Fetch mailing_list_id from the database because it wouldn't be in the session if the user just subscribed
        /** @var \App\Model\Entity\User $user */
        $user = $this->Users->find()
            ->select(['mailing_list_id'])
            ->where(['id' => $this->Auth->user('id')])
            ->first();
        $subscriberId = $user ? $user->mailing_list_id : null;

        if (!$subscriberId) {
            return null;
        }

        return $this->MailingList
            ->find()
            ->where(['id' => $subscriberId])
            ->contain(['Categories'])
            ->first();
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

        if ($this->request->getQuery('confirm')) {
            $subscription = $this->MailingList->get($subscriberId);

            if ($this->MailingList->delete($subscription)) {
                /** @var \App\Model\Entity\User $user */
                $user = $this->Users
                    ->find()
                    ->where(['mailing_list_id' => $subscriberId])
                    ->first();
                if ($user) {
                    $user->mailing_list_id = null;
                    $this->Users->save($user);
                }
                $this->Flash->success('You have been removed from the mailing list');

                return $this->redirect('/');
            } else {
                $this->Flash->error(
                    'Sorry, but there was an error trying to remove you from the mailing list. ' .
                    'Please try again, or contact an administrator for assistance.'
                );
            }
        }

        $this->set([
            'hash' => $hash,
            'pageTitle' => 'Unsubscribe',
            'subscriberId' => $subscriberId,
        ]);

        return null;
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
