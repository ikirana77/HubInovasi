USE hubinovasi;

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
