USE hubinovasi;

CREATE TABLE IF NOT EXISTS admin_users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(160) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin') NOT NULL DEFAULT 'admin',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_admin_users_active_email (is_active, email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS admin_login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    normalized_email_hash CHAR(64) NOT NULL,
    ip_hash CHAR(64) NOT NULL,
    success TINYINT(1) NOT NULL DEFAULT 0,
    attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_attempt_window (normalized_email_hash, ip_hash, success, attempted_at),
    INDEX idx_login_attempt_cleanup (attempted_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS submission_status_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id BIGINT UNSIGNED NOT NULL,
    from_status ENUM('draft','pending_review','needs_revision','published','archived') NOT NULL,
    to_status ENUM('draft','pending_review','needs_revision','published','archived') NOT NULL,
    admin_user_id BIGINT UNSIGNED NULL,
    admin_notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_status_history_submission FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    CONSTRAINT fk_status_history_admin FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL,
    INDEX idx_status_history_submission (submission_id, created_at)
) ENGINE=InnoDB;

SET @column_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'submissions'
      AND COLUMN_NAME = 'reviewed_by_admin_id'
);
SET @column_sql = IF(@column_exists = 0,
    'ALTER TABLE submissions ADD COLUMN reviewed_by_admin_id BIGINT UNSIGNED NULL AFTER reviewed_at',
    'DO 0');
PREPARE cp06_column_stmt FROM @column_sql;
EXECUTE cp06_column_stmt;
DEALLOCATE PREPARE cp06_column_stmt;

SET @fk_exists = (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'submissions'
      AND CONSTRAINT_NAME = 'fk_submissions_reviewer'
);
SET @fk_sql = IF(@fk_exists = 0,
    'ALTER TABLE submissions ADD CONSTRAINT fk_submissions_reviewer FOREIGN KEY (reviewed_by_admin_id) REFERENCES admin_users(id) ON DELETE SET NULL',
    'DO 0');
PREPARE cp06_stmt FROM @fk_sql;
EXECUTE cp06_stmt;
DEALLOCATE PREPARE cp06_stmt;
