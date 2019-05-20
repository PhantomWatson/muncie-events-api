<?php
/**
 * @var AppView $this
 */

use App\View\AppView;
use Cake\Core\Configure;
$googleAnalyticsId = Configure::read('googleAnalyticsId');
$debug = Configure::read('debug');
$gaConfig = [
    'page_location' => $this->request->getUri()->__toString(),
    'page_path' => $this->request->getUri()->getPath()
];
if (isset($titleForLayout) && $titleForLayout) {
    $gaConfig['page_title'] = $titleForLayout;
}
?>
<?php if ($googleAnalyticsId && !$debug): ?>
    <!-- Global Site Tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $googleAnalyticsId ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= $googleAnalyticsId ?>', <?= json_encode($gaConfig) ?>);
        gtag('event', 'page_view');
    </script>
<?php endif; ?>
