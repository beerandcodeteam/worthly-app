## Phase 2 — Authentication

### 2.1 — API Client + Token Storage

- [ ] **2.1.1** Build `App\Services\Worthly\WorthlyApiClient` (Laravel HTTP macro) that injects `Authorization: Bearer <token>` from `SecureTokenStorage` and serializes/deserializes JSON.
  - **Tests:**
    - `tests/Feature/Services/Worthly/WorthlyApiClientTest.php` — `it injects bearer token on every protected call`.
    - `it omits Authorization header when no token is stored`.
    - `it parses {data: …} envelopes for resource endpoints`.
    - `it surfaces 401 / 404 / 422 / 502 as typed exceptions`.

### 2.2 — Register (US-1.2)

- [ ] **2.2.1** Livewire component `App\Livewire\Auth\Register` rendering name / email / password / confirmation fields and calling `POST /api/register`.
  - On `201`: persist token, hydrate `auth.user` cache, route to Home.
  - On `422`: render field errors.
  - **Tests:**
    - `tests/Feature/Livewire/Auth/RegisterTest.php` — `it submits valid payload, stores the token, and redirects to home`.
    - `it renders field-level errors for 422`.
    - `it never stores a token on validation failure`.
    - `it forwards name, email, password, and password_confirmation in the request body`.

### 2.3 — Login (US-1.3)

- [ ] **2.3.1** Livewire component `App\Livewire\Auth\Login` rendering email / password, calling `POST /api/login`.
  - **Tests:**
    - `tests/Feature/Livewire/Auth/LoginTest.php` — `it stores the token and redirects to home on 200`.
    - `it shows a generic message on 401 and stores no token`.
    - `it shows field-level errors on 422`.
    - `it disables the Forgot password link and SSO buttons in MVP`.

### 2.4 — Session Restore (US-1.4)

- [ ] **2.4.1** Middleware / boot hook that reads the stored token at app start, calls `GET /api/me`, and either routes to Home or clears the token and routes to Login.
  - **Tests:**
    - `tests/Feature/Auth/SessionRestoreTest.php` — `it routes to home when /api/me returns 200`.
    - `it wipes the token and routes to login when /api/me returns 401`.
    - `it does not call /api/me when no token is stored`.
    - `it never logs the token in plain text`.

### 2.5 — Logout (US-1.5)

- [ ] **2.5.1** `App\Livewire\Profile\SignOutAction` invokes `POST /api/logout`, clears local token + cached state, routes to Login.
  - **Tests:**
    - `tests/Feature/Livewire/Profile/SignOutTest.php` — `it shows a confirmation prompt before signing out`.
    - `it clears the local token on 204`.
    - `it still clears the local token on 401`.
    - `it clears cached recent analyses and profile on sign out`.

### 2.6 — Onboarding Carousel (US-1.1)

- [ ] **2.6.1** Livewire SFC `App\Livewire\Onboarding\Carousel` — 3 slides, swipeable, skip-to-end, Get-started / I-have-an-account CTAs. First-launch flag persisted on device.
  - **Tests:**
    - `tests/Feature/Livewire/Onboarding/CarouselTest.php` — `it renders three slides with the correct copy`.
    - `it routes Get started to Register and the secondary CTA to Login`.
    - `it sets the first-launch flag once the user reaches the last slide or skips`.
    - `it does not show onboarding again after the flag is set`.

---

