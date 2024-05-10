<?php
/**
 * @var AppView $this
 */
use App\View\AppView;

$phrases = [
    'make MEmories',
    'coME as you are',
    'community engageMEnt',
    'city empowerMEnt',
    'MEet your neighbors',
    'the tiME is now',
    'welcoME hoME',
    'have unforgettable moMEnts',
    'a time to reMEmber',
    'seize the moMEnt',
];
$phrase = $phrases[array_rand($phrases)];
if (in_array(substr($phrase, -1), ['.', '?', '!'])) {
    $punct = substr($phrase, -1);
    $phrase = substr($phrase, 0, (mb_strlen($phrase) - 1));
    $phrase .= '<span class="punctuation">' . $punct . '</span>';
} else {
    $phrase .= '<span class="punctuation">.</span>';
}
$phrase = str_replace('ME', '<i class="icon-me-logo"></i>', $phrase);
echo $phrase;
