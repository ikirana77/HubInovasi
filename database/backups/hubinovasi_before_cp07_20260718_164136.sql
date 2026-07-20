-- MySQL dump 10.13  Distrib 8.0.44, for macos12.7 (arm64)
--
-- Host: localhost    Database: hubinovasi
-- ------------------------------------------------------
-- Server version	8.0.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_login_attempts`
--

DROP TABLE IF EXISTS `admin_login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_login_attempts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `normalized_email_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT '0',
  `attempted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_login_attempt_window` (`normalized_email_hash`,`ip_hash`,`success`,`attempted_at`),
  KEY `idx_login_attempt_cleanup` (`attempted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_login_attempts`
--

LOCK TABLES `admin_login_attempts` WRITE;
/*!40000 ALTER TABLE `admin_login_attempts` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_login_attempts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_admin_users_active_email` (`is_active`,`email`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `people` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `profile_slug` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `person_type` enum('student','mentor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `programme_or_position` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` text COLLATE utf8mb4_unicode_ci,
  `skills` json DEFAULT NULL,
  `achievements` json DEFAULT NULL,
  `verification_status` enum('incomplete','unverified','verified') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'incomplete',
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `profile_slug` (`profile_slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `people`
--

LOCK TABLES `people` WRITE;
/*!40000 ALTER TABLE `people` DISABLE KEYS */;
/*!40000 ALTER TABLE `people` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_assets`
--

DROP TABLE IF EXISTS `project_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_assets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `asset_type` enum('project_logo','cover_image','application_screenshot','prototype_photograph','team_photograph','mentor_photograph','certificate','testing_evidence','competition_evidence') COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime_type` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caption` text COLLATE utf8mb4_unicode_ci,
  `verification_status` enum('unverified','verified','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unverified',
  `display_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_project_assets_public` (`project_id`,`verification_status`,`asset_type`),
  CONSTRAINT `fk_project_assets_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_assets`
--

LOCK TABLES `project_assets` WRITE;
/*!40000 ALTER TABLE `project_assets` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_evidence`
--

DROP TABLE IF EXISTS `project_evidence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_evidence` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `evidence_type` enum('pilot_result','user_feedback','testing_result','impact_metric','award','certificate','competition') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `metric_value` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metric_label` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `evidence_date` date DEFAULT NULL,
  `verification_status` enum('unverified','verified','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unverified',
  `asset_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_project_evidence_project` (`project_id`),
  KEY `fk_project_evidence_asset` (`asset_id`),
  CONSTRAINT `fk_project_evidence_asset` FOREIGN KEY (`asset_id`) REFERENCES `project_assets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_project_evidence_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_evidence`
--

LOCK TABLES `project_evidence` WRITE;
/*!40000 ALTER TABLE `project_evidence` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_evidence` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_links`
--

DROP TABLE IF EXISTS `project_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_links` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `link_type` enum('demo_url','video_url','repository_url','documentation_url','download_url','contact_url') COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_project_link_type` (`project_id`,`link_type`),
  CONSTRAINT `fk_project_links_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_links`
--

LOCK TABLES `project_links` WRITE;
/*!40000 ALTER TABLE `project_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_people`
--

DROP TABLE IF EXISTS `project_people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_people` (
  `project_id` bigint unsigned NOT NULL,
  `person_id` bigint unsigned NOT NULL,
  `relationship_type` enum('team','mentor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_title` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contribution` text COLLATE utf8mb4_unicode_ci,
  `display_order` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`project_id`,`person_id`,`relationship_type`),
  KEY `fk_project_people_person` (`person_id`),
  CONSTRAINT `fk_project_people_person` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_project_people_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_people`
--

LOCK TABLES `project_people` WRITE;
/*!40000 ALTER TABLE `project_people` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_people` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `solution_area` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project_type` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tagline` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_description` text COLLATE utf8mb4_unicode_ci,
  `development_status` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `verification_status` enum('incomplete','unverified','verified') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'incomplete',
  `review_status` enum('draft','pending_review','needs_revision','published','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `display_order` int unsigned NOT NULL DEFAULT '0',
  `accent_class` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_users` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `problem` text COLLATE utf8mb4_unicode_ci,
  `problem_points` json DEFAULT NULL,
  `solution` text COLLATE utf8mb4_unicode_ci,
  `solution_points` json DEFAULT NULL,
  `how_it_works` json DEFAULT NULL,
  `key_features` json DEFAULT NULL,
  `impact` text COLLATE utf8mb4_unicode_ci,
  `impact_points` json DEFAULT NULL,
  `technology_stack` json DEFAULT NULL,
  `technology_details` json DEFAULT NULL,
  `project_journey` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `published_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_projects_public` (`review_status`,`verification_status`,`is_featured`),
  KEY `idx_projects_development` (`development_status`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,'hers','HERS','HERS — Sistem Rekod Kehadiran Muslimah ke Tingkat Atas Surau','Aplikasi Mudah Alih','Kehidupan Kampus','Sistem Pengurusan Kehadiran Berasaskan Kod QR','Kehadiran yang lebih pantas, teratur dan bermakna.','Sistem kehadiran berasaskan kod QR yang membantu merekod pergerakan pelajar Muslimah ke tingkat atas Pusat Islam Raudhatul Jannah dengan aliran yang pantas, mudah dan tersusun.','Functional Pilot / Release Candidate','verified','published',1,1,'project-card--peach','Android','Admin, Warden dan BADAR','Pusat Islam Raudhatul Jannah','Imbasan QR dan Rekod Manual','Proses merekod kehadiran pelajar Muslimah ke tingkat atas surau memerlukan kaedah yang lebih pantas dan konsisten. Rekod manual mengambil masa, sukar disemak semula dan tidak memberikan gambaran jelas tentang corak kehadiran.','[\"Proses manual mengambil masa\", \"Rekod sukar disemak secara menyeluruh\", \"Corak ketidakhadiran sukar dikenal pasti\"]','HERS menggunakan kod QR untuk merekod kehadiran secara automatik. Pelajar menunjukkan kod QR, BADAR mengimbasnya dan rekod disimpan tanpa proses pengesahan tambahan.','[\"Imbas dan teruskan perjalanan\", \"Rekod automatik tanpa pengesahan berulang\", \"Rekod manual sebagai kaedah sokongan\", \"Perlindungan daripada imbasan pendua\", \"Data boleh disemak oleh pengguna yang dibenarkan\"]','[{\"text\": \"Sesi harian dibuka bermula pada pukul 6:30 petang.\", \"title\": \"BADAR Membuka Sesi\"}, {\"text\": \"Kod QR pelajar digunakan sebagai kad imbasan rasmi.\", \"title\": \"Pelajar Menunjukkan Kod QR\"}, {\"text\": \"Sistem mengesahkan pelajar dan merekod kehadiran secara automatik.\", \"title\": \"Kod Diimbas\"}, {\"text\": \"Bunyi, warna dan paparan status membantu mempercepatkan aliran.\", \"title\": \"Maklum Balas Segera\"}, {\"text\": \"Warden dan pentadbir boleh melihat rekod serta pola yang memerlukan semakan.\", \"title\": \"Rekod Boleh Disemak\"}]','[\"Fast Scan Camera\", \"Rekod Automatik\", \"Perlindungan Imbasan Pendua\", \"Rekod Manual Sandaran\", \"Sesi Harian Boleh Dibuka Semula\", \"Rekod Hari Ini\", \"Analitik Kehadiran\", \"Eksport CSV atau Excel\", \"Kawalan Akses Mengikut Peranan\", \"Sokongan Offline Queue\"]','HERS berpotensi mempercepatkan aliran pelajar, mengurangkan catatan manual dan menyediakan rekod yang lebih mudah dijejak. Tiada statistik penggunaan diterbitkan sehingga bukti disahkan.','[\"Mempercepatkan aliran pelajar di tangga atas surau\", \"Mengurangkan kebergantungan kepada catatan manual\", \"Menyediakan rekod yang lebih mudah dijejak\", \"Membantu warden mengenal pasti keadaan yang memerlukan semakan\", \"Menyokong keputusan berasaskan rekod, bukan andaian\"]','[\"Flutter\", \"Firebase\", \"QR Code\", \"Android\", \"Offline Queue\"]','[\"Flutter — pembangunan aplikasi Android\", \"Firebase Authentication — log masuk Google\", \"Cloud Firestore — penyimpanan sesi dan rekod\", \"QR Scanner — pengesanan identiti pelajar\", \"Local Offline Queue — simpanan sementara apabila sambungan terganggu\", \"Role-Based Access — kawalan fungsi mengikut peranan\"]','[\"Kenal pasti masalah operasi sebenar\", \"Reka bentuk aliran imbasan tanpa kelewatan\", \"Bangunkan sistem log masuk dan peranan pengguna\", \"Bangunkan kawalan sesi harian\", \"Uji enjin imbasan QR\", \"Uji perlindungan rekod pendua\", \"Uji pada peranti fizikal\", \"Bangunkan modul analitik warden\"]','2026-07-17 12:06:19','2026-07-17 12:06:19','2026-07-17 12:06:19'),(2,'spark','SPARK','SPARK','Aplikasi Mudah Alih','Kehidupan Kampus','Sistem Pengurusan Asrama','Pengurusan keluar masuk asrama yang lebih selamat dan tersusun.',NULL,'Functional Prototype / Active Development','incomplete','draft',0,2,'project-card--mint',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-17 12:06:19','2026-07-17 12:06:19',NULL),(3,'durian-radar','Durian Radar','Durian Radar','Komuniti','Komuniti & Kesejahteraan',NULL,'Menemukan durian segar melalui peta komuniti masa nyata.',NULL,'Functional Prototype','incomplete','draft',0,3,'project-card--sky',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-17 12:06:19','2026-07-17 12:06:19',NULL),(4,'cms-quest','CMS Quest','CMS Quest','Pendidikan','Pembelajaran Masa Hadapan',NULL,'Pembelajaran pengurusan kandungan web melalui simulasi gamifikasi.',NULL,'Concept and Design Stage','incomplete','draft',0,4,'project-card--lilac',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-17 12:06:19','2026-07-17 12:06:19',NULL),(5,'careline-kvks','Careline KVKS','Careline KVKS','Sistem Web','Kehidupan Kampus','Sistem Institusi','Saluran aduan digital yang lebih jelas, pantas dan terurus.',NULL,'Institutional System / Pilot Implementation','incomplete','draft',0,5,'project-card--coral',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-17 12:06:19','2026-07-17 12:06:19',NULL),(6,'visionlab','VisionLab','VisionLab','Kecerdasan Buatan','Pembelajaran Masa Hadapan',NULL,NULL,NULL,'Unverified','unverified','archived',0,6,'project-card--cream',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-17 12:06:19','2026-07-17 12:06:19',NULL);
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `submission_status_history`
--

DROP TABLE IF EXISTS `submission_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `submission_status_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `submission_id` bigint unsigned NOT NULL,
  `from_status` enum('draft','pending_review','needs_revision','published','archived') COLLATE utf8mb4_unicode_ci NOT NULL,
  `to_status` enum('draft','pending_review','needs_revision','published','archived') COLLATE utf8mb4_unicode_ci NOT NULL,
  `admin_user_id` bigint unsigned DEFAULT NULL,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_status_history_admin` (`admin_user_id`),
  KEY `idx_status_history_submission` (`submission_id`,`created_at`),
  CONSTRAINT `fk_status_history_admin` FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_status_history_submission` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `submission_status_history`
--

LOCK TABLES `submission_status_history` WRITE;
/*!40000 ALTER TABLE `submission_status_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `submission_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `submissions`
--

DROP TABLE IF EXISTS `submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `submissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `public_token` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `submitter_name` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `submitter_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project_name` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `institution` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `solution_area` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project_development_status` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tagline` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `problem` text COLLATE utf8mb4_unicode_ci,
  `solution` text COLLATE utf8mb4_unicode_ci,
  `how_it_works` text COLLATE utf8mb4_unicode_ci,
  `key_features` text COLLATE utf8mb4_unicode_ci,
  `impact` text COLLATE utf8mb4_unicode_ci,
  `evidence_status` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `evidence_summary` text COLLATE utf8mb4_unicode_ci,
  `technologies` text COLLATE utf8mb4_unicode_ci,
  `team_details` text COLLATE utf8mb4_unicode_ci,
  `project_journey` text COLLATE utf8mb4_unicode_ci,
  `call_to_action` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','pending_review','needs_revision','published','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `linked_project_id` bigint unsigned DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by_admin_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `public_token` (`public_token`),
  KEY `fk_submission_project` (`linked_project_id`),
  KEY `idx_submissions_status` (`status`,`updated_at`),
  KEY `idx_submissions_email` (`submitter_email`),
  KEY `fk_submissions_reviewer` (`reviewed_by_admin_id`),
  CONSTRAINT `fk_submission_project` FOREIGN KEY (`linked_project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_submissions_reviewer` FOREIGN KEY (`reviewed_by_admin_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `submissions`
--

LOCK TABLES `submissions` WRITE;
/*!40000 ALTER TABLE `submissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `submissions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-18 16:41:37
