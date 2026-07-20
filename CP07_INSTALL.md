# HubInovasi CP07 — Pemasangan Pantas

CP07 menambah akaun pelajar/pensyarah, kelulusan admin, dashboard Projek Saya dan pemilikan submission.

## 1. Salin patch

Ekstrak `hubinovasi_CP07_patch.zip` terus ke dalam folder projek `hubinovasi` dan pilih **Replace/Merge** apabila diminta. Patch tidak mengandungi `config/local.php` dan tidak mengubah kata laluan database tempatan.

## 2. Jalankan migration

Dari Terminal dalam folder `hubinovasi`:

```bash
/Applications/MAMP/Library/bin/mysql80/bin/mysql \
  --socket=/Applications/MAMP/tmp/mysql/mysql.sock \
  -uroot -p \
  -e "SOURCE database/migrations/004_cp07_user_accounts.sql;"
```

Masukkan kata laluan MySQL MAMP apabila diminta. Untuk konfigurasi MAMP standard, kata laluan lazimnya `root`.

## 3. Jalankan ujian CP07

```bash
/Applications/MAMP/bin/php/php8.3.30/bin/php scripts/test_cp07.php
```

Semua baris perlu memaparkan `[PASS]` dan transaksi ujian akan di-rollback.

## 4. Ujian browser

1. Buka `http://localhost:8888/hubinovasi/register.php`.
2. Daftar satu akaun pelajar atau pensyarah.
3. Buka `http://localhost:8888/hubinovasi/admin/login.php` dan login sebagai admin.
4. Pilih menu **Pengguna** dan klik **Luluskan Akaun**.
5. Login pengguna melalui `http://localhost:8888/hubinovasi/login.php`.
6. Cipta projek, simpan draf dan hantar untuk semakan.
7. Admin semak melalui menu **Submission**.

## Laluan baharu

- `register.php`
- `login.php`
- `logout.php`
- `dashboard/index.php`
- `dashboard/profile.php`
- `admin/users.php`

