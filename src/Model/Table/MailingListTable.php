<?php
namespace App\Model\Table;

use App\Model\Entity\MailingList;
use Cake\Core\Configure;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\ResultSetInterface;
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
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\HasMany $Users
 * @property \App\Model\Table\CategoriesTable&\Cake\ORM\Association\BelongsToMany $Categories
 *
 * @method \App\Model\Entity\MailingList get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MailingList newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\MailingList[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MailingList|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MailingList saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MailingList patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\MailingList[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MailingList findOrCreate($search, ?callable $callback = null, array $options = [])
 *
 * @mixin TimestampBehavior
 * @method \App\Model\Entity\MailingList newEmptyEntity()
 * @method \App\Model\Entity\MailingList[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\MailingList[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\MailingList[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\MailingList[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MailingListTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
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
    public function validationDefault(Validator $validator): Validator
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

        $validator
            ->boolean('enabled');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param RulesChecker $rules The rules object to be modified.
     * @return RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
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
    public function getDays(): array
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
     * @return EntityInterface|null|MailingList
     */
    public function getFromEmail(string $email): MailingList|EntityInterface|null
    {
        /** @var MailingList $mailingList */
        $mailingList = $this->find()
            ->where(['email' => $email])
            ->first();

        return $mailingList;
    }

    /**
     * Returns a new entity with fields set to default values
     *
     * @return MailingList|EntityInterface
     */
    public function newEntityWithDefaults(): MailingList|EntityInterface
    {
        /** @var MailingList $subscription */
        $subscription = $this->newEmptyEntity();
        $subscription->weekly = true;
        $subscription->all_categories = true;
        $subscription->categories = $this->Categories
            ->find()
            ->toArray();

        return $subscription;
    }

    /**
     * Returns a result set of today's daily mailing list message recipients
     *
     * @param string|null $email Optional specific email address
     * @param int $limit Limit of how many recipients to return, zero for no limit
     * @return MailingList[]|ResultSetInterface
     */
    public function getDailyRecipients(string $email = null, int $limit = 0): ResultSetInterface|array
    {
        $timezone = Configure::read('localTimezone');
        $now = new \Cake\I18n\DateTime('now', $timezone);
        $currentDate = $now->format('Y-m-d');
        $dayAbbrev = $now->format('D');
        $query = $this
            ->find()
            ->where([
                'MailingList.enabled' => true,
                'daily_' . strtolower($dayAbbrev) => 1,
                'OR' => [
                    function (QueryExpression $exp) {
                        return $exp->isNull('processed_daily');
                    },
                    'processed_daily <' => "$currentDate 00:00:00",
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
        if ($limit) {
            $query->limit($limit);
        }

        /** @var MailingList[]|ResultSetInterface $subscriptions */
        $subscriptions = $query->all();

        return $subscriptions;
    }

    /**
     * Marks a daily mailing list subscriber as having been processed
     *
     * @param MailingList|null $subscriber Mailing list subscriber, or NULL if this entry applies to all subscribers
     * @param int $result Code representing result of running this script for this recipient
     * @return void
     * @throws InternalErrorException
     */
    public function markDailyAsProcessed(?MailingList $subscriber, int $result): void
    {
        $mailingListLogTable = TableRegistry::getTableLocator()->get('MailingListLog');
        $logEntry = $mailingListLogTable->newEntity([
            'recipient_id' => $subscriber?->id,
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
     * @param int $limit Limit of how many recipients to return, zero for no limit
     * @return ResultSetInterface|MailingList[]
     */
    public function getWeeklyRecipients(?string $email = null, int $limit = 0): ResultSetInterface|array
    {
        $timezone = Configure::read('localTimezone');
        $currentDate = (new \Cake\I18n\DateTime('now', $timezone))->format('Y-m-d');
        $query = $this
            ->find()
            ->where([
                'MailingList.enabled' => true,
                'MailingList.weekly' => 1,
                'OR' => [
                    function (QueryExpression $exp) {
                        return $exp->isNull('processed_weekly');
                    },
                    'processed_weekly <' => "$currentDate 00:00:00",
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
        if ($limit) {
            $query->limit($limit);
        }

        /** @var MailingList[]|ResultSetInterface $subscriptions */
        $subscriptions = $query->all();

        return $subscriptions;
    }

    /**
     * Marks a weekly mailing list subscriber as having been processed
     *
     * @param MailingList|null $subscriber Mailing list subscriber, or NULL if this entry applies to all subscribers
     * @param int $result Code representing result of running this script for this recipient
     * @return void
     * @throws InternalErrorException
     */
    public function markWeeklyAsProcessed(?MailingList $subscriber, int $result): void
    {
        $mailingListLogTable = TableRegistry::getTableLocator()->get('MailingListLog');
        $logEntry = $mailingListLogTable->newEntity([
            'recipient_id' => $subscriber?->id,
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
