<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Categories Model
 *
 * @property \App\Model\Table\EventsTable|\Cake\ORM\Association\HasMany $Events
 *
 * @method \App\Model\Entity\Category get($primaryKey, $options = [])
 * @method \App\Model\Entity\Category newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Category[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Category|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Category patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Category[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Category findOrCreate($search, callable $callback = null, $options = [])
 */
class CategoriesTable extends Table
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

        $this->setTable('categories');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->hasMany('Events', [
            'foreignKey' => 'category_id'
        ]);
        $this->belongsToMany('MailingList', [
            'foreignKey' => 'category_id',
            'targetForeignKey' => 'mailing_list_id',
            'joinTable' => 'categories_mailing_list'
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

        $validator
            ->scalar('name')
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->scalar('slug')
            ->requirePresence('slug', 'create')
            ->notEmpty('slug');

        $validator
            ->integer('weight')
            ->requirePresence('weight', 'create')
            ->notEmpty('weight');

        return $validator;
    }
}
