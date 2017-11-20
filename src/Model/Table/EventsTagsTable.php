<?php
namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EventsTags Model
 *
 * @property \App\Model\Table\EventsTable|\Cake\ORM\Association\BelongsTo $Events
 * @property \App\Model\Table\TagsTable|\Cake\ORM\Association\BelongsTo $Tags
 *
 * @method \App\Model\Entity\EventsTag get($primaryKey, $options = [])
 * @method \App\Model\Entity\EventsTag newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\EventsTag[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EventsTag|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\EventsTag patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventsTag[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\EventsTag findOrCreate($search, callable $callback = null, $options = [])
 */
class EventsTagsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('events_tags');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Events', [
            'foreignKey' => 'event_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Tags', [
            'foreignKey' => 'tag_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['event_id'], 'Events'));
        $rules->add($rules->existsIn(['tag_id'], 'Tags'));

        return $rules;
    }
}
