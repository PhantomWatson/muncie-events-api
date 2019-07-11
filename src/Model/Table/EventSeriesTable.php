<?php
namespace App\Model\Table;

use App\Model\Entity\EventSeries;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * EventSeries Model
 *
 * @property UsersTable|BelongsTo $Users
 *
 * @method EventSeries get($primaryKey, $options = [])
 * @method EventSeries newEntity($data = null, array $options = [])
 * @method EventSeries[] newEntities(array $data, array $options = [])
 * @method EventSeries|bool save(EntityInterface $entity, $options = [])
 * @method EventSeries patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method EventSeries[] patchEntities($entities, array $data, array $options = [])
 * @method EventSeries findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin TimestampBehavior
 */
class EventSeriesTable extends Table
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

        $this->setTable('event_series');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Events')
            ->setForeignKey('series_id')
            ->setDependent(true);
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
            ->scalar('title')
            ->requirePresence('title', 'create')
            ->allowEmptyString('title', 'Title required', false);

        $validator
            ->boolean('published')
            ->requirePresence('published', 'create');

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
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }

    /**
     * Returns an event series and all of its associated events for the /event-series/edit page
     *
     * @param Query $query Query object
     * @return array|EntityInterface|null
     */
    public function findForEdit(Query $query)
    {
        return $query
            ->contain([
                'Events' => function (Query $q) {
                    return $q->find('ordered');
                }
            ]);
    }

    /**
     * Alters a query to include ordered, published events with associated data
     *
     * @param Query $query Query object
     * @return array|Query
     */
    public function findForView(Query $query)
    {
        return $query
            ->contain([
                'Events' => function (Query $q) {
                    return $q
                        ->find('ordered')
                        ->find('published')
                        ->find('withAllAssociated')
                        ->select([
                            'date',
                            'id',
                            'series_id',
                            'time_end',
                            'time_start',
                            'title'
                        ]);
                },
                'Users' => function (Query $q) {
                    return $q->select(['id', 'name']);
                }
            ]);
    }
}
