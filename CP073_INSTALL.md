# CP07.3 — Bilingual BM/English Public Website

## Scope
- Bahasa Malaysia is the default language.
- Visitors can switch between BM and English from the global header.
- The selection is stored in the session and a one-year cookie.
- Homepage, public pages, project pitch, registration, login and submission form are bilingual.
- Administrator pages remain in BM by design.
- Existing URLs, database schema, Google Sign-In and submission workflow are preserved.

## Installation
Copy this patch into the HubInovasi root folder. No database migration is required.

## Test
Run:

```bash
php scripts/test_cp073.php
```

Then visit:

- `http://localhost:8888/hubinovasi/` — defaults to BM
- `http://localhost:8888/hubinovasi/?lang=en` — switches to English

## Dynamic project translations
The current verified HERS project includes English copy. Future projects safely fall back to BM until their verified English copy is added to `lang/en.php` or a later bilingual CMS module.
