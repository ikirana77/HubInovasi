USE hubinovasi;

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
