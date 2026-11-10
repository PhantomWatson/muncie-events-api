<?php
namespace App\Model\Table;

use App\Model\Entity\Image;
use App\Model\Entity\User;
use ArrayObject;
use Cake\Cache\Cache;
use Cake\Event\Event;
use Cake\Http\Exception\InternalErrorException;
use Cake\I18n\DateTime;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
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
 * @method \App\Model\Entity\User findOrCreate(\Cake\ORM\Query\SelectQuery|callable|array $search, ?callable $callback = null, array $options = [])
 * @method \Cake\ORM\Query\SelectQuery findByApiKey($apiKey)
 *
 * @mixin TimestampBehavior
 * @method \App\Model\Entity\User newEmptyEntity()
 * @method \App\Model\Entity\User saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User>|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User> saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User>|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\User> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}>
 */
class UsersTable extends Table
{
    /**
     * Prefix of the keys that the user leaderboards are cached under (see getLeaderboardCacheKey())
     *
     * @var string
     */
    public const string LEADERBOARDS_CACHE_KEY = 'user_leaderboards';

    /**
     * Number of days that the "recent" leaderboard looks back
     *
     * @var int
     */
    public const int LEADERBOARD_RECENT_DAYS = 100;

    /**
     * Time periods that each leaderboard is generated for
     *
     * @var string[]
     */
    public const array LEADERBOARD_PERIODS = ['allTime', 'recent'];

    /**
     * Number of users included in each leaderboard
     *
     * @var int
     */
    public const int LEADERBOARD_SIZE = 10;

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
            'role' => User::ROLE_ADMIN,
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
     * Selects users that have either posted events, acquired an API key, or subscribed to the mailing list
     *
     * @param SelectQuery $query
     * @return SelectQuery
     */
    public function findActive(SelectQuery $query): SelectQuery
    {
        return $query
            ->leftJoinWith('Events')
            ->where(function ($exp) {
                return $exp->or([
                    'Events.id IS NOT' => null,
                    'Users.api_key IS NOT' => null,
                    'Users.mailing_list_id IS NOT' => null,
                ]);
            })
            ->distinct('Users.id');
    }

    /**
     * Selects users that have neither posted events, acquired an API key, nor subscribed to the mailing list
     *
     * @param SelectQuery $query
     * @return SelectQuery
     */
    public function findInactive(SelectQuery $query): SelectQuery
    {
        return $query
            ->notMatching('Events')
            ->where([
                'Users.api_key IS' => null,
                'Users.mailing_list_id IS' => null,
            ]);
    }

    /**
     * Returns the users who have posted the most published events, for every category and time period
     *
     * Each leaderboard is read from its own cache entry (see getCachedLeaderboard()).
     *
     * @return array<string, array{allTime: array<int, array<string, mixed>>, recent: array<int, array<string, mixed>>}>
     */
    public function getCachedLeaderboards(): array
    {
        $retval = [];
        foreach ($this->getLeaderboardCategories() as $categoryName => $categoryId) {
            foreach (self::LEADERBOARD_PERIODS as $period) {
                $retval[$categoryName][$period] = $this->getCachedLeaderboard($period, $categoryId);
            }
        }

        return $retval;
    }

    /**
     * Returns a single leaderboard (e.g. Overall + recent), fetching and caching it for 24 hours if necessary
     *
     * @param string $period Either 'allTime' or 'recent'
     * @param int|null $categoryId A category ID, or NULL for the overall leaderboard
     * @return array<int, array<string, mixed>>
     */
    public function getCachedLeaderboard(string $period, ?int $categoryId = null): array
    {
        return Cache::remember(
            self::getLeaderboardCacheKey($period, $categoryId),
            fn () => $this->fetchLeaderboard($period, $categoryId),
            'daily',
        );
    }

    /**
     * Regenerates every cached leaderboard, even if the current cached copies haven't expired yet
     *
     * Each combination of period ('allTime' / 'recent') and category (Overall / each individual category) is cached
     * under its own key.
     *
     * @return bool TRUE if all of the leaderboards were successfully written to the cache
     */
    public function refreshLeaderboards(): bool
    {
        $success = true;
        foreach ($this->getLeaderboardCategories() as $categoryId) {
            foreach (self::LEADERBOARD_PERIODS as $period) {
                $written = Cache::write(
                    self::getLeaderboardCacheKey($period, $categoryId),
                    $this->fetchLeaderboard($period, $categoryId),
                    'daily',
                );
                $success = $written && $success;
            }
        }

        return $success;
    }

    /**
     * Returns the cache key for a single leaderboard
     *
     * @param string $period Either 'allTime' or 'recent'
     * @param int|null $categoryId A category ID, or NULL for the overall leaderboard
     * @return string
     */
    public static function getLeaderboardCacheKey(string $period, ?int $categoryId = null): string
    {
        return sprintf('%s_%s_%s', self::LEADERBOARDS_CACHE_KEY, $categoryId ?? 'overall', $period);
    }

    /**
     * Returns category IDs keyed by category name, beginning with 'Overall' (NULL ID)
     *
     * @return array<string, int|null>
     */
    private function getLeaderboardCategories(): array
    {
        $categories = TableRegistry::getTableLocator()->get('Categories')
            ->find()
            ->orderByAsc('weight')
            ->all();
        $retval = ['Overall' => null];
        foreach ($categories as $category) {
            $retval[$category->name] = $category->id;
        }

        return $retval;
    }

    /**
     * Fetches a single leaderboard from the database
     *
     * @param string $period Either 'allTime' or 'recent'
     * @param int|null $categoryId A category ID, or NULL for the overall leaderboard
     * @return array<int, array<string, mixed>>
     */
    private function fetchLeaderboard(string $period, ?int $categoryId = null): array
    {
        $since = $period === 'recent'
            ? new DateTime(sprintf('-%d days', self::LEADERBOARD_RECENT_DAYS))
            : null;

        return $this->getTopPosters($since, $categoryId);
    }

    /**
     * Returns the IDs, names, and event counts of the users who have posted the most published events
     *
     * @param \Cake\I18n\DateTime|null $since If provided, only events created at or after this time are counted
     * @return array<int, array<string, mixed>>
     */
    private function getTopPosters(?DateTime $since = null, ?int $categoryId = null): array
    {
        $query = $this->find();

        return $query
            ->select([
                'event_count' => $query->func()->count('Events.id'),
                'Users.id',
                'Users.name',
            ])
            ->innerJoinWith('Events', function (SelectQuery $q) use ($since, $categoryId) {
                $q->find('published');
                if ($since) {
                    $q->where(['Events.created >=' => $since]);
                }
                if ($categoryId) {
                    $q->where(['Events.category_id' => $categoryId]);
                }

                return $q;
            })
            ->groupBy(['Users.id', 'Users.name'])
            ->orderBy(['event_count' => 'DESC', 'Users.name' => 'ASC'])
            ->limit(self::LEADERBOARD_SIZE)
            ->enableHydration(false)
            ->all()
            ->toList();
    }
}
