<?php
namespace App\Model\Table;

use App\Model\Entity\User;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Http\Exception\InternalErrorException;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @property EventSeriesTable|HasMany $EventSeries
 * @property EventsTable|HasMany $Events
 * @property ImagesTable|HasMany $Images
 * @property TagsTable|HasMany $Tags
 *
 * @method User get($primaryKey, $options = [])
 * @method User newEntity($data = null, array $options = [])
 * @method User[] newEntities(array $data, array $options = [])
 * @method User|bool save(EntityInterface $entity, $options = [])
 * @method User patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method User[] patchEntities($entities, array $data, array $options = [])
 * @method User findOrCreate($search, callable $callback = null, $options = [])
 * @method Query findByApiKey($apiKey)
 *
 * @mixin TimestampBehavior
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
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id');

        $validator
            ->scalar('name')
            ->requirePresence('name', 'create')
            ->allowEmptyString('name', 'User name cannot be blank', false);

        $validator
            ->scalar('role')
            ->requirePresence('role', 'create')
            ->minLength('role', 1);

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->allowEmptyString('email', 'Email address cannot be blank', false);

        $validator
            ->scalar('password')
            ->requirePresence('password', 'create')
            ->allowEmptyString('password', 'Password cannot be blank', false)
            ->allowEmptyString('confirm_password', 'Please confirm your password by typing it a second time', false)
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

        $validator
            ->scalar('reset_password_hash')
            ->lengthBetween('reset_password_hash', [32, 32])
            ->ascii('reset_password_hash');

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
        $apiKey = User::generateApiKey();
        $user = $this->get($userId);
        $user = $this->patchEntity($user, ['api_key' => $apiKey]);

        return $this->save($user);
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
     * Returns a user with a matching token or NULL if none was found
     *
     * @param string $token User token
     * @return User|null
     */
    public function getByToken($token)
    {
        /** @var User $user */
        $user = $this
            ->find()
            ->where(['token' => $token])
            ->first();

        return $user;
    }

    /**
     * Saves a new (or updated) token for this user and returns the user
     *
     * @param User $user User entity
     * @return User
     */
    public function addToken($user)
    {
        $token = User::generateToken();

        // Generate and save a NEW user entity so that any other dirty fields aren't saved as a side-effect
        $tempUser = $this->get($user->id);
        $tempUser = $this->patchEntity($tempUser, compact('token'));
        if (!$this->save($tempUser)) {
            throw new InternalErrorException(
                'Error creating new user token. Details: ' . print_r($tempUser->getErrors(), true)
            );
        }

        // Return the provided user with an updated token field
        $user = $this->patchEntity($user, compact('token'));

        return $user;
    }

    /**
     * Returns true if the user's events should be automatically published
     *
     * This is the case for admins or anyone who has previously submitted an event that has been published/approved
     *
     * @param int $userId of user
     * @return bool
     */
    public function getAutoPublish($userId)
    {
        if (!$userId) {
            return false;
        }

        $isAdmin = $this->exists([
            'id' => $userId,
            'role' => 'admin'
        ]);
        if ($isAdmin) {
            return true;
        }

        $count = $this->Events->find()
            ->where(['user_id' => $userId])
            ->andWhere(['published' => 1])
            ->andwhere(['approved_by IS NOT' => null])
            ->count();

        return $count > 1;
    }
}
