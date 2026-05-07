<?php

return [
    'host' => env('ELASTICSEARCH_HOST', 'http://localhost:9200'),
    'books_index' => env('ELASTICSEARCH_BOOKS_INDEX', 'books'),
];
