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
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\EventSeries get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\EventSeries newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\EventSeries[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\EventSeries|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\EventSeries patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\EventSeries[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\EventSeries findOrCreate($search, ?callable $callback = null, array $options = [])
 *
 * @mixin TimestampBehavior
 * @property \App\Model\Table\EventsTable&\Cake\ORM\Association\HasMany $Events
 * @method \App\Model\Entity\EventSeries newEmptyEntity()
 * @method \App\Model\Entity\EventSeries saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\EventSeries[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\EventSeries[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\EventSeries[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, array $options = [])
 * @method \App\Model\Entity\EventSeries[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class EventSeriesTable extends Table
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

        $this->setTable('event_series');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
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
    public function validationDefault(Validator $validator): \Cake\Validation\Validator
    {
        $validator
            ->integer('id');

        $validator
            ->scalar('title')
            ->requirePresence('title', 'create')
            ->allowEmptyString('title', 'Event series title cannot be blank', false);

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
    public function buildRules(RulesChecker $rules): \Cake\ORM\RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }

    /**
     * Returns an event series and all of its associated events for the /event-series/edit page
     *
     * @param Query $query Query object
     * @return Query
     */
    public function findForEdit(Query $query)
    {
        return $query
            ->contain([
                'Events' => function (Query $q) {
                    return $q->find('ordered');
                },
            ]);
    }

    /**
     * Alters a query to include ordered, published events with associated data
     *
     * @param Query $query Query object
     * @return Query
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
                            'title',
                        ]);
                },
                'Users' => function (Query $q) {
                    return $q->select(['id', 'name']);
                },
            ]);
    }
}
