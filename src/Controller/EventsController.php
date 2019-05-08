<?php
namespace App\Controller;

use App\Model\Entity\Event;
use App\Model\Table\EventsTable;
use Cake\Datasource\ResultSetInterface;

/**
 * Events Controller
 *
 * @property EventsTable $Events
 *
 * @method Event[]|ResultSetInterface paginate($object = null, array $settings = [])
 */
class EventsController extends AppController
{
    /**
     * Index method
     *
     * @param string|null $startDate The earliest date to fetch events for
     * @return void
     */
    public function index($startDate = null)
    {
        $pageSize = '1 month';
        $startDate = $startDate ?? date('Y-m-d');
        $endDate = strtotime($startDate . ' + ' . $pageSize);
        $events = $this->Events
            ->find('ordered')
            ->find('startingOn', ['date' => $startDate])
            ->find('endingOn', ['date' => $endDate]);
        $this->set(['events' => $events]);
    }
}
