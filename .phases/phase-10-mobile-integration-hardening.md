## Phase 10 — Mobile Integration & Hardening

- [ ] **10.1** NativePHP bootstrapping
  - Confirm `php artisan native:install` has been run, SQLite + storage paths are set, and the app entry view points at the onboarding/login decision logic.
  - **Tests:** `tests/Feature/NativeBoot/AppBootTest.php` — `it boots into onboarding when no first-launch flag is set` and `it boots into home when a valid token is restored`.
- [ ] **10.2** Camera + file picker integration via the NativePHP bridge (used by Phase 4.2).
  - **Tests:** `tests/Browser/Composer/ImagePickerSmokeTest.php` (Pest 4 Browser) — `it opens the picker, selects an image, shows a thumbnail, and submits` (driven against a fake bridge in the test bench).
- [ ] **10.3** Secure storage wired to the native keychain (iOS) / EncryptedSharedPreferences (Android).
  - **Tests:** `tests/Feature/Storage/NativeSecureStorageTest.php` — `it round-trips a token through the native bridge fake`.
- [ ] **10.4** Architecture / smoke pass with `pest --arch`:
  - **Tests:** `tests/Arch/WorthlyArchTest.php` — `Livewire components are final and live under App\Livewire`, `Services\Worthly classes are not allowed to reference Eloquent`, `Enums are final`.
- [ ] **10.5** Full-suite green run: `php artisan test --compact` must pass on CI before any release build.
  - **Tests:** _N/A — gate, not new code._

---

## Appendix A — Story → Phase coverage

| Story | Phase | Story | Phase |
|---|---|---|---|
| US-1.1 | 2.6 | US-6.1 | 5.6 |
| US-1.2 | 2.2 | US-7.1 | 5.7 |
| US-1.3 | 2.3 | US-8.1 | 6.1 |
| US-1.4 | 2.4 | US-9.1 | 7.1 |
| US-1.5 | 2.5 | US-9.2 | 7.2 |
| US-2.1 | 3.1 | US-9.3 | 7.3 |
| US-2.2 | 3.2 | US-9.4 | 7.4 |
| US-2.3 | 3.3 | US-9.5 | 7.5 |
| US-3.1 | 4.1 | US-10.1 | 8.1 |
| US-3.2 | 4.2 | US-10.2 | 8.2 |
| US-3.3 | 4.3 | US-11.1 | 9.1 |
| US-4.1 | 5.1 | US-11.2 | 9.2 |
| US-4.2 | 5.2 | US-11.3 | 9.3 |
| US-4.3 | 5.3 | US-11.4 | 9.4 |
| US-4.4 | 5.4 | US-11.5 | 9.5 |
| US-5.1 | 5.5 | | |

## Appendix B — Snapshot of what already exists

A scan of the current codebase shows a near-bare Laravel 13 skeleton — Livewire 4, NativePHP Mobile, Pest 4 and Tailwind 4 are installed; the default `User` model + migrations exist; `routes/web.php` only renders the stock `welcome` view. No Worthly-specific configuration, services, components, screens, layouts, or tests are present yet, so every task above is currently `[ ]`. Re-run this check at the start of each phase and tick the boxes as the work lands.
