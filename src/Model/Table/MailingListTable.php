<?php
namespace App\Model\Table;

use App\Model\Entity\MailingList;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Utility\Security;
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
            'foreignKey' => 'mailing_list_id'
        ]);
        $this->belongsToMany('Categories', [
            'foreignKey' => 'mailing_list_id',
            'targetForeignKey' => 'category_id',
            'joinTable' => 'categories_mailing_list'
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
            ->integer('id');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->allowEmptyString('email', 'Email address required', false);

        $validator
            ->boolean('all_categories')
            ->requirePresence('all_categories', 'create');

        $validator
            ->boolean('weekly')
            ->requirePresence('weekly', 'create');

        $validator
            ->boolean('daily_sun')
            ->requirePresence('daily_sun', 'create');

        $validator
            ->boolean('daily_mon')
            ->requirePresence('daily_mon', 'create');

        $validator
            ->boolean('daily_tue')
            ->requirePresence('daily_tue', 'create');

        $validator
            ->boolean('daily_wed')
            ->requirePresence('daily_wed', 'create');

        $validator
            ->boolean('daily_thu')
            ->requirePresence('daily_thu', 'create');

        $validator
            ->boolean('daily_fri')
            ->requirePresence('daily_fri', 'create');

        $validator
            ->boolean('daily_sat')
            ->requirePresence('daily_sat', 'create');

        $validator
            ->boolean('new_subscriber')
            ->requirePresence('new_subscriber', 'create');

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
        $rules->add($rules->isUnique(['email']));

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
            'sat' => 'Saturday'
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
     * Returns the security hash for the specified mailing list subscriber ID
     *
     * @param int|null $subscriberId ID of record in mailing list table
     * @return string
     */
    public function getHash(?int $subscriberId)
    {
        return Security::hash($subscriberId, null, true);
    }
}
