<?php
namespace App\Model\Table;

use App\Model\Entity\ApiCall;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ApiCalls Model
 *
 * @property UsersTable|BelongsTo $Users
 *
 * @method ApiCall get($primaryKey, $options = [])
 * @method ApiCall newEntity($data = null, array $options = [])
 * @method ApiCall[] newEntities(array $data, array $options = [])
 * @method ApiCall|bool save(EntityInterface $entity, $options = [])
 * @method ApiCall patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method ApiCall[] patchEntities($entities, array $data, array $options = [])
 * @method ApiCall findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin TimestampBehavior
 */
class ApiCallsTable extends Table
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

        $this->setTable('api_calls');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        $validator
            ->integer('id');

        $validator
            ->scalar('url')
            ->requirePresence('url', 'create')
            ->allowEmptyString('url', 'API call URL cannot be blank', false);

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param RulesChecker $rules The rules object to be modified.
     * @return RulesChecker
     */
    public function buildRules(RulesChecker $rules): \Cake\ORM\RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
}
