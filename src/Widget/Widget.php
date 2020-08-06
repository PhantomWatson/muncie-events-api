<?php
namespace App\Widget;

use Cake\Http\Exception\InternalErrorException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * Class Widgets
 *
 * @package App\Widgets
 * @property string $type Either 'feed' or 'month'
 * @property \App\Widget\WidgetStyles $WidgetStyles;
 */
class Widget
{
    private $type;
    private $WidgetStyles;

    /**
     * Widgets constructor
     */
    public function __construct()
    {
        $this->WidgetStyles = new WidgetStyles();
    }

    /**
     * Takes an array of options and returns only event filters after validating and correcting formatting errors
     *
     * @param array $options Options passed to a Widgets via query string
     * @return array
     */
    public function getEventFilters($options)
    {
        // Correct formatting of $options
        $correctedOptions = [];
        foreach ($options as $var => $val) {
            if (is_string($val)) {
                $val = trim($val);
                $var = str_replace('amp;', '', $var);
            }

            // Turn specified options into arrays if they're comma-delimited strings
            $expectedArrays = ['category', 'tags_included', 'tags_excluded'];
            if (in_array($var, $expectedArrays) && !is_array($val)) {
                $val = explode(',', $val);
                $correctedArray = [];
                foreach ($val as $member) {
                    $member = trim($member);
                    if ($member != '') {
                        $correctedArray[] = $member;
                    }
                }
                $val = $correctedArray;
            }

            /* Only include if not empty
             * Note: A value of 0 is a valid Widgets parameter elsewhere (e.g. the boolean 'outerBorder'),
             * but not valid for any event filters. */
            if (!empty($val)) {
                $correctedOptions[$var] = $val;
            }
        }
        $options = $correctedOptions;

        // Pull event filters out of options
        $filters = [];
        $filterTypes = ['category', 'location', 'tags_included', 'tags_excluded'];
        foreach ($filterTypes as $type) {
            if (isset($options[$type])) {
                $filters[$type] = $options[$type];
            }
        }

        // Remove categories filter if it specifies all categories
        if (isset($filters['category'])) {
            sort($filters['category']);
            $categoriesTable = TableRegistry::getTableLocator()->get('Categories');
            $categories = $categoriesTable->find()->select(['id'])->orderAsc('id')->toArray();
            $allCategoryIds = Hash::extract($categories, '{n}.id');
            $excludedCategories = array_diff($allCategoryIds, $filters['category']);
            if (empty($excludedCategories)) {
                unset($filters['category']);
            }
        }

        // If a tag is both excluded and included, favor excluding
        if (isset($filters['tags_included']) && isset($filters['tags_excluded'])) {
            foreach ($filters['tags_included'] as $k => $id) {
                if (in_array($id, $filters['tags_excluded'])) {
                    unset($filters['tags_included'][$k]);
                }
            }
            if (empty($filters['tags_included'])) {
                unset($filters['tags_included']);
            }
        }

        return $filters;
    }

    /**
     * Takes $filters provided by a Widgets page, converts valid tag names into tag IDs, removes invalid tag names,
     * and returns the formatted $filters.
     *
     * @param array $filters Options passed to a Widgets via query string
     * @return array
     */
    public function processTagFilters($filters)
    {
        /** @var \App\Model\Table\TagsTable $tagsTable */
        $tagsTable = TableRegistry::getTableLocator()->get('Tags');
        foreach (['included', 'excluded'] as $foocluded) {
            if (!isset($filters["tags_$foocluded"])) {
                continue;
            }
            foreach ($filters["tags_$foocluded"] as $k => $tagName) {
                $tag = $tagsTable->findByName($tagName)->first();
                if ($tag) {
                    $filters["tags_$foocluded"][$k] = $tag->id;
                } else {
                    unset($filters["tags_$foocluded"][$k]);
                }
            }
            $filters["tags_$foocluded"] = array_values($filters["tags_$foocluded"]);
        }

        return $filters;
    }

    /**
     * Sets the 'type' property
     *
     * @param string $widgetType Either 'feed' or 'month'
     * @return void
     * @throws \Cake\Http\Exception\InternalErrorException
     */
    public function setType($widgetType)
    {
        if (in_array($widgetType, ['feed', 'month'])) {
            $this->type = $widgetType;

            return;
        }

        throw new InternalErrorException('Invalid widget type: ' . $widgetType);
    }

    /**
     * Takes an array of query string parameters for customizing a widget iframe and returns a formatted query param
     * array to be used to build the query string for the iframe's src URL
     *
     * @param array $queryParameters Query parameters from the current request
     * @return array
     */
    public function getIframeQueryParams($queryParameters)
    {
        if (empty($queryParameters)) {
            return [];
        }
        $defaults = $this->getDefaults();
        $iframeParams = [];
        foreach ($queryParameters as $key => $val) {
            // Clean up option and skip blanks
            $val = trim($val);
            if ($val == '') {
                continue;
            }

            // Retain only valid params that differ from their default values
            if ($this->isValidNondefaultOption($key, $val)) {
                // Iframe options (applying to the iframe element, but not
                // its contents) aren't included in the query string
                if (!isset($defaults['iframe_options'][$key])) {
                    $iframeParams[$key] = $val;
                }
            }
        }

        return $iframeParams;
    }

    /**
     * Returns the default parameters for a widget
     *
     * @return array
     * @throws \Cake\Http\Exception\InternalErrorException
     */
    public function getDefaults()
    {
        if (!$this->type) {
            throw new InternalErrorException('Widgets type is null');
        }

        $defaults = [
            'styles' => [
                'textColorDefault' => '#000000',
                'textColorLight' => '#666666',
                'textColorLink' => '#0b54a6',
                'borderColorLight' => '#aaaaaa',
                'borderColorDark' => '#000000',
                'backgroundColorDefault' => '#ffffff',
                'backgroundColorAlt' => '#f0f0f0',
                'showIcons' => 1,
                'hideGeneralEventsIcon' => 0,
            ],
            'iframe_options' => [
                'outerBorder' => 1,
            ],
            'event_options' => [
                'category' => '',
                'location' => '',
                'tags_included' => '',
                'tags_excluded' => '',
            ],
        ];
        switch ($this->type) {
            case 'feed':
                $defaults['iframe_options']['height'] = 300;
                $defaults['iframe_options']['width'] = 100;

                return $defaults;
            case 'month':
                $defaults['styles']['fontSize'] = '11px';
                $defaults['styles']['showIcons'] = true;
                $defaults['iframe_options']['height'] = 400;
                $defaults['iframe_options']['width'] = 100;
                $defaults['event_options']['events_displayed_per_day'] = 2;

                return $defaults;
        }

        throw new InternalErrorException('Invalid widget type');
    }

    /**
     * Returns TRUE $val is not the default value for $key
     *
     * @param string $key Parameter key
     * @param string $val Parameter value
     * @return bool
     */
    public function isValidNondefaultOption($key, $val)
    {
        $defaults = $this->getDefaults();

        if (isset($defaults['styles'][$key])) {
            return $defaults['styles'][$key] != $val;
        } elseif (isset($defaults['event_options'][$key])) {
            return $defaults['event_options'][$key] != $val;
        } elseif (isset($defaults['iframe_options'][$key])) {
            return $defaults['iframe_options'][$key] != $val;
        }

        return false;
    }

    /**
     * Gets the CSS rules for the widget iframe
     *
     * @param array $options Widgets options
     * @return string
     */
    public function getIframeStyles($options)
    {
        $iframeStyles = [];
        $defaults = $this->getDefaults();

        // Dimensions
        foreach (['height', 'width'] as $dimension) {
            if (isset($options[$dimension])) {
                $unit = substr($options[$dimension], -1) == '%' ? '%' : 'px';
                $value = preg_replace("/[^0-9]/", "", $options[$dimension]);
            } else {
                $unit = $dimension == 'height' ? 'px' : '%';
                $value = $defaults['iframe_options'][$dimension];
            }
            $iframeStyles[] = "$dimension: {$value}$unit";
        }

        // Border
        if (isset($options['outerBorder']) && $options['outerBorder'] == 0) {
            $iframeStyles[] = "border: 0";
        } else {
            if (isset($options['borderColorDark'])) {
                $outerBorderColor = $options['borderColorDark'];
            } else {
                $outerBorderColor = $defaults['styles']['borderColorDark'];
            }
            $iframeStyles[] = "border: 1px solid $outerBorderColor";
        }

        return implode('; ', $iframeStyles);
    }

    /**
     *
     *
     * @param array $options Widget options
     * @return void
     */
    public function processCustomStyles($options)
    {
        if (empty($options)) {
            return;
        }

        if (!in_array($this->type, ['feed', 'month'])) {
            throw new InternalErrorException('Missing or invalid widget type');
        }

        $this->WidgetStyles->setType($this->type);

        $defaults = $this->getDefaults();
        foreach ($options as $var => $val) {
            if (stripos($var, 'amp;') !== false) {
                $var = str_replace('amp;', '', $var);
            }
            $val = trim($val);
            $var = trim($var);

            // Skip blank values, default values, and unrecognized 'styles' options
            if ($val == '') {
                continue;
            } elseif (isset($defaults['styles'][$var])) {
                if ($defaults['styles'][$var] == $val) {
                    continue;
                }
            } else {
                continue;
            }

            if (method_exists($this->WidgetStyles, $var)) {
                $this->WidgetStyles->$var($val);
            }
        }
    }

    /**
     * Getter for the WidgetStyles styles array
     *
     * @return array
     */
    public function getStyles()
    {
        return $this->WidgetStyles->getStyles();
    }
}
