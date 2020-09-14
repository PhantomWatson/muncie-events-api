<?php
/**
 * @var \App\Model\Entity\Event[]|\Cake\Datasource\ResultSetInterface $events
 * @var array $eventsForJson
 * @var int $eventsDisplayedPerDay
 * @var int $preSpacer The number of days in the week before the first day of the month
 * @var string $lastDay The last day of the month
 * @var string $month
 * @var string $monthName
 * @var string $today YYYYMMDD
 * @var string $year
 */

use App\View\Helper\CalendarHelper;

$eventsByDate = $events ? CalendarHelper::arrangeByDate($events->toArray()) : [];
?>

<table class="calendar" id="calendar_<?= "$year-$month" ?>" data-year="<?= $year ?>" data-month="<?= $month ?>">
    <thead>
        <tr>
            <td class="prev_month">
                <button class="prev_month btn btn-primary" title="Previous month">
                    <i class="fas fa-arrow-left"></i>
                </button>
            </td>
            <th colspan="5" class="month_name">
                <?= $monthName ?>
            </th>
            <td class="next_month">
                <button class="next_month btn btn-primary" title="Next month">
                    <i class="fas fa-arrow-right"></i>
                </button>
            </td>
        </tr>
        <tr>
            <?php foreach (['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $letter): ?>
                <th class="day_header">
                    <?= $letter ?>
                </th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php
        for ($cellNum = 0; $cellNum <= 42; $cellNum++) {
            // Beginning of row
            if ($cellNum % 7 == 0) {
                echo '<tr>';
            }

            // Pre-spacer
            if ($cellNum < $preSpacer) {
                echo '<td class="spacer">&nbsp;</td>';
            }

            // Calendar date
            if ($cellNum >= $preSpacer && $cellNum < $preSpacer + (int)$lastDay) {
                $day = $cellNum - $preSpacer + 1;
                echo ("$year$month$day" == $today) ? '<td class="today">' : '<td>';
                echo '<div>';

                echo $this->Html->link(
                    $day,
                    [
                        'controller' => 'Events',
                        'action' => 'day',
                        $month,
                        $day,
                        $year,
                    ],
                    [
                        'class' => 'date',
                        'data-day' => str_pad($day, 2, '0', STR_PAD_LEFT),
                    ]
                );

                $date = sprintf(
                    '%d-%s-%s',
                    $year,
                    str_pad($month, 2, '0', STR_PAD_LEFT),
                    str_pad($day, 2, '0', STR_PAD_LEFT)
                );
                if (isset($eventsByDate[$date]) && !empty($eventsByDate[$date])) {
                    echo '<ul>';
                    for ($n = 0; $eventsDisplayedPerDay == 0 || $n < $eventsDisplayedPerDay; $n++) {
                        if (!isset($eventsByDate[$date][$n])) {
                            break;
                        }
                        $event = $eventsByDate[$date][$n];
                        echo "<li>";

                        // Event link
                        $linkText = $this->Text->truncate(
                            $event->title,
                            50,
                            [
                                'ending' => '...',
                                'exact' => false,
                            ]
                        );
                        $linkText = $this->Icon->category($event->category->name) . $linkText;
                        echo $this->Html->link(
                            $linkText,
                            [
                                'controller' => 'Events',
                                'action' => 'view',
                                'id' => $event->id,
                            ],
                            [
                                'escape' => false,
                                'class' => 'event',
                                'data-event-id' => $event->id,
                                'title' => $event->time_start->format('g:ia') . ' - ' . $event->title,
                            ]
                        );

                        echo '</li>';
                    }
                    echo '</ul>';
                    $count = count($eventsByDate[$date]);
                    if ($eventsDisplayedPerDay > 0 && $count > $eventsDisplayedPerDay) {
                        echo $this->Html->link(
                            $count - $eventsDisplayedPerDay . ' more',
                            [
                                'controller' => 'Events',
                                'action' => 'day',
                                $month,
                                $day,
                                $year,
                            ],
                            [
                                'class' => 'more',
                                'data-day' => str_pad($day, 2, '0', STR_PAD_LEFT),
                                'title' => 'View all events on this date',
                            ]
                        );
                    }
                }
                echo '</div></td>';
            }

            // After the last day
            if ($cellNum >= $preSpacer + (int)$lastDay - 1) {
                // End of calendar
                if ($cellNum % 7 == 6) {
                    echo '</tr>';
                    break;

                // Normal spacer
                } else {
                    echo '<td class="spacer">&nbsp;</td>';
                }
            }

            // End of row
            if ($cellNum % 7 == 6) {
                echo '</tr>';
            }
        }
        ?>
    </tbody>
</table>

<?php $this->Html->scriptBlock(
    'muncieEventsMonthWidget.setCurrentMonth(' . json_encode($month) . ');' .
    'muncieEventsMonthWidget.setCurrentYear(' . json_encode($year) . ');' .
    "muncieEventsMonthWidget.prepareLinks('#calendar_$year-$month');" .
    'var events = ' . json_encode($eventsForJson) . ';' .
    'muncieEventsMonthWidget.setEvents(events);',
    ['block' => true]
); ?>
