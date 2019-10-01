<?php
namespace App\Model\Table;

use App\Model\Entity\Category;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Association\HasMany;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Categories Model
 *
 * @property EventsTable|HasMany $Events
 *
 * @method Category get($primaryKey, $options = [])
 * @method Category newEntity($data = null, array $options = [])
 * @method Category[] newEntities(array $data, array $options = [])
 * @method Category|bool save(EntityInterface $entity, $options = [])
 * @method Category patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method Category[] patchEntities($entities, array $data, array $options = [])
 * @method Category findOrCreate($search, callable $callback = null, $options = [])
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
     * @param Validator $validator Validator instance.
     * @return Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id');

        $validator
            ->scalar('name')
            ->requirePresence('name', 'create')
            ->allowEmptyString('name', 'Category name cannot be blank', false);

        $validator
            ->scalar('slug')
            ->requirePresence('slug', 'create')
            ->allowEmptyString('slug', 'Category slug cannot be blank', false);

        $validator
            ->integer('weight')
            ->requirePresence('weight', 'create');

        return $validator;
    }
}
