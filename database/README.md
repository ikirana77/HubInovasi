# HubInovasi Database & Admin Setup

Keperluan: MySQL 8, PHP PDO MySQL dan `utf8mb4`.

## Migration

Pemasangan baharu (schema CP05 semasa sudah mengandungi medan development status):

```sh
/Applications/MAMP/Library/bin/mysql80/bin/mysql \
  --socket=/Applications/MAMP/tmp/mysql/mysql.sock \
  -uroot -p \
  -e "SOURCE database/migrations/001_cp05_schema.sql; SOURCE database/seeds/001_cp05_projects.sql; SOURCE database/migrations/003_cp06_admin_review.sql; SOURCE database/migrations/004_cp07_user_accounts.sql; SOURCE database/migrations/006_cp09_taxonomy_programmes.sql; SOURCE database/migrations/007_cp09b_hers_verified_content.sql; SOURCE database/migrations/008_cp09c_durian_radar_verified_content.sql;"
```

Naik taraf database CP05 yang sudah berjalan:

```sh
/Applications/MAMP/Library/bin/mysql80/bin/mysql \
  --socket=/Applications/MAMP/tmp/mysql/mysql.sock \
  -uroot -p \
  -e "SOURCE database/migrations/003_cp06_admin_review.sql;"
```

Migration CP06 selamat dijalankan semula dan tidak mencipta akaun atau kata laluan.

## Konfigurasi

Salin `config/local.example.php` kepada `config/local.php` untuk pembangunan tempatan. `config/local.php` diabaikan Git. Production hendaklah menggunakan environment:

- `HUBINOVASI_DB_HOST`
- `HUBINOVASI_DB_PORT`
- `HUBINOVASI_DB_SOCKET`
- `HUBINOVASI_DB_NAME`
- `HUBINOVASI_DB_USER`
- `HUBINOVASI_DB_PASS`

Jangan commit `config/local.php`, dump database, email admin atau kredensial.

## Mencipta Admin

Jalankan selepas migration CP06:

```sh
/Applications/MAMP/bin/php/php8.3.30/bin/php scripts/create_admin.php
```

Script meminta nama, email, kata laluan minimum 12 aksara dan pengesahan. Pada terminal Unix, input kata laluan disembunyikan menggunakan `stty`. Jika `stty` atau `shell_exec` tidak tersedia, input mungkin kelihatan; jalankan script hanya dalam terminal peribadi dan bersihkan sejarah terminal jika perlu. Kata laluan disimpan menggunakan `password_hash()` dan tidak dicetak atau direkodkan.

Login melalui `/hubinovasi/admin/login.php` menggunakan email dan kata laluan admin.

## Polisi Keselamatan CP06

- Cookie session: HttpOnly, SameSite=Lax dan Secure apabila HTTPS.
- Timeout tidak aktif: 30 minit.
- Had mutlak session: 8 jam.
- Tiada Remember Me.
- Lima kegagalan login bagi hash email+IP dalam 15 minit mengaktifkan sekatan sementara.
- IP dan email mentah tidak disimpan dalam log percubaan login.
- Rekod login melebihi 30 hari dibersihkan semasa percubaan login; kegagalan bagi identifier yang sama dipadam selepas login berjaya.
- Logout hanya menerima POST dengan CSRF.

Transition submission:

```text
Penghantar: draft → pending_review
Penghantar: needs_revision → pending_review
Admin: pending_review → needs_revision | published | archived
Admin: published → archived
```

Nota admin diwajibkan untuk `needs_revision` dan `archived`. Publication menggunakan transaction, memerlukan kandungan lengkap dan mengemas kini `linked_project_id` sedia ada tanpa menduplikasi projek. Semua transition admin direkodkan dalam `submission_status_history`.

## Ujian

```sh
/Applications/MAMP/bin/php/php8.3.30/bin/php scripts/test_cp05.php
/Applications/MAMP/bin/php/php8.3.30/bin/php scripts/test_cp06.php
```

Kedua-dua script menggunakan transaction dan rollback supaya data ujian tidak kekal.

## CP07 — Akaun Pengguna & Pemilikan Projek

Jalankan selepas CP06:

```sh
/Applications/MAMP/Library/bin/mysql80/bin/mysql \
  --socket=/Applications/MAMP/tmp/mysql/mysql.sock \
  -uroot -p \
  -e "SOURCE database/migrations/004_cp07_user_accounts.sql;"
```

CP07 menambah:

- akaun `student` dan `lecturer`;
- status akaun `pending`, `active` dan `suspended`;
- kelulusan akaun oleh admin;
- dashboard `My Projects`;
- hubungan `submissions.owner_user_id`;
- kawalan supaya pengguna hanya boleh menyunting projek sendiri;
- nota pembetulan admin pada dashboard pengguna.

Laluan utama:

- `/hubinovasi/register.php`
- `/hubinovasi/login.php`
- `/hubinovasi/dashboard/index.php`
- `/hubinovasi/dashboard/profile.php`
- `/hubinovasi/admin/users.php`

Aliran CP07:

```text
Pengguna daftar → pending → admin aktifkan → pengguna login
→ cipta/simpan draf → hantar semakan → admin minta pembetulan atau terbit
```

Ujian selepas migration:

```sh
/Applications/MAMP/bin/php/php8.3.30/bin/php scripts/test_cp07.php
```

## CP09 — Taksonomi Inovasi & Kolaborasi Tujuh Program

Jalankan selepas CP07/CP08:

```sh
/Applications/MAMP/Library/bin/mysql80/bin/mysql \
  --socket=/Applications/MAMP/tmp/mysql/mysql.sock \
  -uroot -proot \
  < database/migrations/006_cp09_taxonomy_programmes.sql
```

CP09 menambah lapan bidang penyelesaian, tujuh program KVKS, jenis inovasi dan hubungan banyak-ke-banyak antara projek dengan program penyumbang.

CP09B mengesahkan HERS sebagai projek KPD dengan BADAR sebagai pemilik masalah dan rakan operasi. Jalankan selepas CP09A:

```sh
/Applications/MAMP/Library/bin/mysql80/bin/mysql \
  --socket=/Applications/MAMP/tmp/mysql/mysql.sock \
  -uroot -proot \
  < database/migrations/007_cp09b_hers_verified_content.sql
```

CP09C menerbitkan kandungan Durian Radar yang disahkan daripada laporan projek. Jalankan selepas CP09A:

```sh
/Applications/MAMP/Library/bin/mysql80/bin/mysql \
  --socket=/Applications/MAMP/tmp/mysql/mysql.sock \
  -uroot -proot \
  < database/migrations/008_cp09c_durian_radar_verified_content.sql
```


## CP07.1 Google Sign-In
Run `005_cp071_google_signin.sql` after CP07. The migration makes password optional for Google-only users and adds Google identity fields/indexes.
