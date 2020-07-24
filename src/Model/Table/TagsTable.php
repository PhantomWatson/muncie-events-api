<?php
namespace App\Model\Table;

use App\Model\Entity\Tag;
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
 * @property TagsTable|BelongsTo $ParentTags
 * @property UsersTable|BelongsTo $Users
 * @property TagsTable|HasMany $ChildTags
 * @property EventsTable|BelongsToMany $Events
 * @property Tag[] $children
 *
 * @method Tag get($primaryKey, $options = [])
 * @method Tag newEntity($data = null, array $options = [])
 * @method Tag[] newEntities(array $data, array $options = [])
 * @method Tag|bool save(EntityInterface $entity, $options = [])
 * @method Tag patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method Tag[] patchEntities($entities, array $data, array $options = [])
 * @method Tag findOrCreate($search, callable $callback = null, $options = [])
 * @method Query findByName($name)
 *
 * @mixin TimestampBehavior
 * @mixin TreeBehavior
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
    public function initialize(array $config)
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
    public function validationDefault(Validator $validator)
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
    public function buildRules(RulesChecker $rules)
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
        if (strpos($idAndSlug, '-') === false) {
            return false;
        }

        $tagId = (explode('-', $idAndSlug))[0];

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
}
