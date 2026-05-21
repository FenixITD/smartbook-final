<?php

return [
    'host' => env('ELASTICSEARCH_HOST', 'http://localhost:9200'),

    'books_index'   => env('ELASTICSEARCH_BOOKS_INDEX', 'books'),
    'authors_index' => env('ELASTICSEARCH_AUTHORS_INDEX', 'authors'),
    'genres_index'  => env('ELASTICSEARCH_GENRES_INDEX', 'genres'),
    'orders_index'  => env('ELASTICSEARCH_ORDERS_INDEX', 'orders'),
    'reviews_index' => env('ELASTICSEARCH_REVIEWS_INDEX', 'reviews'),
];
