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

<div class="alert alert-info">
    <p>
        Want to view Muncie Events in Google Calendar or other calendar applications? Just import any of these feeds into
        your calendar of choice.
    </p>
    <p>
        Google Calendar instructions:
    </p>
    <ol>
        <li>
            Click the + button next to "Other calendars" in the sidebar
        </li>
        <li>
            Copy a feed URL and paste it into the "URL of calendar" field
        </li>
        <li>
            Click "Add calendar"
        </li>
    </ol>
</div>

<?php foreach ($feeds as $name => $slug): ?>
    <section>
        <?php $url = Router::url([
            'controller' => 'Events',
            'action' => 'feed',
            $slug,
            '_ext' => 'ics',
        ], true); ?>
        <h2>
            <?= $this->Icon->category($slug == 'all' ? 'General Events' : $name) ?>
            <?= $name ?>
        </h2>
        <p>
            <button class="btn btn-secondary copy-feed-url" data-url="<?= $url ?>">
                <i class="fas fa-copy" title="Copy URL to clipboard"></i>
            </button>
            <?= $url ?>
        </p>
    </section>
<?php endforeach; ?>

<script>
    function copyToClipboard(url) {
        navigator.clipboard.writeText(url).then(() => {
            console.log('copied');
        });
    }
    const allButtons = document.querySelectorAll('.copy-feed-url');
    allButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const url = this.dataset.url;
            copyToClipboard(url);

            // Reset all other button styles
            allButtons.forEach((button) => {
                button.classList.remove('btn-success');
                if (!button.classList.hasClass('btn-secondary')) {
                    button.classList.add('btn-secondary');
                }
            });

            // Set this button's style to 'success'
            this.classList.remove('btn-secondary');
            this.classList.add('btn-success');
        });
    });
</script>
