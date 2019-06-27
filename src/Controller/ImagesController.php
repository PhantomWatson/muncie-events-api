<?php

namespace App\Controller;

use App\Model\Entity\Image;
use App\Model\Table\ImagesTable;
use App\Model\Table\UsersTable;
use Cake\Datasource\ResultSetInterface;

/**
 * Images Controller
 *
 * @property ImagesTable $Images
 * @property UsersTable $Users
 *
 * @method Image[]|ResultSetInterface paginate($object = null, array $settings = [])
 */
class ImagesController extends AppController
{
    /**
     * Displays a collection of this user's previously-uploaded images
     *
     * @param int $userId User ID
     * @return void
     */
    public function userImages($userId)
    {
        $this->viewbuilder()->setLayout('ajax');
        $this->loadModel('Users');
        $this->set([
            'images' => $this->Users->getImagesList($userId)
        ]);
    }
}
