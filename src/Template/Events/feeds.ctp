<?php
/**
 * @var \Cake\ORM\ResultSet|\App\Model\Entity\Category[] $categories
 */

use Cake\Routing\Router;

$feeds = ['All events' => 'all'];
foreach ($categories as $category) {
    $feeds[$category->name] = $category->slug;
}
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
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $name => $slug): ?>
            <tr>
                <?php $url = Router::url([
                    'controller' => 'Events',
                    'action' => 'feed',
                    $category->slug,
                    '_ext' => 'ics',
                ], true); ?>
                <td>
                    <?= $this->Icon->category($category->name) ?>
                    <?= $category->name ?>
                </td>
                <td>
                    <input type="text" value="<?= $url ?>" />
                    <button class="copy-feed-url" data-url="<?= $url ?>">
                        <i class="fas fa-copy" title="Copy URL to clipboard"></i>
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
undefined
<script>
    function copyToClipboard(url) {
        navigator.clipboard.writeText(url).then(() => {
            console.log('copied');
        });
    }
    document.querySelectorAll('.copy-feed-url').forEach((icon) => {
        icon.addEventListener('click', () => {
            const url = this.dataset.url;
            copyToClipboard(url);
        });
    });
</script>
