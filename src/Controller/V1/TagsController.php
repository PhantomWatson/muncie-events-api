<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Table\EventsTable;
use App\Model\Table\TagsTable;
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
        $tags = $this->Tags
            ->find('threaded')
            ->orderAsc('name')
            ->toArray();
        $tags = $this->filterOutHiddenTags($tags);

        $this->set([
            '_entities' => ['Tag'],
            '_include' => ['children'],
            '_serialize' => ['tags'],
            'tags' => $tags
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
            if ($tag['children']) {
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
        /** @var EventsTable $eventsTable */
        $eventsTable = TableRegistry::getTableLocator()->get('Events');
        $tags = $eventsTable->getUpcomingEventTags();

        $this->set([
            '_entities' => ['Tag'],
            '_serialize' => ['tags'],
            'tags' => $tags
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
                }
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
                'User'
            ],
            '_serialize' => ['tag'],
            '_include' => ['events'],
            'tag' => $tag
        ]);
    }
}
