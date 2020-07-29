<?php
namespace App\Widget;

use Cake\Http\Exception\InternalErrorException;

/**
 * Class WidgetStyles
 * @package App\Widgets
 * @property array $styles
 * @property string $type Either 'feed' or 'month'
 */
class WidgetStyles
{
    private $styles = [];
    private $type;

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
     * Getter for the styles property
     *
     * @return array
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * Adds style rules to the customStyles property
     *
     * @param array|string $elements One or more elements to apply rules to
     * @param array|string $rules One or more rules
     * @return void
     */
    public function addCustomStyle($elements, $rules)
    {
        if (!is_array($elements)) {
            $elements = [$elements];
        }
        if (!is_array($rules)) {
            $rules = [$rules];
        }
        foreach ($elements as $element) {
            foreach ($rules as $rule) {
                $this->styles[$element][] = $rule;
            }
        }
    }

    /**
     * Sets alternate background color value
     *
     * @param string $val Color value
     * @return void
     */
    public function backgroundColorAlt($val)
    {
        $this->addCustomStyle(
            '#widget_filters',
            "background-color: $val;"
        );
        if ($this->type == 'feed') {
            $this->addCustomStyle(
                '#event_list li',
                "background-color: $val;"
            );
        } elseif ($this->type == 'month') {
            $this->addCustomStyle(
                [
                    'table.calendar tbody li:nth-child(2n)',
                    '#event_lists a.event:nth-child(even)',
                    '#event_lists .close',
                ],
                "background-color: $val;"
            );
        }
    }

    /**
     * Sets font size
     *
     * @param string $val Font size
     * @return void
     */
    public function fontSize($val)
    {
        if ($this->type == 'month') {
            $this->addCustomStyle(
                [
                    'table.calendar tbody li',
                    'table.calendar .no_events',
                ],
                "font-size: $val;"
            );
        }
    }

    /**
     * Hides icons in month view if FALSE is passed to it
     *
     * @param bool $val FALSE to hide icons in month view
     * @return void
     */
    public function showIcons($val)
    {
        if ($val) {
            return;
        }
        if ($this->type == 'month') {
            $this->addCustomStyle(
                'table.calendar .icon:before',
                "display:none;"
            );
        }
    }

    /**
     * Hides the "general events" icon if TRUE is passed to it
     *
     * @param bool $val TRUE to hide the "general events" icon
     * @return void
     */
    public function hideGeneralEventsIcon($val)
    {
        if (!$val) {
            return;
        }
        if ($this->type == 'month') {
            $this->addCustomStyle(
                'table.calendar .icon-general-events:before',
                "display:none;"
            );
        }
    }
}
