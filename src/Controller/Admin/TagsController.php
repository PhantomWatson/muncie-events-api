<?php
namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\User;
use App\Model\Table\EventsTagsTable;
use App\Model\Table\TagsTable;
use Cake\Database\Expression\QueryExpression;
use Cake\Http\Exception\BadRequestException;
use Cake\Utility\Hash;
use Exception;

/**
 * Tags Controller
 *
 * @property \App\Model\Table\TagsTable $Tags
 * @property \App\Model\Table\EventsTagsTable $EventsTags
 */
class TagsController extends AppController
{
    /**
     * Initialization hook method
     *
     * @return void
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->Auth->deny();
    }

    /**
     * Returns TRUE if the user is authorized to make the current request
     *
     * @param User|null $user User entity
     * @return bool
     */
    public function isAuthorized($user = null)
    {
        return $user['role'] == 'admin';
    }

    /**
     * "Manage tags" page
     *
     * @return void
     */
    public function manage(): void
    {
        $newTag = $this->Tags->newEmptyEntity();
        $this->set([
            'pageTitle' => 'Manage Tags',
            'newTag' => $newTag,
        ]);
    }

    /**
     * Recovers the tree structure of the tags table and redirects back to the manage page
     *
     * @return \Cake\Http\Response
     */
    public function recover()
    {
        list($startUsec, $startSec) = explode(' ', microtime());
        set_time_limit(3600);
        $this->Tags->recover();
        list($endUsec, $endSec) = explode(' ', microtime());
        $startTime = $startUsec + $startSec;
        $endTime = $endUsec + $endSec;
        $loadingTime = $endTime - $startTime;
        $minutes = round($loadingTime / 60, 2);

        $this->Flash->success("Done recovering tag tree (took $minutes minutes).");

        return $this->redirect(['action' => 'manage', '#' => 'tax-fix']);
    }

    /**
     * Getnodes page, used by the tags tree
     *
     * @return void
     */
    public function getNodes()
    {
        // retrieve the node id that Ext JS posts via ajax
        $parentId = isset($_POST['node']) && $_POST['node'] ? intval($_POST['node']) : null;

        // find all the nodes underneath the parent node defined above
        $nodes = $parentId
            ? $this->Tags
                ->find('children', ['for' => $parentId, 'direct' => true])
                ->toArray()
            : $this->Tags
                ->find()
                ->where([function (QueryExpression $exp) {
                    return $exp->isNull('parent_id');
                }])
                ->toArray();

        $rearrangedNodes = ['branches' => [], 'leaves' => []];
        /** @var EventsTagsTable $eventTagsTable */
        $eventsTagsTable = $this->fetchTable('EventsTags');
        foreach ($nodes as &$node) {
            /** @var \App\Model\Entity\Tag $node */
            $tagId = $node->id;

            // Check for events associated with this tag
            if ($node->selectable) {
                $count = $eventsTagsTable
                    ->find()
                    ->where(['tag_id' => $tagId])
                    ->count();

                $node->no_events = !$count;
            }

            // Check for children
            if ($this->Tags->childCount($node, true)) {
                $tagName = $node->name;
                $rearrangedNodes['branches'][$tagName] = $node;
            } else {
                $rearrangedNodes['leaves'][$tagId] = $node;
            }
        }

        // Sort nodes by alphabetical branches, then alphabetical leaves
        ksort($rearrangedNodes['branches']);
        ksort($rearrangedNodes['leaves']);
        $nodes = array_merge(
            array_values($rearrangedNodes['branches']),
            array_values($rearrangedNodes['leaves'])
        );

        // Visually note categories with no data
        $showNoEvents = true;

        // send the nodes to our view
        $this->set(compact('nodes', 'showNoEvents'));

        $this->viewBuilder()->setLayout('ajax');
    }

    /**
     * Reorder page, used by the tags tree
     *
     * @return void
     */
    public function reorder()
    {
        // retrieve the node instructions from javascript
        // delta is the difference in position (1 = next node, -1 = previous node)

        $nodeId = intval($_POST['node']);
        $delta = intval($_POST['delta']);
        $node = $this->Tags->get($nodeId);
        $success = true;

        if ($delta > 0) {
            $success = (bool)$this->Tags->moveDown($node, abs($delta));
        } elseif ($delta < 0) {
            $success = (bool)$this->Tags->moveUp($node, abs($delta));
        }
        $this->set([
            '_serialize' => ['success'],
            'success' => $success ? 1 : 0,
        ]);
    }

    /**
     * Reparent page, used by the tags tree
     *
     * @return void
     */
    public function reparent()
    {
        $tagId = intval($_POST['node']);
        $parentId = ($_POST['parent'] == 'root') ? null : intval($_POST['parent']);
        $inUnlistedBefore = $this->Tags->isUnderUnlistedGroup($tagId);
        $inUnlistedAfter = ($_POST['parent'] == 'root') ? false : $this->Tags->isUnderUnlistedGroup($parentId);
        $tag = $this->Tags->get($tagId);

        // Moving out of the 'Unlisted' group
        if ($inUnlistedBefore && !$inUnlistedAfter) {
            $this->Tags->patchEntity($tag, ['listed' => true]);
        }

        // Moving into the 'Unlisted' group
        if (!$inUnlistedBefore && $inUnlistedAfter) {
            $this->Tags->patchEntity($tag, ['listed' => false]);
        }

        // Move tag
        $this->Tags->patchEntity($tag, ['parent_id' => $parentId]);

        // If position == 0, then we move it straight to the top
        // otherwise we calculate the distance to move ($delta).
        // We have to check if $delta > 0 before moving due to a bug
        // in the tree behaviour (https://trac.cakephp.org/ticket/4037)
        $position = intval($_POST['position']);
        if ($position == 0) {
            $this->Tags->moveUp($tag, true);
        } else {
            $count = $this->Tags->find('children', ['for' => $parentId])->count();
            $delta = $count - $position - 1;
            if ($delta > 0) {
                $this->Tags->moveUp($tag, $delta);
            }
        }

        $success = (bool)$this->Tags->save($tag);

        $this->set([
            '_serialize' => ['success'],
            'success' => $success ? 1 : 0,
        ]);
    }

    /**
     * Returns a path from the root of the Tag tree to the tag with the provided name
     *
     * @param string $tagName Tag name
     * @return void
     */
    public function trace($tagName = '')
    {
        $path = [];
        /** @var \App\Model\Entity\Tag $targetTag */
        $targetTag = $this->Tags
            ->find()
            ->select(['id', 'name', 'parent_id'])
            ->where([['name' => $tagName]])
            ->first();

        if ($targetTag) {
            $path[] = "$targetTag->name ($targetTag->id)";
            if ($targetTag->parent_id) {
                $rootFound = false;
                while (!$rootFound) {
                    /** @var \App\Model\Entity\Tag $parent */
                    $parent = $this->Tags
                        ->find()
                        ->select(['Tag.id', 'Tag.name', 'Tag.parent_id'])
                        ->where(['id' => $targetTag->parent_id])
                        ->first();
                    if ($parent) {
                        $path[] = "$parent->name ($parent->id)";
                        if (!$targetTag->parent_id = $parent->parent_id) {
                            $rootFound = true;
                        }
                    } else {
                        $path[] = "(Parent data tag with id $targetTag->parent_id not found)";
                        break;
                    }
                }
            }
        } else {
            $path[] = "(Tag named '$tagName' not found)";
        }

        $path = array_reverse($path);
        $this->set(compact('path'));
        $this->viewbuilder()->setLayout('ajax');
    }

    /**
     * Removes at tag
     *
     * @param string $name Tag name
     * @return void
     */
    public function remove($name)
    {
        $tag = $this->Tags->findByName($name)->first();
        if (!$tag) {
            $message = "The tag \"$name\" does not exist (you may have already deleted it).";
            $class = 'error';
        } elseif ($this->Tags->delete($tag)) {
            $message = "Tag \"$name\" deleted.";
            $class = 'success';
        } else {
            $message = "There was an unexpected error deleting the \"$name\" tag.";
            $class = 'error';
        }

        $this->set([
            'message' => $message,
            'class' => $class,
        ]);
        $this->viewbuilder()->setLayout('ajax');
    }

    /**
     * Turns all associations with removed Tag into associations with retained Tag,
     * deletes the Tag designated for removal, and reparents any of its child tags to the retained Tag
     *
     * @return null
     */
    public function merge()
    {
        $redirectTo = ['action' => 'manage', '#' => 'tab-merge'];
        $removedTagName = trim($this->request->getData('removed_tag_name'));
        $retainedTagName = trim($this->request->getData('retained_tag_name'));

        // Verify input
        if ($removedTagName == '') {
            $this->Flash->error('No name provided for the tag to be removed.');

            return $this->redirect($redirectTo);
        } else {
            $removedTag = $this->Tags->findByName($removedTagName)->first();
            if (!$removedTag) {
                $this->Flash->error("The tag \"$removedTagName\" could not be found.");

                return $this->redirect($redirectTo);
            }
        }
        if ($retainedTagName == '') {
            $this->Flash->error('No name provided for the tag to be retained.');

            return $this->redirect($redirectTo);
        } else {
            $retainedTag = $this->Tags->findByName($retainedTagName)->first();
            if (!$retainedTag) {
                $this->Flash->error("The tag \"$retainedTagName\" could not be found.");

                return $this->redirect($redirectTo);
            }
        }
        if ($removedTag->id == $retainedTag->id) {
            $this->Flash->error("Cannot merge \"$retainedTagName\" into itself.");

            return $this->redirect($redirectTo);
        }

        // Switch event associations
        /** @var EventsTagsTable $eventTagsTable */
        $eventsTagsTable = $this->fetchTable('EventsTags');
        /** @var \App\Model\Entity\EventsTag[] $associations */
        $associations = $eventsTagsTable
            ->find()
            ->where(['tag_id' => $removedTag->id])
            ->all();
        foreach ($associations as $association) {
            $eventsTagsTable->patchEntity($association, ['tag_id' => $retainedTag->id]);
            $eventsTagsTable->save($association);
        }
        if ($associations) {
            $message = sprintf(
                'Changed association with "%s" into "%s" in %s event%s.\n',
                $removedTagName,
                $retainedTagName,
                count($associations),
                count($associations) == 1 ? '' : 's'
            );
        } else {
            $message = 'No associated events to edit.\n';
        }

        // Move child tags
        $children = $this->Tags
            ->find()
            ->where(['parent_id' => $removedTag->id])
            ->all();
        $success = true;
        if (!$children) {
            $message .= 'No child-tags to move.\n';
        } else {
            foreach ($children as $childTag) {
                $this->Tags->patchEntity($childTag, ['parent_id' => $retainedTag->id]);
                $success = (bool)$this->Tags->save($childTag);
                $message .= sprintf(
                    '%s "%s" from under "%s" to under "%s".\n',
                    $success ? 'Moved' : 'Error moving',
                    $childTag->name,
                    $removedTagName,
                    $retainedTagName
                );
            }
        }

        // Delete tag
        if ($success) {
            if ($this->Tags->delete($removedTag)) {
                $message .= "Removed \"$removedTagName\".";
            } else {
                $message .= "Error trying to delete \"$removedTagName\" from the database.";
                $success = false;
            }
        } else {
            $message .= "\"$removedTagName\" not removed.";
        }

        if ($success) {
            $this->Flash->success($message);
        } else {
            $this->Flash->error($message);
        }

        return $this->redirect($redirectTo);
    }

    /**
     * A version of the autocomplete endpoint that returns unselectable and unlisted tags
     *
     * @return void
     */
    public function autocomplete()
    {
        $this->request->allowMethod('get');

        $term = $this->request->getQuery('term');
        if (empty($term)) {
            throw new BadRequestException('Search term missing');
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
        $limit = 10;
        foreach ($likeConditions as $like) {
            if (count($tags) == $limit) {
                break;
            }
            $conditions = [
                function (QueryExpression $exp) use ($like) {
                    return $exp->like('name', $like);
                },
            ];
            if (!empty($tags)) {
                $tagIds = Hash::extract($tags, '{n}.id');
                $conditions[] = function (QueryExpression $exp) use ($tagIds) {
                    return $exp->notIn('id', $tagIds);
                };
            }
            $query = $this->Tags->find()
                ->select(['id', 'name'])
                ->where($conditions)
                ->limit($limit - count($tags));
            foreach ($query->all() as $result) {
                $tags[] = $result;
            }
        }

        $this->set([
            '_entities' => ['Tag'],
            '_serialize' => ['tags'],
            'tags' => $tags,
        ]);
    }

    /**
     * Removes all tags in the 'delete' group
     *
     * @return void
     */
    public function emptyDeleteGroup()
    {
        $children = $this->Tags
            ->find('children', ['for' => TagsTable::DELETE_GROUP_ID])
            ->all();
        foreach ($children as $child) {
            $this->Tags->delete($child);
        }

        $this->set([
            '_serialize' => ['message', 'class'],
            'message' => 'Delete group emptied',
            'class' => 'success',
        ]);
    }

    /**
     * @param null $tagName Tag name
     * @return null
     */
    public function edit($tagName = null)
    {
        if ($this->request->is('ajax')) {
            $this->viewbuilder()->setLayout('ajax');
        }

        // Process form
        if ($this->request->is(['put', 'post'])) {
            $name = strtolower(trim($this->request->getData('name')));
            $newParentId = $this->request->getData('parent_id');
            $newParentId = is_string($newParentId) ? trim($newParentId) : $newParentId;
            if (!$newParentId) {
                $newParentId = null;
            }
            $tagId = $this->request->getData('id');
            $duplicates = $this->Tags
                ->find()
                ->where([
                    'name' => $name,
                    function (QueryExpression $exp) use ($tagId) {
                        return $exp->not(['id' => $tagId]);
                    },
                ])
                ->count();
            if ($duplicates) {
                $message = sprintf(
                    'That tag\'s name cannot be changed to "%s" because another tag already has that name. ' .
                    'You can, however, merge this tag into that tag.',
                    $this->request->getData('name')
                );

                $this->set([
                    'message' => $message,
                    'class' => 'error',
                ]);

                return null;
            }

            // Set flag to recover tag tree if necessary
            $tag = $this->Tags->get($tagId);
            $previousParentId = $tag->parent_id;
            $recoverTagTree = ($previousParentId != $newParentId);

            $listed = $this->request->getData('listed');
            $data = [
                'parent_id' => $newParentId,
                'name' => $name,
                'listed' => $listed,
                'selectable' => $this->request->getData('selectable'),
            ];

            if ($this->Tags->save($tag, $data)) {
                if ($recoverTagTree) {
                    $this->Tags->recover();
                }
                $message = 'Tag successfully edited.';
                if ($listed && $this->Tags->isUnderUnlistedGroup($tagId)) {
                    $message .= '<br /><strong>This tag is now listed, but is still in the "Unlisted" group. ' .
                        'It is recommended that it now be moved out of that group.</strong>';
                }

                $this->set([
                    'message' => $message,
                    'class' => 'success',
                ]);

                return null;
            }

            $this->set([
                'message' => 'There was an error editing that tag.',
                'class' => 'error',
            ]);

            return null;
        }

        // Report missing tag name
        if (!$tagName) {
            $this->set([
                'title' => 'Tag Name Not Provided',
                'message' => 'Please try again. But with a tag name provided this time.',
                'class' => 'error',
            ]);

            return null;
        }

        // Report unknown tag
        $result = $this->Tags->findByName($tagName)->all();
        if (empty($result)) {
            $this->set([
                'title' => 'Tag Not Found',
                'message' => "Could not find a tag with the exact tag name \"$tagName\".",
                'class' => 'error',
            ]);

            return null;
        }

        // Alert user to duplicate tags
        if (count($result) > 1) {
            $tagIds = [];
            foreach ($result as $tag) {
                $tagIds[] = $tag->id;
            }

            $this->set([
                'title' => 'Duplicate Tags Found',
                'message' => "Tags with the following IDs are named \"$tagName\": " . implode(', ', $tagIds) .
                    '<br />You will need to merge them before editing.',
                'class' => 'error',
            ]);

            return null;
        }

        // Pass entity to form
        $this->set('tag', $result->first());

        return null;
    }

    /**
     * Page for adding a new tag
     *
     * @return void
     */
    public function add()
    {
        $this->viewbuilder()->setLayout('ajax');
        $this->request->allowMethod('post');
        if (trim($this->request->getData('name')) == '') {
            $this->set([
                'title' => 'Error',
                'message' => 'Tag name is blank',
                'class' => 'error',
            ]);

            return;
        }

        // Determine parent_id
        $parentName = $this->request->getData('parent_name');
        if ($parentName == '') {
            $rootParentId = null;
        } else {
            $rootParentId = $this->Tags->findByName($parentName)->first()->id;
            if (!$rootParentId) {
                $this->set([
                    'title' => 'Error',
                    'message' => "Parent tag \"$parentName\" not found",
                    'class' => 'error',
                ]);

                return;
            }
        }

        $class = 'success';
        $message = '';
        $names = trim(strtolower($this->request->getData('name')));
        $splitNames = explode("\n", $names);
        $parents = [$rootParentId];
        foreach ($splitNames as $lineNum => $name) {
            $level = $this->getIndentLevel($name);

            // Discard any now-irrelevant data
            $parents = array_slice($parents, 0, $level + 1);

            // Determine this tag's parent_id
            if ($level == 0) {
                $parentId = $rootParentId;
            } elseif (isset($parents[$level])) {
                $parentId = $parents[$level];
            } else {
                $class = 'error';
                $message .= 'Error with nested tag structure. ' .
                    "Looks like there's an extra indent in line $lineNum: \"$name\".<br />";
                continue;
            }

            // Strip leading/trailing whitespace and hyphens used for indenting
            $name = trim(ltrim($name, '-'));

            // Confirm that the tag name is non-blank and non-redundant
            if (!$name) {
                continue;
            }

            if ($this->Tags->exists(['name' => $name])) {
                $class = 'error';
                $message .= "Cannot create the tag \"$name\" because a tag with that name already exists.<br />";
                continue;
            }

            // Add tag to database
            $tag = $this->Tags->newEntity([
                'name' => $name,
                'parent_id' => $parentId,
                'listed' => 1,
                'selectable' => 1,
            ]);
            if ($this->Tags->save($tag)) {
                $message .= "Created tag #{$tag->id}: $name<br />";
                $parents[$level + 1] = $tag->id;
            } else {
                $class = 'error';
                $message .= "Error creating the tag \"$name\"<br />";
            }
        }

        $this->set([
            'title' => 'Results:',
            'message' => $message,
            'class' => $class,
        ]);
    }

    /**
     * Used by the tag adder (in the tag manager) to determine how indented a line is
     *
     * @param string $name Tag name
     * @return number
     */
    private function getIndentLevel($name)
    {
        $level = 0;
        $length = strlen($name);
        for ($i = 0; $i < $length; $i++) {
            if ($name[$i] == "\t" || $name[$i] == '-') {
                $level++;
            } else {
                break;
            }
        }

        return $level;
    }
}
