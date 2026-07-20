# CP07.1 — Sign in with Google

## Included
- Google Identity Services button on login and registration pages.
- Server-side Google ID token verification with the official Google API Client for PHP.
- Google CSRF double-submit-cookie validation.
- New Google users choose Student or Lecturer and remain Pending until admin approval.
- Existing accounts with the same verified email are linked automatically.
- Stable Google `sub` is stored as the account identifier.
- Password login remains available as fallback.

## Setup
1. Run migration `database/migrations/005_cp071_google_signin.sql`.
2. Add your Google account under Google Auth Platform → Audience → Test users while the app is in Testing.
3. Authorized JavaScript origin: `http://localhost:8888`.
4. Authorized redirect URI: `http://localhost:8888/hubinovasi/auth/google-login.php`.
5. Run `scripts/test_cp071.php`.

Composer is optional. If `vendor/autoload.php` exists, HubInovasi uses the official Google API Client. Otherwise it securely validates the RS256 token with Google public certificates, including `aud`, `iss`, `exp`, signature, and CSRF checks.

The Client ID is not a secret. Never place the OAuth Client Secret or downloaded credential JSON in the project.
