# HubInovasi CP07.2 — Launchpad Homepage

Checkpoint ini membina semula halaman utama mengikut mockup Startup Pitch Launchpad yang diluluskan.

## Fail terlibat

- `index.php`
- `includes/header.php`
- `includes/footer.php`
- `assets/css/home-launchpad.css`
- `assets/images/home/*`

## Pemasangan

Salin semua kandungan patch ke root projek HubInovasi. Tiada migration database diperlukan.

Selepas pemasangan, buka:

`http://localhost:8888/hubinovasi/`

Lakukan hard refresh jika CSS lama masih kelihatan:

- macOS Safari/Chrome: `Command + Shift + R`

## Nota kandungan

Nama projek, statistik dan profil pada halaman utama ialah kandungan showcase yang mengikuti mockup. Pautan projek yang belum diterbitkan akan membawa pengguna ke `explore.php` sehingga projek tersebut berstatus `published` dan `verified` dalam database.
