# Worthly App — Project Phases

> Build plan for the **Worthly App** (Laravel 13 + Livewire 4 + NativePHP Mobile). Phases are numbered so they can be referenced individually (e.g. "implement Phase 3.2"). Each task lists the automated **Pest** feature tests that must accompany it. Status legend: `[ ]` = pending, `[x]` = already implemented in the codebase.
>
> Sources of truth: [`docs/project-description.md`](project-description.md), [`docs/user-stories.md`](user-stories.md), [`docs/worthly-handoff/`](worthly-handoff/), Worthly API OpenAPI (`/api/openapi.yaml`).
>
> **Test conventions (apply to every test bullet)**
> - Pest 4, feature tests under `tests/Feature/...` unless explicitly marked `Browser` or `Unit`.
> - API calls are faked with `Http::fake()` against `worthly-api` endpoints.
> - Livewire component tests use `Livewire::test(...)`.
> - Token storage tests use a swappable secure-storage contract so a fake driver can assert reads/writes.
>
> **General assumption:** Worthly API base URL is configured via `config('services.worthly.base_url')` and a Sanctum bearer token is the only auth artifact stored on device.

---

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

## Phase 1 — Frontend Foundations & Design System (no tests required)

> Pure UI/CSS scaffolding work derived from `docs/worthly-handoff/`. **No automated tests in this phase per the user's directive.**

- [ ] **1.1** Install + configure the design tokens in `resources/css/app.css`:
  - Color CSS vars from `worthly.html` (`--w-cream`, `--w-ink`, `--w-buy`, `--w-wait`, `--w-skip`, `--w-line`, soft variants, etc.).
  - Typography: load **Geist**, **Geist Mono**, **Instrument Serif** via `bunny()` plugin in `vite.config.js`, expose `--font-ui`, `--font-mono`, `--font-display`.
  - Tailwind 4 `@theme` extension with the Worthly palette so utility classes (e.g. `bg-w-buy`, `text-w-ink`) work.
- [ ] **1.2** Build base UI primitive Blade/Livewire components under `resources/views/components/ui/`:
  - `<x-ui.button>` — variants `ink`, `paper`, `buy`, sizes, disabled state, full-width (matches `PrimaryButton`).
  - `<x-ui.input>` — text input with label, error slot, hint, leading/trailing icons.
  - `<x-ui.textarea>` — multi-line composer with char-count slot.
  - `<x-ui.select>` — styled native `<select>` with chevron icon.
  - `<x-ui.checkbox>` — labeled checkbox.
  - `<x-ui.radio>` and `<x-ui.radio-group>`.
  - `<x-ui.modal>` — sheet/modal wrapper with header, body, footer slots.
  - `<x-ui.card>` — paper surface (`Card` primitive in `worthly-ui.jsx`).
  - `<x-ui.hairline>` and `<x-ui.section-label>`.
  - `<x-ui.verdict-pill>` — `Buy / Wait / Skip` pill with size variants.
  - `<x-ui.icon name="…">` — wraps SVG sprite generated from `worthly-ui.jsx` `Icon` set.
  - `<x-ui.product-image>` — abstract gradient placeholder (mirrors `ProductImage`).
  - `<x-ui.tab-bar>` — bottom nav (Home / History / Profile).
  - `<x-ui.screen-header>` — back chevron + title + close affordance.
- [ ] **1.3** Build base layouts:
  - `resources/views/components/layouts/guest.blade.php` — for **unauthenticated** screens (Onboarding, Login, Register). Cream background, no tab bar.
  - `resources/views/components/layouts/app.blade.php` — for **authenticated** screens. Holds the bottom tab bar, scroll container, and reuses `<x-ui.screen-header>`.
  - Both layouts include the design-token CSS and a viewport meta optimized for mobile.
- [ ] **1.4** Create a static **Style Guide** route (`/_dev/ui-kit`) — visible only in `local` env — that renders every primitive on one page so a designer can eyeball regressions.
- [ ] **1.5** Wire Vite manifest entry and run `npm run build` so primitives are usable from any view.

---

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

## Phase 3 — Home Screen

### 3.1 — Home Shell (US-2.1)

- [ ] **3.1.1** Livewire page `App\Livewire\Home\HomePage` rendering greeting (`Hi, {first_name}`), composer placeholder, suggestion chips, recent-analyses list, plan-usage indicator, tab bar.
  - **Tests:**
    - `tests/Feature/Livewire/Home/HomePageTest.php` — `it greets the user with the cached first name`.
    - `it lists up to 3 recent analyses from /api/analyses page 1`.
    - `it renders 3 to 5 suggestion chips`.
    - `it renders the FREE plan usage indicator`.
    - `it requires authentication`.

### 3.2 — Reopen Recent Analysis (US-2.2)

- [ ] **3.2.1** Recent-card click → `GET /api/analyses/{id}` → route to Result screen.
  - **Tests:**
    - `it shows a loading state then routes to Result on 200`.
    - `it renders an "Analysis no longer available" toast on 404 and stays on Home`.
    - `it triggers the global 401 handler on 401`.

### 3.3 — Suggestion Chips (US-2.3)

- [ ] **3.3.1** Tapping a suggestion chip prefills the composer; **Ask** stays disabled until composer is non-empty.
  - **Tests:**
    - `it prefills the composer text input when a chip is tapped`.
    - `it enables the Ask CTA once the composer has content`.
    - `it does not auto-submit the suggestion`.

---

## Phase 4 — Product Analysis Submission

### 4.1 — Text Submission (US-3.1)

- [ ] **4.1.1** Livewire `App\Livewire\Analyze\Composer` submits `POST /api/analyses` with `{input_type: "text", query}` (max 1000 chars).
  - **Tests:**
    - `tests/Feature/Livewire/Analyze/ComposerTextTest.php` — `it submits a text analysis and lands on the Result screen with the returned data`.
    - `it caps the input at 1000 characters`.
    - `it renders field-level errors on 422`.
    - `it shows the 502 error screen on upstream failure and keeps the input`.
    - `it triggers the global 401 handler on 401`.

### 4.2 — Image Submission (US-3.2)

- [ ] **4.2.1** Composer image picker (NativePHP camera/file picker), preview thumbnail, remove (✕), `multipart/form-data` upload to `POST /api/analyses`.
  - **Tests:**
    - `tests/Feature/Livewire/Analyze/ComposerImageTest.php` — `it accepts jpeg, png, and webp under 8 MB`.
    - `it rejects unsupported MIME types client-side`.
    - `it rejects images larger than 8 MB client-side without hitting the API`.
    - `it sends multipart/form-data with input_type=image and the file part named image`.
    - `it renders the 422 errors.image message inline next to the thumbnail`.
    - `it disables Ask (or omits the query) when both text and image are present`.

### 4.3 — Analyzing Loader (US-3.3)

- [ ] **4.3.1** Full-screen loader cycling through the 5 labeled steps, echoing the user input, no cancel CTA in MVP, jumps to the last step if the request resolves early.
  - **Tests:**
    - `tests/Feature/Livewire/Analyze/LoadingStateTest.php` — `it replaces the composer with the loader while the request is in flight`.
    - `it cycles through the five labeled steps`.
    - `it echoes the text query or the image thumbnail`.
    - `it transitions to the Result screen as soon as the API resolves`.
    - `it does not expose a cancel action`.

---

## Phase 5 — Result Screen & Drill-Ins

### 5.1 — Verdict Hero (US-4.1)

- [ ] **5.1.1** Livewire `App\Livewire\Result\ResultPage` hero card: verdict pill (bucket color), TLDR, product name / category / estimated price range, recommendation reason.
  - **Tests:**
    - `tests/Feature/Livewire/Result/HeroTest.php` — `it maps every API decision to the correct verdict bucket` (dataset).
    - `it renders price-conditional secondary copy for buy_if_price_is_good`.
    - `it hides product.category and estimated_price_range when null`.
    - `it places the hero card above all other content`.

### 5.2 — Summary & Reasons (US-4.2)

- [ ] **5.2.1** "Advisor summary" + "Why" card (Reasons for / Reasons against) split heuristically client-side from `cost_benefit_analysis`.
  - **Tests:**
    - `tests/Unit/Support/ProsConsSplitterTest.php` — `it splits sentences into pros and cons based on connector keywords`.
    - `it returns a single paragraph fallback when splitting fails`.
    - `tests/Feature/Livewire/Result/SummaryTest.php` — `it hides the Advisor summary when summary is null`.
    - `it hides the Why card when cost_benefit_analysis is null`.

### 5.3 — Price Right Now (US-4.3)

- [ ] **5.3.1** Card showing estimated price range as headline, static caption, and price-band visualization.
  - **Tests:**
    - `tests/Feature/Livewire/Result/PriceCardTest.php` — `it renders the estimated price range as the headline`.
    - `it hides the entire card when estimated_price_range is null`.
    - `it does not render a live-price marker (post-MVP)`.

### 5.4 — Bottom CTAs (US-4.4)

- [ ] **5.4.1** Pinned **New analysis** + **See best offer** CTAs.
  - **Tests:**
    - `it routes New analysis to Home with a cleared composer`.
    - `it routes See best offer to the Offers drill-in even with no offers data`.

### 5.5 — Similar Products Drill-In (US-5.1)

- [ ] **5.5.1** Drill-in row + `App\Livewire\Result\SimilarPage` listing `similar_products[]` (name, reason, price_reference) and a two-column comparison table.
  - **Tests:**
    - `tests/Feature/Livewire/Result/SimilarPageTest.php` — `it lists every similar product with name, reason, and price reference`.
    - `it falls back to em-dash when price_reference is null`.
    - `it hides the drill-in row on the Result screen when similar_products is empty`.
    - `it caps the list at 5 items per the API contract`.

### 5.6 — Reviews Drill-In (Derived) (US-6.1)

- [ ] **5.6.1** `App\Livewire\Result\ReviewsPage` renders reputation summary, "What Worthly considered" (cost_benefit_analysis), reuses the pros/cons splitter under Top pros / Top cons.
  - **Tests:**
    - `tests/Feature/Livewire/Result/ReviewsPageTest.php` — `it reuses summary and cost_benefit_analysis from the analysis`.
    - `it never renders aggregate rating, review count, sentiment %, or sources`.
    - `it hides the drill-in row when both summary and cost_benefit_analysis are null`.

### 5.7 — Offers Drill-In (Derived) (US-7.1)

- [ ] **5.7.1** `App\Livewire\Result\OffersPage` renders the price-reference callout, recommendation.reason framed as price guidance, "Alternatives by price" list from `similar_products[]` sorted by `price_reference`.
  - **Tests:**
    - `tests/Feature/Livewire/Result/OffersPageTest.php` — `it renders estimated_price_range as the price reference`.
    - `it sorts alternatives by price_reference when present`.
    - `it never renders retailer list, sparkline, or stock badges`.
    - `it hides the drill-in row when both price reference and similar_products are missing`.

---

## Phase 6 — Image Analyses

### 6.1 — Show Original Image (US-8.1)

- [ ] **6.1.1** Result screen fetches `GET /api/analyses/{id}/image` (bearer-token authenticated) for `input_type=image`, renders with skeleton placeholder and graceful 404 fallback.
  - **Tests:**
    - `tests/Feature/Livewire/Result/ImageRenderingTest.php` — `it fetches the image with the bearer token for image analyses`.
    - `it never calls the image endpoint for text analyses`.
    - `it shows an "Image unavailable" placeholder on 404`.
    - `it triggers the global 401 handler on 401`.
    - `it renders a same-aspect-ratio skeleton while loading`.

---

## Phase 7 — Analysis History

### 7.1 — Paginated History (US-9.1)

- [ ] **7.1.1** Livewire page `App\Livewire\History\HistoryPage` calls `GET /api/analyses?page={n}`, infinite scrolls (`links.next`), pull-to-refresh, day-grouping (Today / Yesterday / This week / Earlier).
  - **Tests:**
    - `tests/Feature/Livewire/History/HistoryPageTest.php` — `it fetches page 1 on load and renders every analysis row`.
    - `it loads the next page when the user reaches the end`.
    - `it groups rows by Today / Yesterday / This week / Earlier`.
    - `it resets to page 1 on pull-to-refresh`.
    - `it triggers the global 401 handler on 401`.

### 7.2 — Filter by Verdict (US-9.2)

- [ ] **7.2.1** Client-side filter chips (All / Buy / Wait / Skip).
  - **Tests:**
    - `it filters the loaded rows by verdict bucket`.
    - `it shows an empty-state message when a filter yields zero results`.
    - `it clears the filter when the user leaves the tab`.

### 7.3 — Reopen Historical Analysis (US-9.3)

- [ ] **7.3.1** Row tap → `GET /api/analyses/{id}` → Result screen; row data is reused as hero skeleton; 404 removes the row + toast.
  - **Tests:**
    - `tests/Feature/Livewire/History/ReopenAnalysisTest.php` — `it routes to Result and renders the hero from cached row data while loading`.
    - `it removes the row and shows a toast on 404`.

### 7.4 — Delete Analysis (US-9.4)

- [ ] **7.4.1** Swipe-to-delete (iOS) / long-press menu (Android) calling `DELETE /api/analyses/{id}` with confirmation.
  - **Tests:**
    - `tests/Feature/Livewire/History/DeleteAnalysisTest.php` — `it shows a confirmation before deleting`.
    - `it removes the row on 204 without re-fetching`.
    - `it removes the row on 404 with a toast`.
    - `it triggers the global 401 handler on 401`.

### 7.5 — Empty State (US-9.5)

- [ ] **7.5.1** Empty-state copy + CTA to Home composer.
  - **Tests:**
    - `tests/Feature/Livewire/History/EmptyStateTest.php` — `it renders the empty state when /api/analyses returns no data`.
    - `it does not flicker the empty state while page 1 is loading`.
    - `it routes the CTA to the Home composer`.

---

## Phase 8 — Profile

### 8.1 — Profile View (US-10.1)

- [ ] **8.1.1** Livewire `App\Livewire\Profile\ProfilePage` calls `GET /api/me` on first display and pull-to-refresh.
  - **Tests:**
    - `tests/Feature/Livewire/Profile/ProfilePageTest.php` — `it renders name, email, and an avatar initial`.
    - `it re-fetches /api/me on pull-to-refresh`.
    - `it triggers the global 401 handler on 401`.
    - `it caches the response for the session`.

### 8.2 — Usage Stats & Plan Card (US-10.2)

- [ ] **8.2.1** Static FREE plan card; counters: Total analyses (from `meta.total`), Saved products (`0`), Money saved (`—`); Upgrade CTA disabled.
  - **Tests:**
    - `tests/Feature/Livewire/Profile/UsageStatsTest.php` — `it shows Total analyses from meta.total of /api/analyses page 1`.
    - `it shows 0 for Saved products and em-dash for Money saved`.
    - `it disables the Upgrade CTA with the Coming soon tooltip`.
    - `it never blocks a submission based on the usage indicator`.

---

## Phase 9 — Cross-Cutting Error Handling

### 9.1 — Global 401 Handler (US-11.1)

- [ ] **9.1.1** Single response interceptor clears token + caches and routes to Login with a "Session expired" toast.
  - **Tests:**
    - `tests/Feature/ErrorHandling/Global401Test.php` — `it clears the token, caches, and in-flight state on any 401`.
    - `it shows the Session expired toast once`.
    - `it never auto-retries the original request`.

### 9.2 — 422 Validation Surface (US-11.2)

- [ ] **9.2.1** Parser maps `errors.{field}` to inline messages; unknown fields fall back to a toast with `message`.
  - **Tests:**
    - `tests/Feature/ErrorHandling/ValidationErrorTest.php` — `it renders the first message for each known field inline`.
    - `it surfaces the top-level message as a toast for unknown fields`.
    - `it does not auto-retry on 422`.

### 9.3 — 404 Contextual Handlers (US-11.3)

- [ ] **9.3.1** Per-endpoint contextual behavior (row removal + toast for `GET /analyses/{id}`; image placeholder for image endpoint; silent success for `DELETE`).
  - **Tests:**
    - `tests/Feature/ErrorHandling/NotFoundHandlingTest.php` — `it removes the row and toasts on GET /analyses/{id} 404`.
    - `it renders the Image unavailable placeholder on GET /analyses/{id}/image 404`.
    - `it treats DELETE /analyses/{id} 404 as success`.
    - `it never surfaces the raw 404 status to the user`.

### 9.4 — 502 Upstream Failure Surface (US-11.4)

- [ ] **9.4.1** Dedicated error sheet on `POST /api/analyses` failures with **Try again** (re-submits same payload) + **Back** (returns to composer with preserved input).
  - **Tests:**
    - `tests/Feature/ErrorHandling/UpstreamFailureTest.php` — `it shows the upstream failure sheet on 502`.
    - `it re-submits the same text query on Try again`.
    - `it re-submits the same image file on Try again`.
    - `it preserves the original input until a 201 is received`.
    - `it logs but does not display the API error_code`.

### 9.5 — Offline State (US-11.5)

- [ ] **9.5.1** Transport-error toast ("No connection…"); cached content remains visible; offline submissions never consume the composer input.
  - **Tests:**
    - `tests/Feature/ErrorHandling/OfflineStateTest.php` — `it shows the offline toast when the HTTP client throws a transport error`.
    - `it leaves cached recent analyses and history rows visible`.
    - `it blocks navigation that requires a fresh API call and re-toasts`.
    - `it does not clear the composer input after a failed offline submission`.

---

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
