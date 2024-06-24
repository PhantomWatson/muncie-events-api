<?php
namespace App\Model\Entity;

use Cake\Core\Configure;
use Cake\Http\Exception\InternalErrorException;
use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;

/**
 * Image Entity
 *
 * @property bool $is_flyer
 * @property EventsImage $_joinData
 * @property FrozenTime $created
 * @property FrozenTime $modified
 * @property int $id
 * @property int $user_id
 * @property string $caption
 * @property string $filename
 *
 * @property User $user
 * @property Event[] $events
 */
class Image extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'filename' => true,
        'is_flyer' => true,
        'user_id' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'events' => true,
    ];

    /**
     * Returns the full system path to the full, small, or tiny file
     *
     * @param string $size Either full, small, or tiny
     * @return string
     */
    public function getFullPath(string $size = 'full'): string
    {
        if (!$this->filename) {
            throw new InternalErrorException('Cannot get full path because this image\'s filename is not set');
        }

        return Configure::read('eventImagePath') . DS . $size . DS . $this->filename;
    }

    /**
     * A virtual field that returns this image's caption from its join data
     *
     * @return string
     */
    protected function _getCaption()
    {
        return $this->_joinData['caption'] ?? '';
    }
}
