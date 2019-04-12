<?php
namespace App\Model\Table;

use App\Model\Entity\EventsImage;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EventsImages Model
 *
 * @property ImagesTable|BelongsTo $Images
 * @property EventsTable|BelongsTo $Events
 *
 * @method EventsImage get($primaryKey, $options = [])
 * @method EventsImage newEntity($data = null, array $options = [])
 * @method EventsImage[] newEntities(array $data, array $options = [])
 * @method EventsImage|bool save(EntityInterface $entity, $options = [])
 * @method EventsImage patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method EventsImage[] patchEntities($entities, array $data, array $options = [])
 * @method EventsImage findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin TimestampBehavior
 */
class EventsImagesTable extends Table
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

        $this->setTable('events_images');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Images', [
            'foreignKey' => 'image_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsTo('Events', [
            'foreignKey' => 'event_id',
            'joinType' => 'INNER'
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
            ->integer('weight')
            ->requirePresence('weight', 'create');

        $validator
            ->scalar('caption')
            ->allowEmptyString('caption');

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
        $rules->add($rules->existsIn(['image_id'], 'Images'));
        $rules->add($rules->existsIn(['event_id'], 'Events'));

        return $rules;
    }
}
