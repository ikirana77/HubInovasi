
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

ALTER TABLE submissions
    ADD COLUMN IF NOT EXISTS project_development_status VARCHAR(120) NULL AFTER solution_area;


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


ALTER TABLE users MODIFY COLUMN password_hash VARCHAR(255) NULL;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='google_sub')=0,
    'ALTER TABLE users ADD COLUMN google_sub VARCHAR(255) NULL AFTER password_hash', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='google_email')=0,
    'ALTER TABLE users ADD COLUMN google_email VARCHAR(255) NULL AFTER google_sub', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='avatar_url')=0,
    'ALTER TABLE users ADD COLUMN avatar_url VARCHAR(2048) NULL AFTER google_email', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='auth_provider')=0,
    "ALTER TABLE users ADD COLUMN auth_provider ENUM('password','google','both') NOT NULL DEFAULT 'password' AFTER avatar_url", 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='google_linked_at')=0,
    'ALTER TABLE users ADD COLUMN google_linked_at TIMESTAMP NULL AFTER auth_provider', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='google_last_login_at')=0,
    'ALTER TABLE users ADD COLUMN google_last_login_at TIMESTAMP NULL AFTER google_linked_at', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND INDEX_NAME='uq_users_google_sub')=0,
    'ALTER TABLE users ADD UNIQUE INDEX uq_users_google_sub (google_sub)', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND INDEX_NAME='idx_users_auth_provider')=0,
    'ALTER TABLE users ADD INDEX idx_users_auth_provider (auth_provider, account_status)', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


CREATE TABLE IF NOT EXISTS programmes (
    code CHAR(3) PRIMARY KEY,
    name_ms VARCHAR(180) NOT NULL,
    name_en VARCHAR(180) NOT NULL,
    display_order TINYINT UNSIGNED NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO programmes (code,name_ms,name_en,display_order) VALUES
('KPD','Teknologi Sistem Pengurusan Pangkalan Data dan Aplikasi Web','Database Management Systems and Web Applications Technology',1),
('KMK','Teknologi Sistem Komputer dan Rangkaian','Computer Systems and Networking Technology',2),
('BAK','Perakaunan','Accounting',3),
('BPM','Pengurusan Perniagaan','Business Management',4),
('HSK','Seni Kulinari','Culinary Arts',5),
('HBP','Bakeri dan Pastri','Bakery and Pastry',6),
('OPP','Operasi Pembuatan Perabot','Furniture Manufacturing Operations',7)
ON DUPLICATE KEY UPDATE name_ms=VALUES(name_ms),name_en=VALUES(name_en),display_order=VALUES(display_order),is_active=1;

CREATE TABLE IF NOT EXISTS solution_areas (
    slug VARCHAR(80) PRIMARY KEY,
    name_ms VARCHAR(180) NOT NULL,
    name_en VARCHAR(180) NOT NULL,
    description_ms TEXT NULL,
    description_en TEXT NULL,
    display_order TINYINT UNSIGNED NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO solution_areas (slug,name_ms,name_en,description_ms,description_en,display_order) VALUES
('digital-systems-intelligence','Sistem Digital & Kecerdasan Pintar','Digital Systems & Intelligence','Aplikasi, AI, pangkalan data, IoT, rangkaian dan automasi yang menyelesaikan masalah sebenar.','Applications, AI, databases, IoT, networking and automation solving real problems.',1),
('business-finance-entrepreneurship','Perniagaan, Kewangan & Keusahawanan','Business, Finance & Entrepreneurship','Fintech, perakaunan, pemasaran, inventori dan model perniagaan yang lebih berkesan.','Fintech, accounting, marketing, inventory and more effective business models.',2),
('food-culinary-nutrition','Inovasi Makanan, Kulinari & Nutrisi','Food, Culinary & Nutrition Innovation','Produk makanan, resipi, pembungkusan, nutrisi dan keselamatan makanan yang dipertingkat.','Improved food products, recipes, packaging, nutrition and food safety.',3),
('hospitality-service-experience','Hospitaliti, Perkhidmatan & Reka Bentuk Pengalaman','Hospitality, Service & Experience Design','Pengalaman pelanggan, tempahan, acara dan operasi perkhidmatan yang lebih baik.','Better customer experiences, bookings, events and service operations.',4),
('product-furniture-manufacturing','Reka Bentuk Produk, Perabot & Pembuatan','Product, Furniture & Manufacturing Innovation','Produk ergonomik, perabot modular, bahan dan proses pembuatan yang lebih pintar.','Smarter ergonomic products, modular furniture, materials and manufacturing processes.',5),
('sustainability-circular-economy','Kelestarian & Ekonomi Kitaran','Sustainability & Circular Economy','Pengurangan sisa, guna semula bahan, penjimatan tenaga dan penyelesaian hijau.','Waste reduction, material reuse, energy savings and green solutions.',6),
('community-education-wellbeing','Komuniti, Pendidikan & Kesejahteraan','Community, Education & Well-being','Pembelajaran, inklusiviti, kesejahteraan dan penyelesaian untuk komuniti.','Learning, inclusion, well-being and solutions for communities.',7),
('smart-campus-safety-operations','Kampus Pintar, Keselamatan & Operasi','Smart Campus, Safety & Operations','Kehadiran, parkir, aduan, keselamatan, aset dan operasi institusi yang lebih lancar.','Smoother attendance, parking, reporting, safety, assets and institutional operations.',8)
ON DUPLICATE KEY UPDATE name_ms=VALUES(name_ms),name_en=VALUES(name_en),description_ms=VALUES(description_ms),description_en=VALUES(description_en),display_order=VALUES(display_order),is_active=1;

SET @add_projects_innovation_type = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='projects' AND COLUMN_NAME='innovation_type') = 0,
    'ALTER TABLE projects ADD COLUMN innovation_type VARCHAR(80) NULL AFTER project_type',
    'SELECT 1'
);
PREPARE cp09_stmt FROM @add_projects_innovation_type; EXECUTE cp09_stmt; DEALLOCATE PREPARE cp09_stmt;

SET @add_submissions_innovation_type = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='submissions' AND COLUMN_NAME='innovation_type') = 0,
    'ALTER TABLE submissions ADD COLUMN innovation_type VARCHAR(80) NULL AFTER solution_area',
    'SELECT 1'
);
PREPARE cp09_stmt FROM @add_submissions_innovation_type; EXECUTE cp09_stmt; DEALLOCATE PREPARE cp09_stmt;

SET @add_submissions_programme_codes = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='submissions' AND COLUMN_NAME='programme_codes') = 0,
    'ALTER TABLE submissions ADD COLUMN programme_codes JSON NULL AFTER innovation_type',
    'SELECT 1'
);
PREPARE cp09_stmt FROM @add_submissions_programme_codes; EXECUTE cp09_stmt; DEALLOCATE PREPARE cp09_stmt;

CREATE TABLE IF NOT EXISTS project_programmes (
    project_id BIGINT UNSIGNED NOT NULL,
    programme_code CHAR(3) NOT NULL,
    contribution_type ENUM('lead','contributor') NOT NULL DEFAULT 'contributor',
    PRIMARY KEY (project_id,programme_code),
    CONSTRAINT fk_project_programmes_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_project_programmes_programme FOREIGN KEY (programme_code) REFERENCES programmes(code) ON DELETE RESTRICT
) ENGINE=InnoDB;

UPDATE projects SET solution_area = CASE slug
    WHEN 'hers' THEN 'smart-campus-safety-operations'
    WHEN 'spark' THEN 'smart-campus-safety-operations'
    WHEN 'durian-radar' THEN 'sustainability-circular-economy'
    WHEN 'cms-quest' THEN 'community-education-wellbeing'
    WHEN 'careline-kvks' THEN 'smart-campus-safety-operations'
    WHEN 'visionlab' THEN 'digital-systems-intelligence'
    ELSE solution_area END;

UPDATE projects SET innovation_type = CASE slug
    WHEN 'visionlab' THEN 'research-prototype'
    ELSE 'digital-solution' END
WHERE innovation_type IS NULL;

-- Existing projects are intentionally not assigned to a programme automatically.
-- Programme ownership must be confirmed from the project team before publication.


CREATE TABLE IF NOT EXISTS project_collaborators (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    collaborator_type ENUM('problem_owner','operational_partner','industry_partner','community_partner') NOT NULL,
    role_ms TEXT NULL,
    role_en TEXT NULL,
    verification_status ENUM('unverified','verified') NOT NULL DEFAULT 'unverified',
    display_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_project_collaborator (project_id,name),
    CONSTRAINT fk_project_collaborators_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

UPDATE projects SET
    full_title = 'HERS — Sistem Rekod, Pemantauan Pola dan Sokongan Intervensi',
    category = 'digital-solution',
    solution_area = 'smart-campus-safety-operations',
    innovation_type = 'digital-solution',
    project_type = 'Sistem Rekod dan Analitik Berasaskan Kod QR',
    tagline = 'Rekod cepat. Analitik berhemah. Intervensi lebih awal.',
    short_description = 'HERS menghubungkan rekod pantas oleh BADAR, penyusunan data secara sistematik dan semakan pola oleh warden untuk menyokong intervensi awal yang lebih berhemah.',
    platform = 'Android',
    target_users = 'BADAR, Warden dan Pelajar Tingkat Atas',
    project_location = 'Pusat Islam Raudhatul Jannah',
    method = 'Imbasan QR, rekod manual sokongan dan analitik pola',
    problem = 'Proses merekod pelajar yang tidak menunaikan solat dan berada di tingkat atas memerlukan aliran yang pantas, konsisten dan mudah disemak. Catatan manual boleh memperlahankan checkpoint, menghasilkan kesilapan atau rekod berganda, dan menyukarkan BADAR serta warden melihat pola dari semasa ke semasa.',
    problem_points = JSON_ARRAY(
        'Proses merekod di checkpoint Tangga Atas Surau perlu dipercepat',
        'Kesilapan catatan dan rekod berganda perlu dikurangkan',
        'Rekod harian perlu mudah disemak oleh BADAR',
        'Pola jangka masa, kekerapan dan ketidakselarasan sukar dilihat secara manual',
        'Intervensi awal memerlukan bukti dan konteks, bukan andaian'
    ),
    solution = 'HERS merekod pelajar tingkat atas melalui kod QR yang diimbas oleh BADAR, menyusun rekod harian dan menyediakan analitik pola untuk semakan warden. Pelajar yang menunaikan solat terus ke ruang solat tanpa mengimbas. Analitik hanya menjadi petunjuk untuk semakan manusia dan tidak digunakan untuk diagnosis atau hukuman automatik.',
    solution_points = JSON_ARRAY(
        'Rekod QR automatik untuk aliran operasi BADAR yang pantas',
        'Perlindungan rekod berganda dan rekod manual sebagai sokongan',
        'Analitik pola dengan label neutral seperti Perlu Semakan',
        'Semakan manusia, kawalan akses dan perlindungan privasi pelajar'
    ),
    how_it_works = JSON_ARRAY(
        JSON_OBJECT('title','BADAR Membuka Sesi','text','BADAR membuka sesi harian melalui aplikasi pada waktu yang ditetapkan.'),
        JSON_OBJECT('title','Pelajar Solat Terus ke Ruang Solat','text','Pelajar yang menunaikan solat tidak perlu mengimbas kod QR.'),
        JSON_OBJECT('title','Pelajar Tingkat Atas Menunjukkan QR','text','Pelajar yang tidak menunaikan solat menuju ke Tangga Atas Surau dan menunjukkan kod QR.'),
        JSON_OBJECT('title','BADAR Mengimbas QR','text','Nama pelajar direkodkan secara automatik tanpa butang pengesahan tambahan.'),
        JSON_OBJECT('title','Rekod Berganda Disekat','text','Sistem menghalang rekod kedua bagi pelajar yang sama dalam sesi berkenaan.'),
        JSON_OBJECT('title','Rekod Manual Jika Diperlukan','text','BADAR boleh membuat rekod manual apabila kod QR tidak dapat digunakan.'),
        JSON_OBJECT('title','Sesi Disemak dan Ditutup','text','BADAR menyemak jumlah rekod dan menutup sesi selepas proses selesai.')
    ),
    key_features = JSON_ARRAY(
        'Imbasan QR dan rekod automatik',
        'Perlindungan rekod berganda',
        'Rekod manual sebagai kaedah sokongan',
        'Pengurusan sesi harian oleh BADAR',
        'Analitik pola untuk semakan warden',
        'Label neutral: Perlu Semakan dan Perlu Intervensi',
        'Kawalan akses mengikut peranan',
        'Sokongan offline queue dan eksport rekod'
    ),
    impact = 'HERS berpotensi mempercepat rekod operasi BADAR, mengurangkan kesilapan catatan dan membantu warden mengenal pasti pola yang memerlukan semakan lanjut. Sebarang intervensi mesti dilakukan secara tertutup, berempati dan berdasarkan konteks. Tiada statistik impak diterbitkan sehingga bukti penggunaan disahkan.',
    impact_points = JSON_ARRAY(
        'Rekod harian BADAR menjadi lebih pantas dan mudah disemak',
        'Kebergantungan kepada catatan manual dan risiko rekod berganda dikurangkan',
        'Warden mendapat petunjuk pola untuk semakan dan intervensi awal',
        'Keputusan disokong oleh rekod dan konteks, bukan andaian',
        'Privasi dan maruah pelajar kekal sebagai prinsip penggunaan'
    )
WHERE slug = 'hers';

DELETE pp FROM project_programmes pp
JOIN projects p ON p.id = pp.project_id
WHERE p.slug = 'hers' AND pp.programme_code <> 'KPD';

INSERT INTO project_programmes (project_id,programme_code,contribution_type)
SELECT id,'KPD','lead' FROM projects WHERE slug='hers'
ON DUPLICATE KEY UPDATE contribution_type='lead';

INSERT INTO project_collaborators (project_id,name,collaborator_type,role_ms,role_en,verification_status,display_order)
SELECT id,'BADAR','problem_owner',
       'Pemilik masalah dan pengguna operasi harian: membuka sesi, mengimbas QR, menyemak rekod dan menutup sesi.',
       'Problem owner and daily operational user: opens sessions, scans QR codes, reviews records and closes sessions.',
       'verified',1
FROM projects WHERE slug='hers'
ON DUPLICATE KEY UPDATE collaborator_type=VALUES(collaborator_type),role_ms=VALUES(role_ms),role_en=VALUES(role_en),verification_status='verified',display_order=1;


CREATE TABLE IF NOT EXISTS project_team_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    member_name VARCHAR(180) NOT NULL,
    role_ms VARCHAR(160) NULL,
    role_en VARCHAR(160) NULL,
    verification_status ENUM('unverified','verified') NOT NULL DEFAULT 'unverified',
    display_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_project_team_member (project_id,member_name),
    CONSTRAINT fk_project_team_members_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO projects (
    slug,name,full_title,category,solution_area,project_type,innovation_type,tagline,short_description,
    development_status,verification_status,review_status,is_featured,display_order,accent_class,
    platform,target_users,project_location,method,problem,problem_points,solution,solution_points,
    how_it_works,key_features,impact,impact_points,technology_stack,technology_details,project_journey,published_at
) VALUES (
    'durian-radar',
    'Durian Radar',
    'Durian Radar: Aplikasi Peta Komuniti Berasaskan Freshness Status untuk Mencari Lokasi Durian Secara Terkini di Malaysia',
    'Teknologi Digital / Aplikasi Mudah Alih / Komuniti / Agrotech',
    'sustainability-circular-economy',
    'Aplikasi Peta Komuniti Berasaskan Freshness Status',
    'digital-solution',
    'Cari durian terkini melalui peta komuniti yang lebih segar dan diyakini.',
    'Durian Radar membantu pengguna mencari lokasi durian, melihat maklumat stok, harga dan varieti, serta berkongsi laporan semasa melalui peta komuniti yang disokong Freshness Status dan semakan admin.',
    'MVP / Active Development',
    'verified','published',0,3,'project-card--sky',
    'Android',
    'Pembeli durian, peminat durian, komuniti setempat, pelancong domestik, penjual durian kecil dan pentadbir aplikasi',
    'Malaysia',
    'Peta komuniti, laporan pengguna, Freshness Status dan semakan admin',
    'Pembeli sukar mengetahui gerai durian yang sedang beroperasi, stok semasa, harga dan varieti yang tersedia. Maklumat pada peta biasa, media sosial dan kumpulan mesej lazimnya berselerak, statik dan cepat lapuk, menyebabkan perjalanan yang tidak perlu serta pembaziran masa, bahan api dan tenaga.',
    JSON_ARRAY(
        'Status operasi gerai dan ketersediaan stok berubah dengan cepat',
        'Maklumat harga, varieti dan lokasi tersebar di pelbagai saluran',
        'Maklumat lama boleh menyebabkan perjalanan yang sia-sia',
        'Penjual kecil sukar mendapat keterlihatan pada masa yang tepat'
    ),
    'Durian Radar menyediakan peta interaktif yang membolehkan komuniti melaporkan lokasi, varieti, harga sekilogram, status stok dan gambar. Freshness Status menunjukkan tahap kesegaran maklumat, manakala Admin Review dan pengesahan komuniti membantu memastikan laporan lebih terkini dan boleh dipercayai.',
    JSON_ARRAY(
        'Peta komuniti khusus untuk lokasi durian',
        'Laporan ringkas tentang varieti, harga, stok dan gambar',
        'Freshness Status membezakan laporan baharu, perlu disemak, habis atau luput',
        'Admin Review dan pengesahan komuniti menyokong kebolehpercayaan maklumat'
    ),
    JSON_ARRAY(
        JSON_OBJECT('title','Buka Peta','text','Pengguna melihat lokasi durian berhampiran melalui paparan peta utama.'),
        JSON_OBJECT('title','Semak Freshness Status','text','Warna pin menunjukkan sama ada maklumat masih baharu, perlu disemak, habis atau telah luput.'),
        JSON_OBJECT('title','Lihat Butiran Lokasi','text','Pengguna menyemak varieti, harga, stok, gambar dan masa laporan.'),
        JSON_OBJECT('title','Hantar Laporan Komuniti','text','Pengguna menambah atau mengemas kini maklumat lokasi melalui borang ringkas.'),
        JSON_OBJECT('title','Semakan Admin','text','Pentadbir menyemak laporan yang memerlukan pengesahan sebelum status dikemas kini.'),
        JSON_OBJECT('title','Pengesahan Komuniti','text','Maklum balas pengguna membantu mengekalkan ketepatan dan kerelevanan lokasi.'),
        JSON_OBJECT('title','Laporan Luput','text','Maklumat lama ditandai sebagai luput supaya pengguna tidak bergantung pada data yang tidak terkini.')
    ),
    JSON_ARRAY(
        'Home Map dan carian lokasi',
        'Pin Detail untuk varieti, harga, stok dan gambar',
        'Report Durian untuk laporan komuniti',
        'Fresh List bagi lokasi terkini',
        'Freshness Status berasaskan warna dan tempoh laporan',
        'Login dan pendaftaran pengguna',
        'Admin Review untuk moderasi laporan'
    ),
    'Durian Radar berpotensi menjimatkan masa dan perjalanan pengguna, mengukuhkan perkongsian maklumat komuniti, meningkatkan keterlihatan penjual kecil dan menyokong pelancongan domestik serta ekonomi tempatan. Impak ini masih berupa potensi; angka sasaran dalam laporan tidak diterbitkan sebagai pencapaian sebenar sehingga bukti penggunaan disahkan.',
    JSON_ARRAY(
        'Mengurangkan perjalanan ke lokasi yang sudah tutup atau kehabisan stok',
        'Memudahkan komuniti berkongsi maklumat durian yang lebih terkini',
        'Memberi keterlihatan kepada penjual durian kecil',
        'Menyokong penerokaan durian tempatan dan aktiviti pelancongan domestik',
        'Menyediakan asas data komuniti untuk penambahbaikan pada fasa seterusnya'
    ),
    JSON_ARRAY('Flutter','Supabase','OpenStreetMap','GitHub','Android'),
    JSON_ARRAY(
        'Flutter — pembangunan aplikasi mudah alih Android',
        'Supabase — pangkalan data, autentikasi dan storan',
        'OpenStreetMap — paparan peta dan koordinat lokasi',
        'GitHub — kawalan versi dan dokumentasi pembangunan',
        'Admin Review — moderasi laporan komuniti',
        'Freshness and Expiry Logic — status laporan mengikut kesegaran maklumat'
    ),
    JSON_ARRAY(
        'Masalah dan skop projek dikenal pasti',
        'Reka bentuk antara muka rasmi dimuktamadkan',
        'Struktur modul MVP disediakan',
        'Reka bentuk awal pangkalan data disediakan',
        'Pembangunan Flutter dan paparan emulator didokumenkan',
        'Laporan komuniti dan Admin Review sedang dibangunkan',
        'Penerbitan Google Play dirancang untuk fasa seterusnya'
    ),
    CURRENT_TIMESTAMP
)
ON DUPLICATE KEY UPDATE
    name=VALUES(name),full_title=VALUES(full_title),category=VALUES(category),solution_area=VALUES(solution_area),
    project_type=VALUES(project_type),innovation_type=VALUES(innovation_type),tagline=VALUES(tagline),
    short_description=VALUES(short_description),development_status=VALUES(development_status),
    verification_status=VALUES(verification_status),review_status=VALUES(review_status),is_featured=VALUES(is_featured),
    display_order=VALUES(display_order),accent_class=VALUES(accent_class),platform=VALUES(platform),
    target_users=VALUES(target_users),project_location=VALUES(project_location),method=VALUES(method),
    problem=VALUES(problem),problem_points=VALUES(problem_points),solution=VALUES(solution),
    solution_points=VALUES(solution_points),how_it_works=VALUES(how_it_works),key_features=VALUES(key_features),
    impact=VALUES(impact),impact_points=VALUES(impact_points),technology_stack=VALUES(technology_stack),
    technology_details=VALUES(technology_details),project_journey=VALUES(project_journey),published_at=VALUES(published_at);

INSERT INTO project_team_members (project_id,member_name,role_ms,role_en,verification_status,display_order)
SELECT id,'Intan Keristina Mohd Yusop','Ketua Projek','Project Leader','verified',1 FROM projects WHERE slug='durian-radar'
ON DUPLICATE KEY UPDATE role_ms=VALUES(role_ms),role_en=VALUES(role_en),verification_status='verified',display_order=1;

INSERT INTO project_team_members (project_id,member_name,role_ms,role_en,verification_status,display_order)
SELECT id,'Hanum','Ahli Projek','Project Member','verified',2 FROM projects WHERE slug='durian-radar'
ON DUPLICATE KEY UPDATE role_ms=VALUES(role_ms),role_en=VALUES(role_en),verification_status='verified',display_order=2;

INSERT INTO project_team_members (project_id,member_name,role_ms,role_en,verification_status,display_order)
SELECT id,'Qausar','Ahli Projek','Project Member','verified',3 FROM projects WHERE slug='durian-radar'
ON DUPLICATE KEY UPDATE role_ms=VALUES(role_ms),role_en=VALUES(role_en),verification_status='verified',display_order=3;

INSERT INTO project_team_members (project_id,member_name,role_ms,role_en,verification_status,display_order)
SELECT id,'Sharizat','Ahli Projek','Project Member','verified',4 FROM projects WHERE slug='durian-radar'
ON DUPLICATE KEY UPDATE role_ms=VALUES(role_ms),role_en=VALUES(role_en),verification_status='verified',display_order=4;

INSERT INTO project_team_members (project_id,member_name,role_ms,role_en,verification_status,display_order)
SELECT id,'Nazlah','Ahli Projek','Project Member','verified',5 FROM projects WHERE slug='durian-radar'
ON DUPLICATE KEY UPDATE role_ms=VALUES(role_ms),role_en=VALUES(role_en),verification_status='verified',display_order=5;

DELETE FROM project_assets
WHERE project_id=(SELECT id FROM projects WHERE slug='durian-radar')
  AND file_path='assets/images/projects/durian-radar-main.png';

INSERT INTO project_assets (project_id,asset_type,file_path,original_filename,mime_type,alt_text,caption,verification_status,display_order)
SELECT id,'application_screenshot','assets/images/projects/durian-radar-main.png',
       'Laporan Projek Inovasi Durian Radar-3(1).pdf — halaman 4','image/png',
       'Paparan peta utama aplikasi Durian Radar pada telefon',
       'Visual aplikasi daripada laporan projek Durian Radar.','verified',1
FROM projects WHERE slug='durian-radar';

DELETE pp FROM project_programmes pp
JOIN projects p ON p.id=pp.project_id
WHERE p.slug='durian-radar' AND pp.programme_code <> 'KPD';

INSERT INTO project_programmes (project_id,programme_code,contribution_type)
SELECT id,'KPD','lead' FROM projects WHERE slug='durian-radar'
ON DUPLICATE KEY UPDATE contribution_type='lead';

-- Durian Radar disahkan tidak mempunyai rakan kolaborasi luar.
SET @clear_durian_collaborators = IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='project_collaborators') > 0,
    'DELETE pc FROM project_collaborators pc JOIN projects p ON p.id=pc.project_id WHERE p.slug=''durian-radar''',
    'SELECT 1'
);
PREPARE cp09c_stmt FROM @clear_durian_collaborators;
EXECUTE cp09c_stmt;
DEALLOCATE PREPARE cp09c_stmt;


-- CP10A prepares the adaptive submission data model. The legacy submissions
-- table remains the parent and its existing columns are intentionally unchanged.

CREATE TABLE IF NOT EXISTS submission_people (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id BIGINT UNSIGNED NOT NULL,
    full_name VARCHAR(160) NOT NULL,
    role_title VARCHAR(160) NULL,
    programme VARCHAR(180) NULL,
    email VARCHAR(255) NULL,
    bio TEXT NULL,
    contribution TEXT NULL,
    profile_image_path VARCHAR(500) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_submission_people_submission FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    INDEX idx_submission_people_order (submission_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS submission_mentors (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id BIGINT UNSIGNED NOT NULL,
    full_name VARCHAR(160) NOT NULL,
    position_title VARCHAR(180) NULL,
    institution VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    bio TEXT NULL,
    mentorship_contribution TEXT NULL,
    profile_image_path VARCHAR(500) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_submission_mentors_submission FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    INDEX idx_submission_mentors_order (submission_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS submission_features (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id BIGINT UNSIGNED NOT NULL,
    feature_title VARCHAR(180) NOT NULL,
    feature_description TEXT NULL,
    feature_type VARCHAR(80) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_submission_features_submission FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    INDEX idx_submission_features_order (submission_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS submission_steps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id BIGINT UNSIGNED NOT NULL,
    step_number TINYINT UNSIGNED NOT NULL,
    completion_status VARCHAR(40) NOT NULL DEFAULT 'not_started',
    completed_at TIMESTAMP NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_submission_steps_submission FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    UNIQUE KEY uq_submission_steps_number (submission_id, step_number),
    INDEX idx_submission_steps_status (submission_id, completion_status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS submission_metrics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id BIGINT UNSIGNED NOT NULL,
    metric_label VARCHAR(180) NOT NULL,
    metric_value VARCHAR(120) NULL,
    metric_unit VARCHAR(80) NULL,
    baseline_value VARCHAR(120) NULL,
    target_value VARCHAR(120) NULL,
    measured_at DATE NULL,
    evidence_notes TEXT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_submission_metrics_submission FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    INDEX idx_submission_metrics_order (submission_id, sort_order),
    INDEX idx_submission_metrics_date (submission_id, measured_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS submission_awards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id BIGINT UNSIGNED NOT NULL,
    award_title VARCHAR(255) NOT NULL,
    organiser VARCHAR(255) NULL,
    award_level VARCHAR(120) NULL,
    award_date DATE NULL,
    description TEXT NULL,
    evidence_path VARCHAR(500) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_submission_awards_submission FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    INDEX idx_submission_awards_order (submission_id, sort_order),
    INDEX idx_submission_awards_date (submission_id, award_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS submission_media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id BIGINT UNSIGNED NOT NULL,
    media_type VARCHAR(80) NOT NULL,
    file_path VARCHAR(500) NULL,
    original_filename VARCHAR(255) NULL,
    mime_type VARCHAR(120) NULL,
    external_url VARCHAR(1000) NULL,
    alt_text VARCHAR(255) NULL,
    caption TEXT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_submission_media_submission FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    INDEX idx_submission_media_order (submission_id, sort_order),
    INDEX idx_submission_media_type (submission_id, media_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS submission_milestones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id BIGINT UNSIGNED NOT NULL,
    milestone_title VARCHAR(255) NOT NULL,
    milestone_description TEXT NULL,
    milestone_date DATE NULL,
    milestone_status VARCHAR(80) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_submission_milestones_submission FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    INDEX idx_submission_milestones_order (submission_id, sort_order),
    INDEX idx_submission_milestones_date (submission_id, milestone_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS submission_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id BIGINT UNSIGNED NOT NULL,
    link_type VARCHAR(80) NOT NULL,
    label VARCHAR(160) NULL,
    url VARCHAR(1000) NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_submission_links_submission FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    INDEX idx_submission_links_order (submission_id, sort_order),
    INDEX idx_submission_links_type (submission_id, link_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS submission_category_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id BIGINT UNSIGNED NOT NULL,
    category_slug VARCHAR(80) NOT NULL,
    detail_key VARCHAR(120) NOT NULL,
    detail_value TEXT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_submission_category_details_submission FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    UNIQUE KEY uq_submission_category_detail (submission_id, category_slug, detail_key),
    INDEX idx_submission_category_details_order (submission_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


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


-- CP10F reuses the CP10A media, links and milestones tables. Only the cover
-- marker required by the gallery is added here.
SET @cp10f_cover_column := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'submission_media'
      AND COLUMN_NAME = 'is_cover'
);
SET @cp10f_sql := IF(
    @cp10f_cover_column = 0,
    'ALTER TABLE submission_media ADD COLUMN is_cover TINYINT(1) NOT NULL DEFAULT 0 AFTER caption',
    'SELECT 1'
);
PREPARE cp10f_statement FROM @cp10f_sql;
EXECUTE cp10f_statement;
DEALLOCATE PREPARE cp10f_statement;

-- Early CP10F builds used project_gallery. Normalise those rows to the
-- canonical CP10A media type without touching media owned by other sections.
UPDATE submission_media
SET media_type = 'gallery'
WHERE media_type = 'project_gallery';

SET @cp10f_cover_index := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'submission_media'
      AND INDEX_NAME = 'idx_submission_media_cover'
);
SET @cp10f_sql := IF(
    @cp10f_cover_index = 0,
    'ALTER TABLE submission_media ADD INDEX idx_submission_media_cover (submission_id, media_type, is_cover, sort_order)',
    'SELECT 1'
);
PREPARE cp10f_statement FROM @cp10f_sql;
EXECUTE cp10f_statement;
DEALLOCATE PREPARE cp10f_statement;


INSERT INTO projects (
    slug, name, full_title, category, solution_area, project_type, tagline, short_description,
    development_status, verification_status, review_status, is_featured, display_order, accent_class,
    platform, target_users, project_location, method, problem, problem_points, solution, solution_points,
    how_it_works, key_features, impact, impact_points, technology_stack, technology_details, project_journey, published_at
) VALUES (
    'hers', 'HERS', 'HERS — Sistem Rekod Kehadiran Muslimah ke Tingkat Atas Surau',
    'Aplikasi Mudah Alih', 'smart-campus-safety-operations', 'Sistem Pengurusan Kehadiran Berasaskan Kod QR',
    'Kehadiran yang lebih pantas, teratur dan bermakna.',
    'Sistem kehadiran berasaskan kod QR yang membantu merekod pergerakan pelajar Muslimah ke tingkat atas Pusat Islam Raudhatul Jannah dengan aliran yang pantas, mudah dan tersusun.',
    'Functional Pilot / Release Candidate', 'verified', 'published', 1, 1, 'project-card--peach',
    'Android', 'Admin, Warden dan BADAR', 'Pusat Islam Raudhatul Jannah', 'Imbasan QR dan Rekod Manual',
    'Proses merekod kehadiran pelajar Muslimah ke tingkat atas surau memerlukan kaedah yang lebih pantas dan konsisten. Rekod manual mengambil masa, sukar disemak semula dan tidak memberikan gambaran jelas tentang corak kehadiran.',
    JSON_ARRAY('Proses manual mengambil masa','Rekod sukar disemak secara menyeluruh','Corak ketidakhadiran sukar dikenal pasti'),
    'HERS menggunakan kod QR untuk merekod kehadiran secara automatik. Pelajar menunjukkan kod QR, BADAR mengimbasnya dan rekod disimpan tanpa proses pengesahan tambahan.',
    JSON_ARRAY('Imbas dan teruskan perjalanan','Rekod automatik tanpa pengesahan berulang','Rekod manual sebagai kaedah sokongan','Perlindungan daripada imbasan pendua','Data boleh disemak oleh pengguna yang dibenarkan'),
    JSON_ARRAY(
        JSON_OBJECT('title','BADAR Membuka Sesi','text','Sesi harian dibuka bermula pada pukul 6:30 petang.'),
        JSON_OBJECT('title','Pelajar Menunjukkan Kod QR','text','Kod QR pelajar digunakan sebagai kad imbasan rasmi.'),
        JSON_OBJECT('title','Kod Diimbas','text','Sistem mengesahkan pelajar dan merekod kehadiran secara automatik.'),
        JSON_OBJECT('title','Maklum Balas Segera','text','Bunyi, warna dan paparan status membantu mempercepatkan aliran.'),
        JSON_OBJECT('title','Rekod Boleh Disemak','text','Warden dan pentadbir boleh melihat rekod serta pola yang memerlukan semakan.')
    ),
    JSON_ARRAY('Fast Scan Camera','Rekod Automatik','Perlindungan Imbasan Pendua','Rekod Manual Sandaran','Sesi Harian Boleh Dibuka Semula','Rekod Hari Ini','Analitik Kehadiran','Eksport CSV atau Excel','Kawalan Akses Mengikut Peranan','Sokongan Offline Queue'),
    'HERS berpotensi mempercepatkan aliran pelajar, mengurangkan catatan manual dan menyediakan rekod yang lebih mudah dijejak. Tiada statistik penggunaan diterbitkan sehingga bukti disahkan.',
    JSON_ARRAY('Mempercepatkan aliran pelajar di tangga atas surau','Mengurangkan kebergantungan kepada catatan manual','Menyediakan rekod yang lebih mudah dijejak','Membantu warden mengenal pasti keadaan yang memerlukan semakan','Menyokong keputusan berasaskan rekod, bukan andaian'),
    JSON_ARRAY('Flutter','Firebase','QR Code','Android','Offline Queue'),
    JSON_ARRAY('Flutter — pembangunan aplikasi Android','Firebase Authentication — log masuk Google','Cloud Firestore — penyimpanan sesi dan rekod','QR Scanner — pengesanan identiti pelajar','Local Offline Queue — simpanan sementara apabila sambungan terganggu','Role-Based Access — kawalan fungsi mengikut peranan'),
    JSON_ARRAY('Kenal pasti masalah operasi sebenar','Reka bentuk aliran imbasan tanpa kelewatan','Bangunkan sistem log masuk dan peranan pengguna','Bangunkan kawalan sesi harian','Uji enjin imbasan QR','Uji perlindungan rekod pendua','Uji pada peranti fizikal','Bangunkan modul analitik warden'),
    CURRENT_TIMESTAMP
),
('spark','SPARK','SPARK','Aplikasi Mudah Alih','smart-campus-safety-operations','Sistem Pengurusan Asrama','Pengurusan keluar masuk asrama yang lebih selamat dan tersusun.',NULL,'Functional Prototype / Active Development','incomplete','draft',0,2,'project-card--mint',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
('durian-radar','Durian Radar','Durian Radar','Komuniti','sustainability-circular-economy',NULL,'Menemukan durian segar melalui peta komuniti masa nyata.',NULL,'Functional Prototype','incomplete','draft',0,3,'project-card--sky',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
('cms-quest','CMS Quest','CMS Quest','Pendidikan','community-education-wellbeing',NULL,'Pembelajaran pengurusan kandungan web melalui simulasi gamifikasi.',NULL,'Concept and Design Stage','incomplete','draft',0,4,'project-card--lilac',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
('careline-kvks','Careline KVKS','Careline KVKS','Sistem Web','smart-campus-safety-operations','Sistem Institusi','Saluran aduan digital yang lebih jelas, pantas dan terurus.',NULL,'Institutional System / Pilot Implementation','incomplete','draft',0,5,'project-card--coral',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
('visionlab','VisionLab','VisionLab','Kecerdasan Buatan','digital-systems-intelligence',NULL,NULL,NULL,'Unverified','unverified','archived',0,6,'project-card--cream',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL)
ON DUPLICATE KEY UPDATE
    name = VALUES(name), full_title = VALUES(full_title), category = VALUES(category), solution_area = VALUES(solution_area),
    project_type = VALUES(project_type), tagline = VALUES(tagline), short_description = VALUES(short_description),
    development_status = VALUES(development_status), verification_status = VALUES(verification_status),
    review_status = VALUES(review_status), is_featured = VALUES(is_featured), display_order = VALUES(display_order),
    accent_class = VALUES(accent_class), platform = VALUES(platform), target_users = VALUES(target_users),
    project_location = VALUES(project_location), method = VALUES(method), problem = VALUES(problem),
    problem_points = VALUES(problem_points), solution = VALUES(solution), solution_points = VALUES(solution_points),
    how_it_works = VALUES(how_it_works), key_features = VALUES(key_features), impact = VALUES(impact),
    impact_points = VALUES(impact_points), technology_stack = VALUES(technology_stack),
    technology_details = VALUES(technology_details), project_journey = VALUES(project_journey), published_at = VALUES(published_at);

