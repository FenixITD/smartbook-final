<?php

return [
    'default' => env('ELASTIC_CONNECTION', 'default'),
    'connections' => [
        'default' => [
            'hosts' => [
                env('ELASTICSEARCH_HOST', 'http://localhost:9200'),
            ],
        ],
    ],
];
