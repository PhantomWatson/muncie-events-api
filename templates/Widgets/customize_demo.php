<?php
/**
 * @var string $iframeStyles
 * @var string $iframeUrl
 * @var string $codeUrl
 * @var \App\View\AppView $this
 */
?>

<iframe style="<?= h($iframeStyles) ?>" src="<?= $iframeUrl ?>"></iframe>
<p>
    To include this widget in your webpage, insert the following code where you would like it to appear:
    <code>
        &lt;iframe style="<?= h($iframeStyles) ?>" src="<?= $codeUrl ?>"&gt;&lt;/iframe&gt;
    </code>
</p>
