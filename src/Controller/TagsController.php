<?php
namespace App\Controller;

use App\Model\Entity\User;
use App\Model\Table\CategoriesTable;
use App\Model\Table\EventsTable;
use App\Model\Table\TagsTable;
use Cake\Http\Exception\BadRequestException;
use Cake\Utility\Hash;
use Exception;

/**
 * Tags Controller
 *
 * @property CategoriesTable $Categories
 * @property EventsTable $Events
 * @property \App\Model\Table\TagsTable $Tags
 */
class TagsController extends AppController
{
    /**
     * Initialize hook method.
     *
     * @return void
     * @throws Exception
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Auth->allow(['index']);
    }

    /**
     * Returns TRUE if the user is authorized to make the current request
     *
     * @param User|null $user User entity
     * @return bool
     */
    public function isAuthorized($user = null)
    {
        return true;
    }

    /**
     * Tag index / cloud page
     *
     * @param string $direction Either 'upcoming' or 'past'
     * @param string|int $categoryId Either 'all' or a category ID
     * @return void
     * @throws BadRequestException
     */
    public function index($direction = 'upcoming', $categoryId = 'all')
    {
        // Filters
        if (!in_array($direction, ['upcoming', 'past'])) {
            $direction = 'upcoming';
        }

        if ($categoryId == 'all') {
            $categoryId = null;
        }
        if (is_numeric($categoryId)) {
            $categoryId = (int)$categoryId;
        }

        $tags = $this->Events->getEventTags($direction, $categoryId);

        // Create separate sub-lists of tags according to what character they start with
        $tagsByFirstLetter = [];
        foreach ($tags as $tag) {
            $firstLetter = ctype_alpha($tag->name[0]) ? $tag->name[0] : 'nonalpha';
            $tagsByFirstLetter[$firstLetter][$tag->name] = $tag;
        }

        // Generate the page title, specifying direction and (if applicable) category
        $pageTitle = sprintf('Tags (%s Events)', ucfirst($direction));
        /** @var CategoriesTable $categoriesTable */
        $categoriesTable = $this->fetchTable('Categories');
        $categoryName = $categoryId ? $categoriesTable->get($categoryId)->name : null;
        if ($categoryName) {
            $categoryName = str_replace(' Events', '', ucwords($categoryName));
            $pageTitle = str_replace(' Events', " $categoryName Events", $pageTitle);
        }

        // Create a function for determining each tag's individual font size in the cloud
        $maxCount = $tags ? max(Hash::extract($tags, '{s}.count')) : 0;
        $calculateFontSize = function ($tagCount) use ($maxCount) {
            $minFontSize = 75;
            $maxFontSize = 150;
            $fontSizeRange = $maxFontSize - $minFontSize;
            $fontSize = log($maxCount) == 0
                ? log($tagCount) / 1 * $fontSizeRange + $minFontSize
                : log($tagCount) / log($maxCount) * $fontSizeRange + $minFontSize;

            return round($fontSize, 1);
        };

        $this->set(compact(
            'calculateFontSize',
            'categoryId',
            'direction',
            'tags',
            'tagsByFirstLetter',
            'pageTitle'
        ));
        $this->set([
            'categories' => $categoriesTable->find('list')->all(),
            'letters' => array_merge(range('a', 'z'), ['nonalpha']),
        ]);
    }
}
