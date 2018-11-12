<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Tag Entity
 *
 * @property int $id
 * @property int $parent_id
 * @property int $lft
 * @property int $rght
 * @property string $name
 * @property bool $listed
 * @property bool $selectable
 * @property int $user_id
 * @property \Cake\I18n\FrozenTime $created
 *
 * @property \App\Model\Entity\Tag $parent_tag
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Tag[] $child_tags
 * @property \App\Model\Entity\Event[] $events
 */
class Tag extends Entity
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
        'parent_id' => true,
        'lft' => true,
        'rght' => true,
        'name' => true,
        'listed' => true,
        'selectable' => true,
        'user_id' => true,
        'created' => true,
        'parent_tag' => true,
        'user' => true,
        'child_tags' => true,
        'events' => true
    ];
}
