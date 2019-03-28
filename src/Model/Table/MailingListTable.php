<?php
namespace App\Model\Table;

use App\Model\Entity\MailingList;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
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
            ->integer('id')
            ->allowEmptyString('id', 'create');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->allowEmptyString('email', false);

        $validator
            ->boolean('all_categories')
            ->requirePresence('all_categories', 'create')
            ->allowEmptyString('all_categories', false);

        $validator
            ->boolean('weekly')
            ->requirePresence('weekly', 'create')
            ->allowEmptyString('weekly', false);

        $validator
            ->boolean('daily_sun')
            ->requirePresence('daily_sun', 'create')
            ->allowEmptyString('daily_sun', false);

        $validator
            ->boolean('daily_mon')
            ->requirePresence('daily_mon', 'create')
            ->allowEmptyString('daily_mon', false);

        $validator
            ->boolean('daily_tue')
            ->requirePresence('daily_tue', 'create')
            ->allowEmptyString('daily_tue', false);

        $validator
            ->boolean('daily_wed')
            ->requirePresence('daily_wed', 'create')
            ->allowEmptyString('daily_wed', false);

        $validator
            ->boolean('daily_thu')
            ->requirePresence('daily_thu', 'create')
            ->allowEmptyString('daily_thu', false);

        $validator
            ->boolean('daily_fri')
            ->requirePresence('daily_fri', 'create')
            ->allowEmptyString('daily_fri', false);

        $validator
            ->boolean('daily_sat')
            ->requirePresence('daily_sat', 'create')
            ->allowEmptyString('daily_sat', false);

        $validator
            ->boolean('new_subscriber')
            ->requirePresence('new_subscriber', 'create')
            ->allowEmptyString('new_subscriber', false);

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
}
