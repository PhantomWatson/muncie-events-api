<?php
namespace App\Model\Table;

use App\Model\Entity\User;
use ArrayObject;
use Cake\Event\Event;
use Cake\Http\Exception\InternalErrorException;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @property \App\Model\Table\EventSeriesTable|\Cake\ORM\Association\HasMany $EventSeries
 * @property \App\Model\Table\EventsTable|\Cake\ORM\Association\HasMany $Events
 * @property \App\Model\Table\ImagesTable|\Cake\ORM\Association\HasMany $Images
 * @property \App\Model\Table\TagsTable|\Cake\ORM\Association\HasMany $Tags
 *
 * @method User get($primaryKey, $options = [])
 * @method User newEntity($data = null, array $options = [])
 * @method User[] newEntities(array $data, array $options = [])
 * @method User|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method User[] patchEntities($entities, array $data, array $options = [])
 * @method User findOrCreate($search, callable $callback = null, $options = [])
 * @method \Cake\ORM\Query findByApiKey($apiKey)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
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

        $this->setTable('users');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('EventSeries', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Events', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Images', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Tags', [
            'foreignKey' => 'user_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id');

        $validator
            ->scalar('name')
            ->requirePresence('name', 'create')
            ->minLength('name', 1);

        $validator
            ->scalar('role')
            ->requirePresence('role', 'create')
            ->minLength('role', 1);

        $validator
            ->email('email')
            ->requirePresence('email', 'create');

        $validator
            ->scalar('password')
            ->requirePresence('password', 'create')
            ->minLength('password', 1)
            ->minLength('confirm_password', 1)
            ->add('confirm_password', [
                'compare' => [
                    'rule' => ['compareWith', 'password'],
                    'message' => 'Your passwords do not match.'
                ]
            ]);

        $validator
            ->scalar('api_key')
            ->lengthBetween('api_key', [32, 32])
            ->ascii('api_key');

        $validator
            ->scalar('token')
            ->lengthBetween('token', [32, 32])
            ->ascii('token');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['email']), 'uniqueEmail', [
            'message' => 'There is already a Muncie Events account registered with this email address.'
        ]);

        return $rules;
    }

    /**
     * beforeMarshal method
     *
     * @param Event $event Event
     * @param ArrayObject $data Entity data
     * @param ArrayObject $options Array of options
     * @return void
     */
    public function beforeMarshal(Event $event, ArrayObject $data, ArrayObject $options)
    {
        if (isset($data['email'])) {
            $data['email'] = mb_strtolower($data['email']);
        }
    }

    /**
     * Generates a random API key and assigns it to the specified user
     *
     * @param int $userId User ID
     * @return User|bool
     */
    public function setApiKey($userId)
    {
        $apiKey = $this->generateApiKey();
        $user = $this->get($userId);
        $user = $this->patchEntity($user, ['api_key' => $apiKey]);

        return $this->save($user);
    }

    /**
     * Generates a string token for the Users.api_key field
     *
     * The API key is used to authorize the user or application to make any API call
     *
     * @return string
     */
    public function generateApiKey()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $length = 32;
        $apiKey = '';
        for ($i = 0; $i < $length; $i++) {
            $apiKey .= $characters[rand(0, $charactersLength - 1)];
        }

        return $apiKey;
    }

    /**
     * Returns the user's API key, or null if not set
     *
     * Throws an exception if the user isn't found
     *
     * @param int $userId User ID
     * @return string|null
     */
    public function getApiKey($userId)
    {
        /** @var User $result */
        $result = $this->find()
            ->select(['api_key'])
            ->where(['id' => $userId])
            ->firstOrFail();

        return $result->api_key;
    }

    /**
     * Returns whether or not the API key was found in the database
     *
     * @param string $apiKey API key
     * @return bool
     */
    public function isValidApiKey($apiKey)
    {
        $count = $this->find()
            ->where(['api_key' => $apiKey])
            ->count();

        return $count > 0;
    }

    /**
     * Generates a string token for the Users.token field
     *
     * The user token is used to authorize the API end-user to perform an action tied to record ownership, e.g.
     * adding events or updating user contact information
     *
     * @return string
     */
    public function generateToken()
    {
        return $this->generateApiKey();
    }

    /**
     * Custom finder that finds the first user with a matching token
     *
     * @param Query $query Query object
     * @param array $options Array of options including 'token'
     * @return Query
     */
    public function findByToken(Query $query, array $options)
    {
        if (!array_key_exists('token', $options)) {
            throw new InternalErrorException("\$options['token'] unspecified");
        }

        return $query
            ->where(['token' => $options['token']])
            ->limit(1);
    }
}
