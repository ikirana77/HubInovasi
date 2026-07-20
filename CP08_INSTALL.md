# HubInovasi CP08 — Revamped Public Pages

CP08 melaksanakan rupa baharu lapan halaman awam berdasarkan mockup yang diluluskan:

1. Home
2. Discover Solutions
3. Project Detail
4. Solution Areas
5. Competitions & Impact
6. Innovator Profile
7. Mentor Profile
8. About HubInovasi

## Pemasangan pada MAMP (macOS)

1. Tutup tab HubInovasi dalam pelayar.
2. Buat salinan keselamatan folder semasa:

   ```bash
   cp -R /Applications/MAMP/htdocs/hubinovasi ~/Desktop/hubinovasi_before_CP08
   ```

3. Nyahzip pakej CP08 yang diterima.
4. Gantikan folder `/Applications/MAMP/htdocs/hubinovasi` dengan folder `hubinovasi` daripada pakej CP08.
5. Pastikan Apache dan MySQL dalam MAMP sedang berjalan.
6. Buka `http://localhost:8888/hubinovasi/`.

Pangkalan data sedia ada tidak diubah oleh CP08. Konfigurasi login, penghantaran projek, dashboard dan panel admin turut dikekalkan.

## Semakan pantas

- Uji menu pada desktop dan telefon.
- Uji BM/EN.
- Buka sekurang-kurangnya satu projek daripada Discover Solutions.
- Pastikan login, Submit Project dan Admin masih boleh dibuka.
