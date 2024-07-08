<?php
namespace App\Model\Table;

use App\Model\Entity\Image;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Association\BelongsToMany;
use Cake\ORM\Behavior\TimestampBehavior;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Images Model
 *
 * @property UsersTable|BelongsTo $Users
 * @property EventsTable|BelongsToMany $Events
 *
 * @method Image get($primaryKey, $options = [])
 * @method Image newEntity($data = null, array $options = [])
 * @method Image[] newEntities(array $data, array $options = [])
 * @method Image|bool save(EntityInterface $entity, $options = [])
 * @method Image patchEntity(EntityInterface $entity, array $data, array $options = [])
 * @method Image[] patchEntities($entities, array $data, array $options = [])
 * @method Image findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin TimestampBehavior
 */
class ImagesTable extends Table
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

        $this->setTable('images');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsToMany('Events', [
            'foreignKey' => 'image_id',
            'targetForeignKey' => 'event_id',
            'joinTable' => 'events_images',
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
            ->scalar('filename')
            ->allowEmptyString('filename', 'Image filename cannot be blank', false);

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
     * Deletes the associated files for this image after the image's database record is deleted
     *
     * @param Event $event CakePHP event object
     * @param Image $image Image entity
     * @param ArrayObject $options Options array
     * @return void
     */
    public function afterDelete(\Cake\Event\EventInterface $event, Image $image, ArrayObject $options)
    {
        if (!$image->filename) {
            return;
        }

        $filesToDelete = [
            $image->getFullPath('full'),
            $image->getFullPath('small'),
            $image->getFullPath('tiny'),
        ];
        foreach ($filesToDelete as $filePath) {
            if (!file_exists($filePath)) {
                continue;
            }

            unlink($filePath);
        }
    }
}
