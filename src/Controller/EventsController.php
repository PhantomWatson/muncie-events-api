<?php
namespace App\Controller;

use App\Model\Entity\Category;
use App\Model\Entity\Event;
use App\Model\Table\EventsTable;
use Cake\Datasource\ResultSetInterface;
use Cake\Http\Exception\NotFoundException;
use Exception;

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
     * Initialization hook method
     *
     * @return void
     * @throws Exception
     */
    public function initialize()
    {
        parent::initialize();

        $this->Auth->allow([
            'category',
            'index'
        ]);
    }

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
        $endDate = date('Y-m-d', strtotime($startDate . ' + ' . $pageSize));
        $events = $this->Events
            ->find('ordered')
            ->find('startingOn', ['date' => $startDate])
            ->find('endingOn', ['date' => $endDate])
            ->find('withAllAssociated')
            ->all();
        $this->set([
            'events' => $events
        ]);
    }

    /**
     * Displays events in the specified category
     *
     * @param string $slug Slug of category name
     * @param string|null $startDate The earliest date to fetch events for
     * @return void
     * @throws NotFoundException
     */
    public function category($slug)
    {
        /** @var Category $category */
        $category = $this->Events->Categories
            ->find()
            ->where(['slug' => $slug])
            ->first();
        if (!$category) {
            throw new NotFoundException(sprintf('The "%s" event category was not found', $slug));
        }

        $events = $this->Events
            ->find('future')
            ->find('ordered')
            ->find('withAllAssociated')
            ->find('inCategory', ['categoryId' => $category->id]);

        $this->set([
            'category' => $category,
            'events' => $events,
            'pageTitle' => $category->name
        ]);
    }
}
