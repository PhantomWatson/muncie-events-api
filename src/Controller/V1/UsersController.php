<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Entity\User;
use App\Model\Table\UsersTable;
use Cake\Auth\FormAuthenticate;
use Cake\Controller\ComponentRegistry;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\MethodNotAllowedException;
use Cake\Mailer\MailerAwareTrait;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Exception;

/**
 * Class UsersController
 * @package App\Controller
 * @property UsersTable $Users
 */
class UsersController extends ApiController
{
    use MailerAwareTrait;

    public $paginate = [
        'limit' => 50,
        'order' => [
            'Events.date' => 'desc',
            'Events.time_start' => 'desc',
        ],
    ];

    /**
     * Initialization hook method
     *
     * @return void
     * @throws Exception
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow([
            'events',
            'forgotPassword',
            'login',
            'register',
            'view',
        ]);
    }

    /**
     * /user/register endpoint
     *
     * @return void
     * @throws BadRequestException
     * @throws MethodNotAllowedException
     */
    public function register()
    {
        $this->request->allowMethod('post');

        $user = $this->Users->newEntity($this->request->getData(), [
            'fields' => ['name', 'email', 'password'],
        ]);
        $user->role = 'user';
        $user->token = User::generateToken();

        if (!$this->Users->save($user)) {
            throw new BadRequestException(
                'There was an error registering. Details: ' . print_r($user->getErrors(), true)
            );
        }

        if ((bool)$this->request->getData('join_mailing_list')) {
            $this->subscribeUserToMailingList($user);
        }

        // Recreate entity so that only specific fields are visible
        $user = $this->Users
            ->find()
            ->select(['id', 'name', 'email', 'token'])
            ->where(['id' => $user->id])
            ->first();

        $this->set([
            '_entities' => ['User'],
            '_serialize' => ['user'],
            'user' => $user,
        ]);
    }

    /**
     * /user/login endpoint
     *
     * @return void
     * @throws BadRequestException
     * @throws MethodNotAllowedException
     */
    public function login()
    {
        $this->request->allowMethod('post');

        foreach (['email', 'password'] as $field) {
            if (!$this->request->getData($field)) {
                throw new BadRequestException('The parameter "' . $field . '" is required');
            }
        }

        $user = $this->getUserFromLoginCredentials();
        if (!$user) {
            throw new BadRequestException('Email or password is incorrect');
        }

        // Convert user array into user entity, as required by JsonApi view
        /** @var User $user */
        $user = $this->Users
            ->find()
            ->select(['id', 'name', 'email', 'token'])
            ->where(['id' => $user['id']])
            ->first();
        if (!$user->token) {
            $user = $this->Users->addToken($user);
        }

        $this->set([
            '_entities' => ['User'],
            '_serialize' => ['user'],
            'user' => $user,
        ]);
    }

    /**
     * Identifies a user based on email and password
     *
     * @return array|bool
     */
    private function getUserFromLoginCredentials()
    {
        $registry = new ComponentRegistry();
        $config = [
            'fields' => ['username' => 'email'],
            'passwordHasher' => [
                'className' => 'Fallback',
                'hashers' => ['Default', 'Legacy'],
            ],
        ];
        $auth = new FormAuthenticate($registry, $config);

        return $auth->authenticate($this->getRequest(), $this->response);
    }

    /**
     * /user/{userId} endpoint
     *
     * @param int $userId User ID
     * @return void
     * @throws BadRequestException
     * @throws MethodNotAllowedException
     */
    public function view($userId = null)
    {
        $this->request->allowMethod('get');

        $user = $this->Users
            ->find()
            ->select(['id', 'name', 'email'])
            ->where(['id' => $userId])
            ->first();
        if (!$user) {
            throw new BadRequestException('User not found');
        }

        $this->set([
            '_entities' => ['User'],
            '_serialize' => ['user'],
            'user' => $user,
        ]);
    }

    /**
     * /user/forgot-password endpoint
     *
     * @return void
     * @throws BadRequestException
     * @throws MethodNotAllowedException
     */
    public function forgotPassword()
    {
        $this->request->allowMethod('post');

        $email = $this->request->getData('email');
        $email = trim($email);
        $email = mb_strtolower($email);
        if (empty($email)) {
            throw new BadRequestException('Please provide an email address');
        }

        $user = $this->Users
            ->find()
            ->where(['email' => $email])
            ->first();
        if (!$user) {
            throw new BadRequestException('No account was found matching that email address');
        }

        $this->getMailer('Users')->send('forgotPassword', [$user]);

        $this->set204Response();
    }

    /**
     * /user/images endpoint
     *
     * @return void
     * @throws BadRequestException
     * @throws MethodNotAllowedException
     */
    public function images()
    {
        $this->request->allowMethod('get');
        if (!$this->tokenUser) {
            throw new BadRequestException('User token missing');
        }
        $imagesTable = TableRegistry::getTableLocator()->get('Images');
        $images = $imagesTable
            ->find()
            ->where(['user_id' => $this->tokenUser->id])
            ->orderDesc('created')
            ->all();

        $this->set([
            '_entities' => ['Image'],
            '_serialize' => ['images'],
            'images' => $images,
        ]);
    }

    /**
     * /user/profile endpoint
     *
     * @return void
     * @throws BadRequestException
     * @throws MethodNotAllowedException
     */
    public function profile()
    {
        $this->request->allowMethod('patch');
        if (!$this->tokenUser) {
            throw new BadRequestException('User token missing');
        }

        $updatedName = $this->request->getData('name');
        $updatedEmail = $this->request->getData('email');
        if ($updatedName === null && $updatedEmail === null) {
            throw new BadRequestException('Either \'name\' or \'email\' parameters must be provided');
        }

        $user = $this->tokenUser;
        $data = [];
        if ($updatedName !== null) {
            $data['name'] = $updatedName;
        }
        if ($updatedEmail !== null) {
            $data['email'] = $updatedEmail;
        }
        $this->Users->patchEntity($user, $data);
        if (!$this->Users->save($user)) {
            $errors = $user->getErrors();
            $messages = Hash::extract($errors, '{s}.{s}');
            throw new BadRequestException('There was an error updating your profile. Details: ' . implode('; ', $messages));
        }

        $this->set204Response();
    }

    /**
     * /user/password endpoint
     *
     * @return void
     * @throws BadRequestException
     * @throws MethodNotAllowedException
     */
    public function password()
    {
        $this->request->allowMethod('patch');
        if (!$this->tokenUser) {
            throw new BadRequestException('User token missing');
        }

        $newPassword = $this->request->getData('password');
        if ($newPassword === null) {
            throw new BadRequestException('No password provided');
        }

        $user = $this->tokenUser;
        $this->Users->patchEntity($user, ['password' => $newPassword]);
        if (!$this->Users->save($user)) {
            $errors = $user->getErrors();
            $messages = Hash::extract($errors, '{s}.{s}');
            throw new BadRequestException(
                'There was an error updating your password. Details: ' . implode('; ', $messages)
            );
        }

        $this->set204Response();
    }

    /**
     * /user/{userId}/events endpoint
     *
     * @param int $userId User ID
     * @return void
     * @throws BadRequestException
     * @throws MethodNotAllowedException
     * @throws Exception
     */
    public function events($userId)
    {
        $this->loadComponent('ApiPagination', ['model' => 'Events']);

        $this->request->allowMethod('get');

        $userExists = $this->Users->exists(['id' => $userId]);
        if (!$userExists) {
            throw new BadRequestException('Error: User not found');
        }

        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        $query = $eventsTable
            ->find('forApi')
            ->where(['Events.user_id' => $userId]);

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'EventSeries',
                'Image',
                'Tag',
                'User',
            ],
            '_serialize' => ['events', 'pagination'],
            'events' => $this->paginate($query),
        ]);
    }

    /**
     * Subscribes a new user to the mailing list, using default options
     *
     * @param User $user Entity of user who just registered
     * @return void
     */
    private function subscribeUserToMailingList(User $user)
    {
        // Prepare data
        $subscriptionData = [
            'email' => $user->email,
            'new_subscriber' => true,
            'all_categories' => true,
            'categories' => [],
            'weekly' => true,
        ];
        foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $day) {
            $subscriptionData["daily_$day"] = false;
        }

        // Save
        $mailingListTable = TableRegistry::getTableLocator()->get('MailingList');
        $newSubscription = $mailingListTable->newEntity($subscriptionData);
        if (!$mailingListTable->save($newSubscription)) {
            throw new BadRequestException(
                'There was an error subscribing you to the mailing list. ' .
                'Please try again or contact an administrator for assistance.'
            );
        }

        // Associate user with subscription
        $this->Users->patchEntity($user, ['mailing_list_id' => $newSubscription->id]);
        $this->Users->save($user);
    }
}
