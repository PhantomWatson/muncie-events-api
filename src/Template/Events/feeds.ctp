<?php
/**
 * @var \Cake\ORM\ResultSet|\App\Model\Entity\Category[] $categories
 */

use Cake\Routing\Router;

?>

<p class="alert alert-info">
    Want to view Muncie Events in Google Calendar or other calendar applications? Just import any of these feeds into
    your calendar of choice:
</p>

<table class="table">
    <thead>
        <tr>
            <th>Category</th>
            <td>URL</td>
            <td>Copy</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <?php $url = Router::url([
                'controller' => 'Events',
                'action' => 'feed',
                'all',
                '_ext' => 'ics',
            ]); ?>
            <td>
                All events
            </td>
            <td>
                <?= $url ?>
            </td>
            <td>
                <i class="fa-solid fa-copy copy-feed-url" data-url="<?= $url ?>"></i>
            </td>
        </tr>
        <?php foreach ($categories as $category): ?>
            <tr>
                <?php $url = Router::url([
                    'controller' => 'Events',
                    'action' => 'feed',
                    $category->slug,
                    '_ext' => 'ics',
                ]); ?>
                <td>
                    <?= $this->Icon->category($category->name) ?>
                    <?= $category->name ?>
                </td>
                <td>
                    <?= $url ?>
                </td>
                <td>
                    <i class="fa-solid fa-copy copy-feed-url" data-url="<?= $url ?>"></i>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
    function copyToClipboard(url) {
        navigator.clipboard.writeText(url).then(() => {
            console.log('copied');
        });
    }
    document.querySelectorAll('.copy-feed-url').forEach((icon) => {
        icon.addEventListener('click', (event) => {
            cont url = event.target.dataset.url;
            copyToClipboard(url);
        });
    });
</script>
