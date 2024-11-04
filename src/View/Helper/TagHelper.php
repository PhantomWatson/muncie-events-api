<?php

namespace App\View\Helper;

use App\Model\Entity\Event;
use Cake\Datasource\ResultSetInterface;
use Cake\ORM\TableRegistry;
use Cake\View\Helper;
use Cake\View\Helper\HtmlHelper;

/**
 * @property HtmlHelper $Html
 */
class TagHelper extends Helper
{

    /**
     * Buffers JS that pre-selects tags in the event form
     *
     * @param Event $event Event entity
     * @return void
     */
    private function preselectTags($event)
    {
        $selectedTags = [];
        if (is_array($event->tags)) {
            foreach ($event->tags as $tag) {
                $selectedTags[] = [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ];
            }
        }
        $this->Html->scriptBlock(
            sprintf('TagManager.preselectTags(%s);', json_encode($selectedTags)),
            ['block' => true]
        );
    }

    /**
     * Buffers JS for the tag menu
     *
     * @param string $containerSelector The CSS selector of the 'available tags' container
     * @param Event $event The event associated with the current page
     * @return void
     */
    public function setup($containerSelector, $event)
    {
        $this->preselectTags($event);
        $this->setAvailableTags($containerSelector);
        $this->Html->scriptBlock(
            '
                $(\'#new_tag_rules_toggler\').click(function(event) {
                    event.preventDefault();
                    $(\'#new_tag_rules\').slideToggle(200);
                });
            ',
            ['block' => true]
        );
    }

    /**
     * Buffers JS for creating a menu of selectable tags
     *
     * @param string $containerSelector The CSS selector of the 'available tags' container
     * @return void
     */
    private function setAvailableTags($containerSelector)
    {
        $tagsTable = TableRegistry::getTableLocator()->get('Tags');
        $results = $tagsTable->find('threaded')
            ->where(['listed' => true])
            ->orderAsc('name')
            ->all();
        $availableTags = $this->availableTagsToArray($results);

        $this->Html->scriptBlock(
            sprintf(
                'TagManager.createTagList(%s, $(%s));',
                json_encode($availableTags),
                json_encode($containerSelector)
            ),
            ['block' => true]
        );
    }

    /**
     * Takes a threaded resultset of available tags and returns a nested array with branches placed before leaves
     *
     * i.e. tags with children are ordered before tags without children
     *
     * @param ResultSetInterface $tags ResultSet of tags
     * @return array
     */
    private function availableTagsToArray($tags)
    {
        $tagsWithChildren = [];
        $tagsWithoutChildren = [];
        foreach ($tags as $tag) {
            $tagArray = [
                'id' => $tag->id,
                'name' => $tag->name,
                'selectable' => (bool)$tag->selectable,
                'children' => $this->availableTagsToArray($tag->children),
            ];
            if ($tag->children) {
                $tagsWithChildren[] = $tagArray;
            } else {
                $tagsWithoutChildren[] = $tagArray;
            }
        }

        return array_merge($tagsWithChildren, $tagsWithoutChildren);
    }
}
