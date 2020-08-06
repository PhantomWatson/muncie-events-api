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
            [
                '#widget_filters',
                '.btn-primary',
                '.btn-primary:hover',
                '.btn-primary:not(:disabled):not(.disabled):active',
                '.btn-secondary',
                '.btn-secondary:hover',
                '.btn-secondary:not(:disabled):not(.disabled):active',
                '.show > .btn-secondary.dropdown-toggle',
            ],
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
     * Sets primary background color value
     *
     * @param string $val Color value
     * @return void
     */
    public function backgroundColorDefault($val)
    {
        $this->addCustomStyle(
            [
                'html, body',
                '#loading div:nth-child(1)',
            ],
            "background-color: $val;"
        );
        if ($this->type == 'month') {
            $this->addCustomStyle(
                '#event_lists > div > div',
                "background-color: $val;"
            );
        }
    }

    /**
     * Sets the dark border color
     *
     * @param string $val Color value
     * @return void
     */
    public function borderColorDark($val)
    {
        $this->addCustomStyle(
            [
                '.btn-primary:hover',
                '.btn-primary:not(:disabled):not(.disabled):active',
                '.btn-secondary:hover',
                '.btn-secondary:not(:disabled):not(.disabled):active',
            ],
            "border: 1px solid $val;"
        );
        if ($this->type == 'feed') {
            $this->addCustomStyle(
                '#event_list li:hover',
                "border-color: $val;"
            );
        } elseif ($this->type == 'month') {
            $this->addCustomStyle(
                [
                    'table.calendar td',
                    'table.calendar thead'
                ],
                "border-color: $val;"
            );
        }
    }

    /**
     * Sets the light border color
     *
     * @param string $val Color value
     * @return void
     */
    public function borderColorLight($val)
    {
        $this->addCustomStyle(
            'a.back:first-child',
            "border-bottom: 1px solid $val;"
        );
        $this->addCustomStyle(
            'a.back:last-child',
            "border-top: 1px solid $val;"
        );
        $this->addCustomStyle(
            '.event .description',
            "border-top: 1px solid $val;"
        );
        $this->addCustomStyle(
            [
                '#widget_filters',
                '.btn-primary',
                '.btn-secondary',
            ],
            "border: 1px solid $val;"
        );
        if ($this->type == 'feed') {
            $this->addCustomStyle(
                '#event_list li',
                "border-bottom-color: $val;"
            );
            $this->addCustomStyle(
                '#event_list li:first-child',
                "border-color: $val;"
            );
        } elseif ($this->type == 'month') {
            $this->addCustomStyle(
                '#event_lists .close',
                "border-color: $val;"
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
     * Sets the default text color
     *
     * @param string $val Color value
     * @return void
     */
    public function textColorDefault($val)
    {
        $this->addCustomStyle(
            [
                'body',
                '.btn-primary',
                '.btn-primary:hover',
                '.btn-primary:not(:disabled):not(.disabled):active',
                '.btn-secondary',
                '.btn-secondary:hover',
                '.btn-secondary:not(:disabled):not(.disabled):active',
                '.show > .btn-secondary.dropdown-toggle',
            ],
            "color: $val;"
        );
        if ($this->type == 'feed') {
            $this->addCustomStyle(
                '#event_list li a.event_link',
                "color: $val;"
            );
        } elseif ($this->type == 'month') {
            $this->addCustomStyle(
                ['table.calendar thead', '#event_lists .time'],
                "color: $val;"
            );
        }
    }

    /**
     * Sets the light text color
     *
     * @param string $val Color value
     * @return void
     */
    public function textColorLight($val)
    {
        $this->addCustomStyle(
            [
                'div.header',
                'div.header a',
                '.event table.details th',
                '.event .footer',
                '#widget_filters',
                '#event_list li .icon:before',
            ],
            "color: $val;"
        );
        $this->addCustomStyle(
            'ul.header li',
            "border-right: 1px solid $val;"
        );
        if ($this->type == 'feed') {
            $this->addCustomStyle(
                [
                    '#event_list h2.day',
                    '#event_list p.no_events',
                    '#load_more_events_wrapper.loading a',
                ],
                "color: $val;"
            );
        }
    }

    /**
     * Sets the link text color
     *
     * @param string $val Color value
     * @return void
     */
    public function textColorLink($val)
    {
        $this->addCustomStyle(
            'a',
            "color: $val;"
        );
        $this->addCustomStyle(
            [
                '.btn-primary:focus',
                '.btn-primary:not(:disabled):not(.disabled):active:focus',
                '.btn-secondary:focus',
                '.btn-secondary:not(:disabled):not(.disabled):active:focus',
            ],
            "box-shadow: 0 0 0 0.2rem $val;"
        );
        if ($this->type == 'feed') {
            $this->addCustomStyle(
                '#event_list li a.event_link .title',
                "color: $val;"
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
