USE hubinovasi;

INSERT INTO projects (
    slug, name, full_title, category, solution_area, project_type, tagline, short_description,
    development_status, verification_status, review_status, is_featured, display_order, accent_class,
    platform, target_users, project_location, method, problem, problem_points, solution, solution_points,
    how_it_works, key_features, impact, impact_points, technology_stack, technology_details, project_journey, published_at
) VALUES (
    'hers', 'HERS', 'HERS — Sistem Rekod Kehadiran Muslimah ke Tingkat Atas Surau',
    'Aplikasi Mudah Alih', 'Kehidupan Kampus', 'Sistem Pengurusan Kehadiran Berasaskan Kod QR',
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
('spark','SPARK','SPARK','Aplikasi Mudah Alih','Kehidupan Kampus','Sistem Pengurusan Asrama','Pengurusan keluar masuk asrama yang lebih selamat dan tersusun.',NULL,'Functional Prototype / Active Development','incomplete','draft',0,2,'project-card--mint',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
('durian-radar','Durian Radar','Durian Radar','Komuniti','Komuniti & Kesejahteraan',NULL,'Menemukan durian segar melalui peta komuniti masa nyata.',NULL,'Functional Prototype','incomplete','draft',0,3,'project-card--sky',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
('cms-quest','CMS Quest','CMS Quest','Pendidikan','Pembelajaran Masa Hadapan',NULL,'Pembelajaran pengurusan kandungan web melalui simulasi gamifikasi.',NULL,'Concept and Design Stage','incomplete','draft',0,4,'project-card--lilac',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
('careline-kvks','Careline KVKS','Careline KVKS','Sistem Web','Kehidupan Kampus','Sistem Institusi','Saluran aduan digital yang lebih jelas, pantas dan terurus.',NULL,'Institutional System / Pilot Implementation','incomplete','draft',0,5,'project-card--coral',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),
('visionlab','VisionLab','VisionLab','Kecerdasan Buatan','Pembelajaran Masa Hadapan',NULL,NULL,NULL,'Unverified','unverified','archived',0,6,'project-card--cream',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL)
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
