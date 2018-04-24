<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Solr indexing monitor',
    'description' => 'Notifies the user about the solr indexing status in record\'s edit view',
    'category' => 'backend',
    'author' => 'Michael Straschek',
    'author_email' => 'm@straschek.io',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-8.7.99'
        ],
        'conflicts' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'StrIo\\SolrIndexingMonitor\\' => 'Classes"'
        ],
    ],
    'state' => 'alpha',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author_company' => '',
    'version' => '0.0.0',
];
