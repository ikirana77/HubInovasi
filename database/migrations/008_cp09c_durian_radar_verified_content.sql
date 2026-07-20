USE hubinovasi;

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
