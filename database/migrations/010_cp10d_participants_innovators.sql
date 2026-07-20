USE hubinovasi;

-- CP10D extends the existing submission_people table. No parallel people
-- structure is created and all existing submission records remain intact.

ALTER TABLE submission_people
    ADD COLUMN IF NOT EXISTS class_name VARCHAR(120) NULL AFTER programme,
    ADD COLUMN IF NOT EXISTS year_of_study VARCHAR(80) NULL AFTER class_name,
    ADD COLUMN IF NOT EXISTS is_team_leader TINYINT(1) NOT NULL DEFAULT 0 AFTER year_of_study;

SET @cp10d_leader_index_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'submission_people'
      AND INDEX_NAME = 'idx_submission_people_leader_order'
);
SET @cp10d_leader_index_sql = IF(
    @cp10d_leader_index_exists = 0,
    'ALTER TABLE submission_people ADD INDEX idx_submission_people_leader_order (submission_id, is_team_leader, sort_order)',
    'DO 0'
);
PREPARE cp10d_leader_index_stmt FROM @cp10d_leader_index_sql;
EXECUTE cp10d_leader_index_stmt;
DEALLOCATE PREPARE cp10d_leader_index_stmt;
