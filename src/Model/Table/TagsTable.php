<?php
namespace App\Model\Table;

use App\Model\Entity\Tag;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Behavior\TreeBehavior;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Tags Model
 *
 * @property \App\Model\Table\TagsTable&\Cake\ORM\Association\BelongsTo $ParentTags
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\TagsTable&\Cake\ORM\Association\HasMany $ChildTags
 * @property \App\Model\Table\EventsTable&\Cake\ORM\Association\BelongsToMany $Events
 * @property Tag[] $children
 *
 * @method \App\Model\Entity\Tag get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Tag newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Tag[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Tag|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Tag patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Tag[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Tag findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method Query findByName($name)
 *
 * @mixin TimestampBehavior
 * @mixin TreeBehavior
 * @method \App\Model\Entity\Tag newEmptyEntity()
 * @method \App\Model\Entity\Tag saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Tag[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Tag[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Tag[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Tag[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 * @mixin \Cake\ORM\Behavior\TreeBehavior
 */
class TagsTable extends Table
{
    const UNLISTED_GROUP_ID = 1012;
    const DELETE_GROUP_ID = 1011;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('tags');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Tree');

        $this->belongsTo('ParentTags', [
            'className' => 'Tags',
            'foreignKey' => 'parent_id',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('ChildTags', [
            'className' => 'Tags',
            'foreignKey' => 'parent_id',
        ]);
        $this->belongsToMany('Events', [
            'foreignKey' => 'tag_id',
            'targetForeignKey' => 'event_id',
            'joinTable' => 'events_tags',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        $validator
            ->integer('id');

        $validator
            ->scalar('name')
            ->allowEmptyString('name', 'Tag name required', false);

        $validator
            ->boolean('listed')
            ->requirePresence('listed', 'create');

        $validator
            ->boolean('selectable')
            ->requirePresence('selectable', 'create');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param RulesChecker $rules The rules object to be modified.
     * @return RulesChecker
     */
    public function buildRules(RulesChecker $rules): \Cake\ORM\RulesChecker
    {
        $rules->add($rules->existsIn(['parent_id'], 'ParentTags'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        $rules->add($rules->isUnique(['name']), 'uniqueName', [
            'message' => 'There is already another tag with that name.',
        ]);

        return $rules;
    }

    /**
     * Takes a string formatted as "$id-$slug" and returns a tag entity or FALSE
     *
     * @param string|null $idAndSlug A string formatted as "$id-$slug"
     * @return bool|Tag
     */
    public function getFromIdSlug(?string $idAndSlug)
    {
        if (strpos($idAndSlug, '-') === false && strpos($idAndSlug, '_') === false) {
            return false;
        }

        $tagId = (explode('-', $idAndSlug))[0];
        $tagId = (explode('_', $tagId))[0];

        /** @var Tag $tag */
        $tag = $this->find()
            ->select(['id', 'name'])
            ->where(['id' => $tagId])
            ->first();

        return $tag ? $tag : false;
    }

    /**
     * Returns TRUE if the tag with the provided ID is a descendent of the "Unlisted" tag group
     *
     * @param int|null $tagId Tag ID
     * @return bool
     */
    public function isUnderUnlistedGroup($tagId)
    {
        if (!$tagId) {
            return false;
        }

        for ($n = 0; $n <= 100; $n++) {
            try {
                $tag = $this->get($tagId);
            } catch (RecordNotFoundException $e) {
                return false;
            }

            // Child of root
            if (empty($tag->parent_id)) {
                return false;
            }

            // Child of 'unlisted'
            if ($tag->parent_id == self::UNLISTED_GROUP_ID) {
                return true;
            }

            // Go up a level
            $tagId = $tag->parent_id;
        }

        // Assume that after 100 levels, a circular path must have been found and exit
        return false;
    }

    /**
     * Returns an array of the filtered and published Event IDs associated with the specified Tags
     *
     * @param int[] $tagIds Tag IDs
     * @param int|null $categoryFilter Category ID
     * @param string|null $locationFilter Location name
     * @return array|array[]|\ArrayAccess|\ArrayAccess[]
     */
    public function getFilteredAssociatedEventIds($tagIds, $categoryFilter, $locationFilter)
    {
        $results = $this
            ->find()
            ->where(function (QueryExpression $exp) use ($tagIds) {
                return $exp->in('Tags.id', $tagIds);
            })
            ->select(['Tags.id'])
            ->contain([
                'Events' => function (Query $q) use ($categoryFilter, $locationFilter) {
                    $q
                        ->find('published')
                        ->select(['Events.id']);

                    if ($categoryFilter) {
                        $q->where(function (QueryExpression $exp) use ($categoryFilter) {
                            if (is_int($categoryFilter)) {
                                $categoryFilter = [$categoryFilter];
                            }

                            return $exp->in('category_id', $categoryFilter);
                        });
                    }

                    if ($locationFilter) {
                        $q->where(function (QueryExpression $exp) use ($locationFilter) {
                            return $exp->like('location', "%$locationFilter%");
                        });
                    }

                    return $q;
                },
            ])
            ->toArray();

        $eventIds = [];
        foreach ($results as $tag) {
            foreach ($tag->events as $event) {
                $eventIds[] = $event->id;
            }
        }

        return $eventIds;
    }
}
