<?php
namespace App\Controller\V1;

use App\Controller\AppController;
use App\Model\Entity\User;
use Cake\Event\Event;
use Cake\Network\Exception\BadRequestException;
use Cake\Routing\Router;

class EventsController extends AppController
{
    /**
     * Initialize method
     *
     * @return \Cake\Http\Response|null
     */
    public function initialize()
    {
        parent::initialize();

        if (!$this->request->is('ssl')) {
            throw new BadRequestException('API calls must be made with HTTPS protocol');
        }

        $this->viewBuilder()->setClassName('JsonApi.JsonApi');

        $this->set('_url', Router::url('/v1', true));

        $this->loadComponent('ApiPagination');

        return null;
    }

    /**
     * isAuthorized method
     *
     * @param User $user User entity
     * @return bool
     */
    public function isAuthorized($user)
    {
        return true;
    }

    /**
     * /events endpoint
     *
     * @return void
     */
    public function index()
    {
        $start = $this->request->getQuery('start');
        $end = $this->request->getQuery('end');
        if (!$start) {
            throw new BadRequestException('The parameter "start" is required');
        }

        $query = $this->Events
            ->find('forApi')
            ->find('startingOn', ['date' => $start])
            ->find('endingOn', ['date' => $end]);

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'Tag',
                'User'
            ],
            '_serialize' => ['events'],
            'events' => $this->paginate($query)
        ]);
    }

    /**
     * /events/future endpoint
     *
     * @return void
     */
    public function future()
    {
        $query = $this->Events
            ->find('forApi')
            ->find('startingOn', ['date' => date('Y-m-d')]);

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'Tag',
                'User'
            ],
            '_serialize' => ['events', 'pagination'],
            'events' => $this->paginate($query)
        ]);
    }
}
