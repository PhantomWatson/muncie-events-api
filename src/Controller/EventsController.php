<?php
namespace App\Controller;

use App\Model\Entity\Category;
use App\Model\Entity\Event;
use App\Model\Table\EventsTable;
use App\Model\Table\TagsTable;
use Cake\Datasource\ResultSetInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Exception;

/**
 * Events Controller
 *
 * @property EventsTable $Events
 * @property TagsTable $Tags
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
            'index',
            'tag'
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
            ->find('published')
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
            ->find('published')
            ->find('ordered')
            ->find('withAllAssociated')
            ->find('inCategory', ['categoryId' => $category->id]);

        $this->set([
            'category' => $category,
            'events' => $events,
            'pageTitle' => $category->name
        ]);
    }

    /**
     * Displays events with a specified tag
     *
     * @param string|null $idAndSlug A string formatted as "$id-$slug"
     * @param string|null $direction Either 'upcoming', 'past', or null (defaults to 'upcoming')
     * @return null|Response
     * @throws NotFoundException
     * @throws BadRequestException
     */
    public function tag($idAndSlug = '', $direction = null)
    {
        $this->loadModel('Tags');
        $tag = $this->Tags->getFromIdSlug($idAndSlug);
        if (!$tag) {
            throw new NotFoundException('Sorry, we couldn\'t find that tag');
        }

        $direction = $direction ?? 'upcoming';
        if (!in_array($direction, ['upcoming', 'past'])) {
            throw new BadRequestException(
                'Sorry, but due to our current one-dimensional understanding of time, you can\'t view events ' .
                'in any direction other than \'upcoming\' or \'past\'.'
            );
        }

        $baseQuery = $this->Events
            ->find('published')
            ->find('ordered', ['direction' => $direction == 'past' ? 'DESC' : 'ASC'])
            ->find('withAllAssociated')
            ->find('tagged', ['tags' => [$tag->name]]);
        $mainQuery = (clone $baseQuery)->find($direction == 'past' ? 'past' : 'future');
        $oppositeDirectionQuery = (clone $baseQuery)->find($direction == 'past' ? 'future' : 'past');

        $this->set([
            'pageTitle' => 'Tag: ' . ucwords($tag->name),
            'events' => $this->paginate($mainQuery),
            'count' => $mainQuery->count(),
            'direction' => $direction,
            'countOppositeDirection' => $oppositeDirectionQuery->count(),
            'oppositeDirection' => $direction == 'past' ? 'upcoming' : 'past',
            'tag' => $tag
        ]);

        return null;
    }
}
