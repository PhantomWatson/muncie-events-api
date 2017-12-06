<?php
namespace App\Controller\V1;

use App\Controller\AppController;
use App\Model\Entity\User;
use Cake\Database\Expression\QueryExpression;
use Cake\Network\Exception\BadRequestException;
use Cake\ORM\Query;
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
        $isDate = function ($date) {
            return preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}\z/', $date);
        };

        $start = $this->request->getQuery('start');
        $end = $this->request->getQuery('end');
        if (!$start) {
            throw new BadRequestException('The parameter "start" is required');
        }
        if (!$isDate($start)) {
            throw new BadRequestException('The "start" parameter must be in the format YYYY-MM-DD');
        }
        if ($end && !$isDate($end)) {
            throw new BadRequestException('The "end" parameter must be in the format YYYY-MM-DD');
        }

        /** @var Query $query */
        $query = $this->Events->find()
            ->where([
                'published' => true,
                function ($exp) use ($start) {
                    /** @var QueryExpression $exp */
                    return $exp->gte('date', $start);
                }
            ])
            ->contain([
                'Categories',
                'Tags',
                'Users'
            ]);
        if ($end) {
            $query->where(function ($exp) use ($start) {
                /** @var QueryExpression $exp */
                return $exp->lte('date', $start);
            });
        }

        $results = $query->all();

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'Tag',
                'User'
            ],
            '_serialize' => ['events'],
            'events' => $results
        ]);
    }
}
