# CP05 Database Setup

Keperluan: MySQL 8, PHP PDO MySQL, dan pangkalan data yang boleh menggunakan `utf8mb4`.

Jalankan mengikut urutan:

```sh
/Applications/MAMP/Library/bin/mysql80/bin/mysql \
  --socket=/Applications/MAMP/tmp/mysql/mysql.sock \
  -uroot -p \
  -e "SOURCE database/migrations/001_cp05_schema.sql; SOURCE database/migrations/002_submission_development_status.sql; SOURCE database/seeds/001_cp05_projects.sql;"
```

Salin `config/local.example.php` kepada `config/local.php` untuk pembangunan tempatan. Fail `local.php` diabaikan Git. Gunakan environment berikut untuk deployment:

- `HUBINOVASI_DB_HOST`
- `HUBINOVASI_DB_PORT`
- `HUBINOVASI_DB_SOCKET`
- `HUBINOVASI_DB_NAME`
- `HUBINOVASI_DB_USER`
- `HUBINOVASI_DB_PASS`
- `HUBINOVASI_ADMIN_PASSWORD`

Tiada kata laluan database atau admin disimpan dalam repository.

Ujian penerimaan:

```sh
/Applications/MAMP/bin/php/php8.3.30/bin/php scripts/test_cp05.php
```
