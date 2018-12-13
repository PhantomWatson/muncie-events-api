<?php
namespace App\Controller\V1;

use App\Controller\ApiController;
use App\Model\Table\TagsTable;

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
}
