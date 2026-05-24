<?php

return [
    'DebugKit' => [
        'onlyDebug' => true,
    ],
    'Bake' => [
        'onlyCli' => true,
        'optional' => true,
    ],
    'Migrations' => [
        'onlyCli' => true,
    ],
    'JsonApi' => ['path' => ROOT . DS . 'plugins' . DS . 'JsonApi' . DS],
    'Recaptcha' => [],
    'Search' => [],
    'Calendar' => [],
    'Authentication' => [],
    'IdeHelper' => [],
];
