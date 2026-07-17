CREATE DATABASE IF NOT EXISTS hubinovasi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hubinovasi;

CREATE TABLE IF NOT EXISTS projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    name VARCHAR(160) NOT NULL,
    full_title VARCHAR(255) NULL,
    category VARCHAR(120) NULL,
    solution_area VARCHAR(120) NULL,
    project_type VARCHAR(160) NULL,
    tagline VARCHAR(255) NULL,
    short_description TEXT NULL,
    development_status VARCHAR(120) NOT NULL,
    verification_status ENUM('incomplete','unverified','verified') NOT NULL DEFAULT 'incomplete',
    review_status ENUM('draft','pending_review','needs_revision','published','archived') NOT NULL DEFAULT 'draft',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    display_order INT UNSIGNED NOT NULL DEFAULT 0,
    accent_class VARCHAR(80) NULL,
    platform VARCHAR(120) NULL,
    target_users VARCHAR(255) NULL,
    project_location VARCHAR(255) NULL,
    method VARCHAR(255) NULL,
    problem TEXT NULL,
    problem_points JSON NULL,
    solution TEXT NULL,
    solution_points JSON NULL,
    how_it_works JSON NULL,
    key_features JSON NULL,
    impact TEXT NULL,
    impact_points JSON NULL,
    technology_stack JSON NULL,
    technology_details JSON NULL,
    project_journey JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    INDEX idx_projects_public (review_status, verification_status, is_featured),
    INDEX idx_projects_development (development_status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS people (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    profile_slug VARCHAR(120) NULL UNIQUE,
    full_name VARCHAR(160) NOT NULL,
    person_type ENUM('student','mentor') NOT NULL,
    programme_or_position VARCHAR(180) NULL,
    bio TEXT NULL,
    skills JSON NULL,
    achievements JSON NULL,
    verification_status ENUM('incomplete','unverified','verified') NOT NULL DEFAULT 'incomplete',
    is_public TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS project_people (
    project_id BIGINT UNSIGNED NOT NULL,
    person_id BIGINT UNSIGNED NOT NULL,
    relationship_type ENUM('team','mentor') NOT NULL,
    role_title VARCHAR(160) NULL,
    contribution TEXT NULL,
    display_order INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (project_id, person_id, relationship_type),
    CONSTRAINT fk_project_people_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_people_person FOREIGN KEY (person_id) REFERENCES people(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS project_assets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    asset_type ENUM('project_logo','cover_image','application_screenshot','prototype_photograph','team_photograph','mentor_photograph','certificate','testing_evidence','competition_evidence') NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_filename VARCHAR(255) NULL,
    mime_type VARCHAR(120) NULL,
    alt_text VARCHAR(255) NULL,
    caption TEXT NULL,
    verification_status ENUM('unverified','verified','rejected') NOT NULL DEFAULT 'unverified',
    display_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_project_assets_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_project_assets_public (project_id, verification_status, asset_type)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS project_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    link_type ENUM('demo_url','video_url','repository_url','documentation_url','download_url','contact_url') NOT NULL,
    url VARCHAR(1000) NOT NULL,
    label VARCHAR(120) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    display_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_project_links_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    UNIQUE KEY uq_project_link_type (project_id, link_type)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS project_evidence (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    evidence_type ENUM('pilot_result','user_feedback','testing_result','impact_metric','award','certificate','competition') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    metric_value VARCHAR(120) NULL,
    metric_label VARCHAR(160) NULL,
    evidence_date DATE NULL,
    verification_status ENUM('unverified','verified','rejected') NOT NULL DEFAULT 'unverified',
    asset_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_project_evidence_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_evidence_asset FOREIGN KEY (asset_id) REFERENCES project_assets(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS submissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    public_token CHAR(64) NOT NULL UNIQUE,
    submitter_name VARCHAR(160) NULL,
    submitter_email VARCHAR(255) NULL,
    project_name VARCHAR(160) NULL,
    institution VARCHAR(255) NULL,
    solution_area VARCHAR(120) NULL,
    project_development_status VARCHAR(120) NULL,
    tagline VARCHAR(255) NULL,
    problem TEXT NULL,
    solution TEXT NULL,
    how_it_works TEXT NULL,
    key_features TEXT NULL,
    impact TEXT NULL,
    evidence_status VARCHAR(120) NULL,
    evidence_summary TEXT NULL,
    technologies TEXT NULL,
    team_details TEXT NULL,
    project_journey TEXT NULL,
    call_to_action VARCHAR(160) NULL,
    status ENUM('draft','pending_review','needs_revision','published','archived') NOT NULL DEFAULT 'draft',
    admin_notes TEXT NULL,
    linked_project_id BIGINT UNSIGNED NULL,
    submitted_at TIMESTAMP NULL,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_submission_project FOREIGN KEY (linked_project_id) REFERENCES projects(id) ON DELETE SET NULL,
    INDEX idx_submissions_status (status, updated_at),
    INDEX idx_submissions_email (submitter_email)
) ENGINE=InnoDB;
