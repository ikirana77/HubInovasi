USE hubinovasi;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(160) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student','lecturer') NOT NULL,
    account_status ENUM('pending','active','suspended') NOT NULL DEFAULT 'pending',
    institution VARCHAR(255) NULL,
    programme_or_position VARCHAR(180) NULL,
    last_login_at TIMESTAMP NULL,
    approved_at TIMESTAMP NULL,
    approved_by_admin_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_approver FOREIGN KEY (approved_by_admin_id) REFERENCES admin_users(id) ON DELETE SET NULL,
    INDEX idx_users_status_role (account_status, role),
    INDEX idx_users_email_status (email, account_status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    normalized_email_hash CHAR(64) NOT NULL,
    ip_hash CHAR(64) NOT NULL,
    success TINYINT(1) NOT NULL DEFAULT 0,
    attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_login_attempt_window (normalized_email_hash, ip_hash, success, attempted_at),
    INDEX idx_user_login_attempt_cleanup (attempted_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_account_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    from_status ENUM('pending','active','suspended') NOT NULL,
    to_status ENUM('pending','active','suspended') NOT NULL,
    admin_user_id BIGINT UNSIGNED NULL,
    admin_notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_history_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_history_admin FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL,
    INDEX idx_user_history_user (user_id, created_at)
) ENGINE=InnoDB;

SET @owner_column_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'submissions'
      AND COLUMN_NAME = 'owner_user_id'
);
SET @owner_column_sql = IF(@owner_column_exists = 0,
    'ALTER TABLE submissions ADD COLUMN owner_user_id BIGINT UNSIGNED NULL AFTER public_token',
    'DO 0');
PREPARE cp07_owner_column_stmt FROM @owner_column_sql;
EXECUTE cp07_owner_column_stmt;
DEALLOCATE PREPARE cp07_owner_column_stmt;

SET @owner_fk_exists = (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'submissions'
      AND CONSTRAINT_NAME = 'fk_submissions_owner'
);
SET @owner_fk_sql = IF(@owner_fk_exists = 0,
    'ALTER TABLE submissions ADD CONSTRAINT fk_submissions_owner FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL',
    'DO 0');
PREPARE cp07_owner_fk_stmt FROM @owner_fk_sql;
EXECUTE cp07_owner_fk_stmt;
DEALLOCATE PREPARE cp07_owner_fk_stmt;

SET @owner_index_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'submissions'
      AND INDEX_NAME = 'idx_submissions_owner_status'
);
SET @owner_index_sql = IF(@owner_index_exists = 0,
    'ALTER TABLE submissions ADD INDEX idx_submissions_owner_status (owner_user_id, status, updated_at)',
    'DO 0');
PREPARE cp07_owner_index_stmt FROM @owner_index_sql;
EXECUTE cp07_owner_index_stmt;
DEALLOCATE PREPARE cp07_owner_index_stmt;
