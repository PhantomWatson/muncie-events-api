<?php
/**
 * @var AppView $this
 * @var array $ogMetaTags
 */
use App\View\AppView;
use Cake\Utility\Text;

$defaultOgMetaTags = [
    'og:title' => 'Muncie Events',
    'og:type' => 'website',
    'og:image' => $this->Url->image('/img/logo/facebook_logo.png'),
    'og:url' => $this->request->getUri(),
    'og:site_name' => 'Muncie Events',
    'fb:app_id' => '496726620385625',
    'og:description' => 'Upcoming events in Muncie, IN',
    'og:locale' => 'en_US',
];

// Add tags set in viewVars
if (isset($ogMetaTags)) {
    foreach ($ogMetaTags as $property => $contents) {
        $contents = is_array($contents) ? $contents : [$contents];
        foreach ($contents as $content) {
            if ($property == 'og:description') {
                $content = Text::truncate(
                    strip_tags($content),
                    1000,
                    ['exact' => false]
                );
            }
            echo sprintf('<meta property="%s" content="%s" />', $property, htmlentities($content));
        }
    }
}

// Add default tags
foreach ($defaultOgMetaTags as $property => $defaultContents) {
    // Skip any tags that have already been added
    if (isset($ogMetaTags[$property])) {
        continue;
    }

    $defaultContents = is_array($defaultContents) ? $defaultContents : [$defaultContents];
    foreach ($defaultContents as $content) {
        echo sprintf('<meta property="%s" content="%s" />', $property, htmlentities($content));
    }
}
