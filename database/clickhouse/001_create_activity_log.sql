CREATE DATABASE IF NOT EXISTS smartbook;

CREATE TABLE IF NOT EXISTS smartbook.activity_log
(
    id UInt64,
    log_name String,
    description String,
    subject_type String,
    subject_id Nullable(UInt64),
    causer_type String,
    causer_id Nullable(UInt64),
    causer_name String,
    properties String,
    created_at DateTime DEFAULT now(),
    updated_at DateTime DEFAULT now()
    )
    ENGINE = MergeTree()
    PARTITION BY toYYYYMM(created_at)
    ORDER BY (created_at, id)
    SETTINGS index_granularity = 8192;
