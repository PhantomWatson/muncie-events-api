<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Table\EventsTable;
use App\Model\Table\TagsTable;
use Cake\Database\Expression\QueryExpression;
use Cake\Http\Exception\BadRequestException;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;

/**
 * Class TagsController
 * @package App\Controller\V1
 * @property TagsTable $Tags
 */
class TagsController extends ApiController
{
    /**
     * /tags/tree endpoint
     *
     * @return void
     */
    public function tree()
    {
        $this->request->allowMethod('get');

        $tags = $this->Tags
            ->find('threaded')
            ->orderAsc('name')
            ->toArray();
        $tags = $this->filterOutHiddenTags($tags);

        $this->set([
            '_entities' => ['Tag'],
            '_include' => ['children'],
            '_serialize' => ['tags'],
            'tags' => $tags,
        ]);
    }

    /**
     * Removes any tags that are unlisted or descend from an unlisted tag
     *
     * @param array $tags Array of tags
     * @return array
     */
    private function filterOutHiddenTags(array $tags)
    {
        foreach ($tags as $k => $tag) {
            if (!$tag['listed']) {
                unset($tags[$k]);
            }
            if (isset($tag['children']) && $tag['children']) {
                $tag['children'] = $this->filterOutHiddenTags($tag['children']);
            }
        }

        return $tags;
    }

    /**
     * /tags/future endpoint
     *
     * @return void
     */
    public function future()
    {
        $this->request->allowMethod('get');

        /** @var EventsTable $eventsTable */
        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        $tags = $eventsTable->getUpcomingEventTags();

        $this->set([
            '_entities' => ['Tag'],
            '_serialize' => ['tags'],
            'tags' => $tags,
        ]);
    }

    /**
     * /tag/{tagId} endpoint
     *
     * @param null $tagId Tag ID
     * @return void
     * @throws BadRequestException
     */
    public function view($tagId = null)
    {
        $this->request->allowMethod('get');

        if (!$tagId) {
            throw new BadRequestException('Required tag ID is missing');
        }

        $tag = $this->Tags->find()
            ->where(['id' => $tagId])
            ->contain([
                'Events' => function (Query $q) {
                    return $q
                        ->find('forApi')
                        ->find('future');
                },
            ])
            ->first();
        if (!$tag) {
            throw new BadRequestException('Invalid tag ID: ' . $tagId);
        }

        $this->set([
            '_entities' => [
                'Category',
                'Event',
                'EventSeries',
                'Image',
                'Tag',
                'User',
            ],
            '_serialize' => ['tag'],
            '_include' => ['events'],
            'tag' => $tag,
        ]);
    }

    /**
     * GET /v1/tags endpoint
     *
     * Returns all listed and selectable tags
     *
     * @return void
     */
    public function index()
    {
        $this->request->allowMethod('get');

        $tags = $this->Tags
            ->find()
            ->select(['id', 'name'])
            ->where([
                'listed' => true,
                'selectable' => true,
            ])
            ->orderAsc('name')
            ->toArray();

        $this->set([
            '_entities' => ['Tag'],
            '_serialize' => ['tags'],
            'tags' => $tags,
        ]);
    }

    /**
     * GET /v1/tags/autocomplete endpoint
     *
     * Returns a list of matching (and listed and selectable) tags
     *
     * @return void
     * @throws BadRequestException
     */
    public function autocomplete()
    {
        $this->request->allowMethod('get');

        $term = $this->request->getQuery('term');
        if (empty($term)) {
            throw new BadRequestException('Search term missing');
        }

        $limit = $this->request->getQuery('limit') === null ? 10 : $this->request->getQuery('limit');
        if (!is_numeric($limit) || $limit < 1) {
            throw new BadRequestException("Invalid limit: $limit");
        }

        // Tag.name will be compared via LIKE to each of these, in order, until $limit tags are found
        $likeConditions = [
            $term,
            $term . ' %',
            $term . '%',
            '% ' . $term . '%',
            '%' . $term . '%',
        ];

        // Collect tags up to $limit
        $tags = [];
        foreach ($likeConditions as $like) {
            if (count($tags) == $limit) {
                break;
            }
            $conditions = [
                'listed' => true,
                'selectable' => true,
                function (QueryExpression $exp) use ($like) {
                    return $exp->like('name', $like);
                },
            ];
            if (!empty($tags)) {
                $tagIds = array_keys($tags);
                $conditions[] = function (QueryExpression $exp) use ($tagIds) {
                    return $exp->notIn('id', $tagIds);
                };
            }
            $query = $this->Tags->find()
                ->select(['id', 'name'])
                ->where($conditions)
                ->limit($limit - count($tags));
            foreach ($query->all() as $result) {
                $tags[$result->id] = $result;
            }
        }

        $this->set([
            '_entities' => ['Tag'],
            '_serialize' => ['tags'],
            'tags' => $tags,
        ]);
    }
}
