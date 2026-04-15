<?php

declare(strict_types=1);

return [
    'host' => env('ELASTICSEARCH_HOST', 'http://elasticsearch:9200'),

    'books_index' => 'books',

    'books_mapping' => [
        'mappings' => [
            'properties' => [
                'id' => ['type' => 'integer'],
                'title' => [
                    'type' => 'text',
                    'fields' => [
                        'keyword' => ['type' => 'keyword'],
                    ],
                ],
                'slug' => ['type' => 'keyword'],
                'description' => ['type' => 'text'],
                'author_id' => ['type' => 'integer'],
                'price' => ['type' => 'float'],
                'stock' => ['type' => 'integer'],
                'publish_year' => ['type' => 'integer'],
                'cover_image' => ['type' => 'keyword', 'index' => false],
                'average_rating' => ['type' => 'float'],
                'ratings_count' => ['type' => 'integer'],
                'status' => ['type' => 'keyword'],
                'created_at' => ['type' => 'date'],
                'updated_at' => ['type' => 'date'],
            ],
        ],
    ],
];
