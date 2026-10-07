<?php

return [
    /*
    | Public-page search and social metadata. Add or update page entries here;
    | keys are route names. Leave description empty only when the page has no
    | useful search snippet.
    */
    'defaults' => [
        'site_name' => env('APP_NAME', 'CMS Template'),
        'title' => 'CMS Template',
        'description' => 'Explore the CMS Template website.',
        'image' => 'images/cms-logo.png',
        'robots' => 'index, follow',
    ],

    'pages' => [
        'home' => [
            'title' => 'Home',
            'description' => 'Welcome to the CMS Template. Explore our latest updates and get in touch with our team.',
        ],
        'about' => [
            'title' => 'About',
            'description' => 'Learn more about the CMS Template and what it offers.',
        ],
    ],
];
