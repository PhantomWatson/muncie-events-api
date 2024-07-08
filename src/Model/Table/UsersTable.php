<?php
namespace App\Model\Table;

use App\Model\Entity\Image;
use App\Model\Entity\User;
use ArrayObject;
use Cake\Core\Configure;
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
 * @property \App\Model\Table\EventSeriesTable&\Cake\ORM\Association\HasMany $EventSeries
 * @property \App\Model\Table\EventsTable&\Cake\ORM\Association\HasMany $Events
 * @property \App\Model\Table\ImagesTable&\Cake\ORM\Association\HasMany $Images
 * @property \App\Model\Table\TagsTable&\Cake\ORM\Association\HasMany $Tags
 *
 * @method \App\Model\Entity\User get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\User newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method Query findByApiKey($apiKey)
 *
 * @mixin TimestampBehavior
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, array $options = [])
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
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('EventSeries', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Events', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Images', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Tags', [
            'foreignKey' => 'user_id',
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
                    'message' => 'Your passwords do not match.',
                ],
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
    public function buildRules(RulesChecker $rules): \Cake\ORM\RulesChecker
    {
        $rules->add($rules->isUnique(['email']), 'uniqueEmail', [
            'message' => 'There is already a Muncie Events account registered with this email address.',
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
            'role' => 'admin',
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

    /**
     * Returns a list of images associated with the specified user
     *
     * @param int $userId User ID
     * @return \Cake\Datasource\ResultSetInterface|Image[]
     */
    public function getImagesList($userId)
    {
        return $this->Images->find()
            ->where(['user_id' => $userId])
            ->orderByDesc('created')
            ->all();
    }

    /**
     * get the security hash for the password reset
     *
     * @param int $userId User ID
     * @param string $email Recipient email
     * @return string
     */
    public function getResetPasswordHash($userId, $email)
    {
        $salt = Configure::read('password_reset_salt');
        $month = date('my');

        return md5($userId . $email . $salt . $month);
    }
}
