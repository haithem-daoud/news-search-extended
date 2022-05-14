<?php

/**
 * Extension Manager/Repository config file for ext "news_search".
 */
$EM_CONF[$_EXTKEY] = [
    'title' => 'News Search',
    'description' => '',
    'category' => 'plugin',
    'constraints' => [
        'depends' => [
            'typo3' => '10.2.0-11.5.99',
            'news' => '8.0.0-10.9.99',
            'numbered_pagination' => '1.0.0-1.0.99',
        ],
        'conflicts' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'T3hd\\NewsSearch\\' => 'Classes',
        ],
    ],
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Haithem Daoud',
    'author_email' => 'contact@haithemdaoud.com',
    'author_company' => 'Freelance',
    'version' => '1.0.0',
];
