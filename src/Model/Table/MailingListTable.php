<?php
namespace App\Model\Table;

use App\Model\Entity\MailingList;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\EntityInterface;
use Cake\Http\Exception\InternalErrorException;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * MailingList Model
 *
 * @property UsersTable|HasMany $Users
 * @property CategoriesTable|BelongsToMany $Categories
 *
 * @method MailingList get($primaryKey, $options = [])
 * @method MailingList newEntity($data = null, array $options = [])
 * @method MailingList[] newEntities(array $data, array $options = [])
 * @method MailingList|bool save(EntityInterface $entity, $options = [])
 * @method MailingList|bool saveOrFail(EntityInterface $entity, $options = [])
 * @method MailingList patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method MailingList[] patchEntities($entities, array $data, array $options = [])
 * @method MailingList findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin TimestampBehavior
 */
class MailingListTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('mailing_list');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Users', [
            'foreignKey' => 'mailing_list_id',
        ]);
        $this->belongsToMany('Categories', [
            'foreignKey' => 'mailing_list_id',
            'targetForeignKey' => 'category_id',
            'joinTable' => 'categories_mailing_list',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->allowEmptyString('email', 'Email address cannot be blank', false);

        $validator
            ->boolean('all_categories')
            ->requirePresence('all_categories', 'create');

        $validator
            ->boolean('weekly');

        $validator
            ->boolean('daily_sun');

        $validator
            ->boolean('daily_mon');

        $validator
            ->boolean('daily_tue');

        $validator
            ->boolean('daily_wed');

        $validator
            ->boolean('daily_thu');

        $validator
            ->boolean('daily_fri');

        $validator
            ->boolean('daily_sat');

        $validator
            ->boolean('new_subscriber')
            ->requirePresence('new_subscriber', null, 'create');

        $validator
            ->dateTime('processed_daily')
            ->allowEmptyDateTime('processed_daily');

        $validator
            ->dateTime('processed_weekly')
            ->allowEmptyDateTime('processed_weekly');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param RulesChecker $rules The rules object to be modified.
     * @return RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add(
            $rules->isUnique(['email']),
            'emailIsUnique',
            ['message' => 'That email address is already subscribed to the mailing list']
        );

        return $rules;
    }

    /**
     * Returns an array of days of the week, with abbreviations as keys
     *
     * @return array
     */
    public function getDays()
    {
        return [
            'sun' => 'Sunday',
            'mon' => 'Monday',
            'tue' => 'Tuesday',
            'wed' => 'Wednesday',
            'thu' => 'Thursday',
            'fri' => 'Friday',
            'sat' => 'Saturday',
        ];
    }

    /**
     * Returns a mailing list record matching the provided email address, or null if none is found
     *
     * @param string $email Email address
     * @return MailingList|null|EntityInterface
     */
    public function getFromEmail($email)
    {
        return $this->find()
            ->where(['email' => $email])
            ->first();
    }

    /**
     * Returns a new entity with fields set to default values
     *
     * @return \App\Model\Entity\MailingList
     */
    public function newEntityWithDefaults()
    {
        $subscription = $this->newEntity();
        $subscription->weekly = true;
        $subscription->all_categories = true;
        $subscription->categories = $this->Categories
            ->find()
            ->toArray();

        return $subscription;
    }

    /**
     * Returns a resultset of today's daily mailing list message recipients
     *
     * @param string|null $email Optional specific email address
     * @return \Cake\Datasource\ResultSetInterface|MailingList[]
     */
    public function getDailyRecipients($email = null)
    {
        list($y, $m, $d) = [date('Y'), date('m'), date('d')];
        $query = $this
            ->find()
            ->where([
                'daily_' . strtolower(date('D')) => 1,
                'OR' => [
                    function (QueryExpression $exp) {
                        return $exp->isNull('processed_daily');
                    },
                    'processed_daily <' => "$y-$m-$d 00:00:00",
                ],
            ])
            ->contain([
                'Categories' => function (Query $q) {
                    return $q->select(['Categories.id', 'Categories.name']);
                },
            ]);
        if ($email) {
            $query->where(['email' => $email]);
        }

        return $query->all();
    }

    /**
     * Marks a daily mailing list subscriber as having been processed
     *
     * @param MailingList|null $subscriber Mailing list subscriber, or NULL if this entry applies to all subscribers
     * @param int $result Code representing result of running this script for this recipient
     * @return void
     * @throws \Cake\Http\Exception\InternalErrorException
     */
    public function markDailyAsProcessed($subscriber, $result)
    {
        $mailingListLogTable = TableRegistry::getTableLocator()->get('MailingListLog');
        $logEntry = $mailingListLogTable->newEntity([
            'recipient_id' => $subscriber ? $subscriber->id : null,
            'result' => $result,
            'is_daily' => 1,
        ]);
        if (!$mailingListLogTable->save($logEntry)) {
            throw new InternalErrorException('Failed to save mailing list log entry');
        }

        if (!$subscriber) {
            return;
        }

        $this->patchEntity($subscriber, [
            'processed_daily' => date('Y-m-d H:i:s'),
            'new_subscriber' => 0,
        ]);
        if (!$this->save($subscriber)) {
            throw new InternalErrorException('Failed to update subscriber record');
        }
    }

    /**
     * Returns a set of weekly mailing list subscribers
     *
     * @param string|null $email Optional specific email address
     * @return \Cake\Datasource\ResultSetInterface|MailingList[]
     */
    public function getWeeklyRecipients($email = null)
    {
        list($y, $m, $d) = [date('Y'), date('m'), date('d')];
        $query = $this
            ->find()
            ->where([
                'MailingList.weekly' => 1,
                'OR' => [
                    function (QueryExpression $exp) {
                        return $exp->isNull('processed_weekly');
                    },
                    'processed_weekly <' => "$y-$m-$d 00:00:00",
                ],
            ])
            ->contain([
                'Categories' => function (Query $q) {
                    return $q->select(['Categories.id', 'Categories.name']);
                },
            ]);
        if ($email) {
            $query->where(['email' => $email]);
        }

        return $query->all();
    }

    /**
     * Marks a weekly mailing list subscriber as having been processed
     *
     * @param MailingList|null $subscriber Mailing list subscriber, or NULL if this entry applies to all subscribers
     * @param int $result Code representing result of running this script for this recipient
     * @return void
     * @throws \Cake\Http\Exception\InternalErrorException
     */
    public function markWeeklyAsProcessed($subscriber, $result)
    {
        $mailingListLogTable = TableRegistry::getTableLocator()->get('MailingListLog');
        $logEntry = $mailingListLogTable->newEntity([
            'recipient_id' => $subscriber ? $subscriber->id : null,
            'result' => $result,
            'is_weekly' => 1,
        ]);
        if (!$mailingListLogTable->save($logEntry)) {
            throw new InternalErrorException('Failed to save mailing list log entry');
        }

        if (!$subscriber) {
            return;
        }

        $this->patchEntity($subscriber, [
            'processed_weekly' => date('Y-m-d H:i:s'),
            'new_subscriber' => 0,
        ]);
        if (!$this->save($subscriber)) {
            throw new InternalErrorException('Failed to update subscriber record');
        }
    }
}
