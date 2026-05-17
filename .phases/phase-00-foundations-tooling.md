## Phase 0 — Foundations & Tooling

- [ ] **0.1** Add API + auth configuration
  - Add `config/services.php` block for `worthly` (`base_url`, `timeout`, `image_max_kb`).
  - Add `.env.example` entries (`WORTHLY_API_URL`, `WORTHLY_API_TIMEOUT`).
  - **Tests:** _none — configuration only._
- [ ] **0.2** Define `App\Support\Verdict` enum (`Buy`, `Wait`, `Skip`) with mapping from API decision strings (`buy`, `buy_if_price_is_good`, `wait`, `consider_alternatives`, `do_not_buy`).
  - **Tests:**
    - `tests/Unit/Support/VerdictTest.php` — `it maps every API decision to the correct verdict bucket` (dataset of all 5 decisions).
    - `it exposes color, label, and code tokens for each bucket`.
- [ ] **0.3** Define secure-storage contract `App\Contracts\SecureTokenStorage` with a NativePHP-backed implementation and an in-memory fake for tests.
  - **Tests:**
    - `tests/Unit/Storage/SecureTokenStorageTest.php` — `it stores, reads, and forgets a token` against the in-memory fake.
    - `it never returns a token after forget()`.
- [ ] **0.4** Add Pest base helpers: `fakeWorthlyApi()`, `actingAsWorthlyUser()`, `worthlyAnalysisPayload()` factory-style helpers in `tests/Pest.php`.
  - **Tests:** _none — covered indirectly by every later feature test._

---

