<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Table\UsersTable;
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
        foreach (['email', 'password'] as $field) {
            if (!$this->request->getData($field)) {
                throw new BadRequestException('The parameter "' . $field . '" is required');
            }
        }

        $user = $this->Auth->identify();
        if (!$user) {
            throw new BadRequestException('Email or password is incorrect');
        }

        // Convert user array into user entity, as required by JsonApi view
        $user = $this->Users
            ->find()
            ->select(['id', 'name', 'email', 'token'])
            ->where(['id' => $user['id']])
            ->first();

        $this->set([
            '_entities' => ['User'],
            '_serialize' => ['user'],
            'user' => $user
        ]);
    }
}