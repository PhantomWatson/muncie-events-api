<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\User;
use Cake\Http\Response;

class AdminController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event): ?Response
    {
        parent::beforeFilter($event);

        $authUser = $this->getAuthUser();
        if ($authUser?->role !== User::ROLE_ADMIN) {
            $this->Flash->error('You are not logged in to an admin account');
            return $this->redirect($this->referer());
        }

        return null;
    }
}
