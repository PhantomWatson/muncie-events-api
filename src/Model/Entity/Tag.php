<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Utility\Text;

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
 * @property \Cake\I18n\DateTime $created
 * @property string $slug
 *
 * @property Tag $parent_tag
 * @property User $user
 * @property Tag[] $child_tags
 * @property Event[] $events
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
    protected array $_accessible = [
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
        'events' => true,
    ];

    /**
     * Returns a slug for this tag, formatted as "$tagId-$tagName"
     *
     * @return string
     */
    protected function _getSlug()
    {
        $slug = str_replace('\'', '', $this->name);
        $slug = mb_strtolower($slug);
        $slug = Text::slug($slug);

        return $this->id . '-' . $slug;
    }
}
