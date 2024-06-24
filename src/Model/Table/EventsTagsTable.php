<?php
namespace App\Model\Table;

use App\Model\Entity\EventsTag;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EventsTags Model
 *
 * @property EventsTable|BelongsTo $Events
 * @property TagsTable|BelongsTo $Tags
 *
 * @method EventsTag get($primaryKey, $options = [])
 * @method EventsTag newEntity($data = null, array $options = [])
 * @method EventsTag[] newEntities(array $data, array $options = [])
 * @method EventsTag|bool save(EntityInterface $entity, $options = [])
 * @method EventsTag patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method EventsTag[] patchEntities($entities, array $data, array $options = [])
 * @method EventsTag findOrCreate($search, callable $callback = null, $options = [])
 */
class EventsTagsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('events_tags');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Events', [
            'foreignKey' => 'event_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Tags', [
            'foreignKey' => 'tag_id',
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
        $rules->add($rules->existsIn(['event_id'], 'Events'));
        $rules->add($rules->existsIn(['tag_id'], 'Tags'));

        return $rules;
    }
}
