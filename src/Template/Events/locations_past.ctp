<?php
/**
 * @var string $pageTitle
 * @var array $locations
 */
?>
<h1 class="page_title">
    <?= sprintf(
        '%s %s of Past Events',
        $count,
        __n('Location', 'Locations', $count)
    ) ?>
</h1>

<?php if (empty($locations)): ?>
    <p class="alert alert-info">
        No locations found for past events.
    </p>
<?php else: ?>
    <ul>
        <?php foreach ($locations as $location): ?>
            <li>
                <?php echo $this->Html->link($location['location'], [
                    'controller' => 'Events',
                    'action' => 'location',
                    'location' => $location['location_slug'],
                    'direction' => 'past'
                ]); ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
