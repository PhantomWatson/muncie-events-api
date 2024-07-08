<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Routing\Router;

/**
 * Category Entity
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $weight
 *
 * @property \App\Model\Entity\Event[] $events
 * @property string $url
 * @property \App\Model\Entity\MailingList[] $mailing_list
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
    protected array $_accessible = [
        'name' => true,
        'slug' => true,
        'weight' => true,
        'events' => true,
        'mailing_list' => true,
    ];

    /**
     * Returns the URL for the page that lists this category's upcoming events
     *
     * @return string
     */
    protected function _getUrl()
    {
        return Router::url([
            'plugin' => false,
            'prefix' => false,
            'controller' => 'Events',
            'action' => 'category',
            $this->slug,
        ]);
    }
}
