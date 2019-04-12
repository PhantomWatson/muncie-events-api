<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Category Entity
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $weight
 *
 * @property Event[] $events
 */
class Category extends Entity
{
    /**
     * A flag used to suppress a lookup in CategorySchema of the number of upcoming events in this category
     * @var bool
     */
    public $noEventCount = false;

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
        'name' => true,
        'slug' => true,
        'weight' => true,
        'events' => true,
        'mailing_list' => true
    ];
}
