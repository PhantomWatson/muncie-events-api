<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Entity\User;
use App\Model\Table\UsersTable;
use Cake\Auth\FormAuthenticate;
use Cake\Controller\ComponentRegistry;
use Cake\Http\Exception\BadRequestException;

/**
 * Class UsersController
 * @package App\Controller
 * @property UsersTable $Users
 */
class UsersController extends ApiController
{
    /**
     * /user/register endpoint
     *
     * @return void
     * @throws BadRequestException
     */
    public function register()
    {
        $this->request->allowMethod('post');

        $user = $this->Users->newEntity($this->request->getData(), [
            'fields' => ['name', 'email', 'password']
        ]);
        $user->role = 'user';
        $user->token = $this->Users->generateToken();

        if (!$this->Users->save($user)) {
            throw new BadRequestException(
                'There was an error registering. Details: ' . print_r($user->getErrors(), true)
            );
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
            'user' => $user
        ]);
    }

    /**
     * /user/login endpoint
     *
     * @return void
     * @throws BadRequestException
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
            'user' => $user
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
                'hashers' => ['Default', 'Legacy']
            ]
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
     */
    public function view($userId)
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
            'user' => $user
        ]);
    }
}
