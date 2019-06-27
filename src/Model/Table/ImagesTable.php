<?php
namespace App\Model\Table;

use App\Model\Entity\Image;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Filesystem\File;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\InternalErrorException;
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
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('images');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->belongsToMany('Events', [
            'foreignKey' => 'image_id',
            'targetForeignKey' => 'event_id',
            'joinTable' => 'events_images'
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
            ->scalar('filename')
            ->allowEmptyString('filename', false);

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
     * Adds an image to the database and saves full and thumbnail versions under /webroot/img
     *
     * @param int $userId User ID
     * @param array $fileInfo Array of image file info (name, type, tmp_name, error, size)
     * @return Image
     * @throws BadRequestException
     * @throws InternalErrorException
     */
    public function processUpload(int $userId, $fileInfo)
    {
        // Create record in database
        $image = $this->newEntity(['user_id' => $userId]);
        if (!$this->save($image)) {
            throw new InternalErrorException('Error saving image to database');
        }

        // Set and save filename, which must happen after the initial save in order to use the image's ID
        $image->setExtension($fileInfo['name']);
        $image->setNewFilename();
        if (!$this->save($image)) {
            throw new InternalErrorException('Error updating image in database');
        }

        // Create the three resized versions of the uploaded image
        try {
            $image->setSourceFile($fileInfo['tmp_name']);
            $image->createFull();
            $image->createSmall();
            $image->createTiny();
        } catch (InternalErrorException $e) {
            $this->delete($image);
            throw new InternalErrorException($e->getMessage());
        } catch (BadRequestException $e) {
            $this->delete($image);
            throw new BadRequestException($e->getMessage());
        }

        return $image;
    }

    /**
     * Deletes the associated files for this image after the image's database record is deleted
     *
     * @param Event $event CakePHP event object
     * @param Image $image Image entity
     * @param ArrayObject $options Options array
     * @return void
     */
    public function afterDelete(Event $event, Image $image, ArrayObject $options)
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

            $file = new File($filePath);
            $file->delete();
            $file->close();
        }
    }
}
