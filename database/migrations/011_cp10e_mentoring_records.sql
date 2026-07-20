USE hubinovasi;

-- The composite key allows the database to enforce that a mentoring record
-- can only reference a mentor belonging to the same submission.
SET @cp10e_mentor_owner_index_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'submission_mentors'
      AND INDEX_NAME = 'uq_submission_mentors_owner'
);
SET @cp10e_mentor_owner_index_sql = IF(
    @cp10e_mentor_owner_index_exists = 0,
    'ALTER TABLE submission_mentors ADD UNIQUE INDEX uq_submission_mentors_owner (id, submission_id)',
    'DO 0'
);
PREPARE cp10e_mentor_owner_index_stmt FROM @cp10e_mentor_owner_index_sql;
EXECUTE cp10e_mentor_owner_index_stmt;
DEALLOCATE PREPARE cp10e_mentor_owner_index_stmt;

CREATE TABLE IF NOT EXISTS submission_mentoring_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id BIGINT UNSIGNED NOT NULL,
    mentor_id BIGINT UNSIGNED NULL,
    guidance_type VARCHAR(40) NULL,
    guidance_focus TEXT NULL,
    guidance_outcome TEXT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_mentoring_record_submission FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    CONSTRAINT fk_mentoring_record_owned_mentor FOREIGN KEY (mentor_id, submission_id) REFERENCES submission_mentors(id, submission_id) ON DELETE CASCADE,
    INDEX idx_mentoring_records_order (submission_id, sort_order),
    INDEX idx_mentoring_records_mentor (mentor_id, submission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
