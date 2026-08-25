-- Migrate activity_log from MergeTree to ReplacingMergeTree for row-level dedup.
-- Apply manually: clickhouse-client --multiquery < 002_activity_log_replacing.sql

CREATE TABLE IF NOT EXISTS smartbook.activity_log_new
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
ENGINE = ReplacingMergeTree(updated_at)
PARTITION BY toYYYYMM(created_at)
ORDER BY (id)
SETTINGS index_granularity = 8192;

INSERT INTO smartbook.activity_log_new SELECT * FROM smartbook.activity_log;

RENAME TABLE smartbook.activity_log TO smartbook.activity_log_old;
RENAME TABLE smartbook.activity_log_new TO smartbook.activity_log;
DROP TABLE smartbook.activity_log_old;
