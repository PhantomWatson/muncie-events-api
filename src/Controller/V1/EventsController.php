<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Entity\User;
use Cake\Network\Exception\BadRequestException;
use Cake\Routing\Router;

class EventsController extends ApiController
{
    public $paginate = [
        'limit' => 50,
        'order' => [
            'Events.date' => 'asc',
            'Events.time_start' => 'asc',
        ]
    ];

    /**
     * Initialize method
     *
     * @return \Cake\Http\Response|null
     */
    public function initialize()
    {
        parent::initialize();

        $this->Auth->deny();

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
        $tags = $this->request->getQuery('withTags');
        if (!$start) {
            throw new BadRequestException('The parameter "start" is required');
        }

        $query = $this->Events
            ->find('forApi')
            ->find('startingOn', ['date' => $start])
            ->find('endingOn', ['date' => $end])
            ->find('tagged', ['tags' => $tags]);

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
        $tags = $this->request->getQuery('withTags');
        $query = $this->Events
            ->find('forApi')
            ->find('startingOn', ['date' => date('Y-m-d')])
            ->find('tagged', ['tags' => $tags]);

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
