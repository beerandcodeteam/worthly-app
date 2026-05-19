<h1 align="center">Worthly App</h1>

<p align="center">
  A native iOS &amp; Android shopping advisor built with <strong>Laravel 13</strong>, <strong>Livewire 4</strong> and <strong>NativePHP Mobile</strong>.<br/>
  Snap a photo or type a product name — Worthly tells you if it's a <strong>Buy</strong>, <strong>Wait</strong> or <strong>Skip</strong>.
</p>

---

## Demo

<p align="center">
  <video src="https://objectstorage.sa-saopaulo-1.oraclecloud.com/n/grcgbsnjuagf/b/site-assets/o/worthlyvideo.mp4" controls width="360"></video>
</p>

> If your Markdown renderer doesn't inline the `<video>` tag, open the recording directly:
> <https://objectstorage.sa-saopaulo-1.oraclecloud.com/n/grcgbsnjuagf/b/site-assets/o/worthlyvideo.mp4>

---

## What it does

- **Ask Worthly anything you're about to buy.** Text query or a photo of the product.
- **Async analysis pipeline.** The app submits to `POST /api/analyses`, polls `GET /api/analyses/{id}` once per second, and walks the user through the five live pipeline steps (`l1`..`l5`) until the verdict is ready.
- **Three crisp verdicts:** Buy, Wait, Skip — backed by a one-line advisor reason, summary, price band, similar alternatives, review highlights and retailer offers.
- **History on device.** Every analysis lands in the History tab (filterable by verdict, grouped Today / Yesterday / This week / Earlier).
- **Profile & sign-out.** Authenticated session bound to the device keychain.

The full UX spec lives in [`docs/project-description.md`](docs/project-description.md), the build plan in [`docs/project-phases.md`](docs/project-phases.md), and the static HTML/CSS prototypes the design was derived from in [`docs/worthly-handoff/`](docs/worthly-handoff/).

---

## Tech stack

| Layer            | What                                                                                            |
| ---------------- | ----------------------------------------------------------------------------------------------- |
| Runtime          | PHP **8.4** running on-device via NativePHP Mobile 3 (full PHP binary embedded in the app)      |
| Framework        | Laravel **13** · Livewire **4** · Alpine for tiny client-side gestures                          |
| UI               | Tailwind 4 + design tokens from the Worthly handoff (Geist, Geist Mono, Instrument Serif)       |
| Native shell     | NativePHP Mobile (Swift bridge on iOS, Kotlin bridge on Android, embedded SQLite)               |
| Secure storage   | Custom iOS Keychain bridge handlers (`SecureStorage.Set/Get/Delete`) for the Sanctum token      |
| API              | Worthly REST API (Sanctum bearer auth, polling-based analysis pipeline)                         |
| Tests            | Pest 4 (137 feature + unit tests, 600+ assertions)                                              |
| Code style       | Laravel Pint                                                                                    |

---

## Architecture in 60 seconds

```
┌──────────────────────────────────────────────────────────────────────┐
│  iOS / Android device                                                │
│                                                                      │
│   ┌──────────────────────────────┐   ┌──────────────────────────┐    │
│   │  Native Swift / Kotlin shell │ ◄─┤  Keychain (auth token)   │    │
│   │   (WKWebView + PHP runtime)  │   │  Cache DB (SQLite)       │    │
│   └──────────────┬───────────────┘   └──────────────────────────┘    │
│                  │ nativephp_call (Bridge)                           │
│                  ▼                                                   │
│   ┌──────────────────────────────────────────────────────────────┐   │
│   │  Laravel 13 + Livewire 4 (running locally in WebView)        │   │
│   │  · Onboarding / Auth / Home / Analyze / Result / History     │   │
│   │  · AnalysisSubmitter → POST /api/analyses (multipart)        │   │
│   │  · wire:poll.1s → pollAnalysisStatus() until completed       │   │
│   └──────────────────────────────┬───────────────────────────────┘   │
│                                  │ HTTPS · Bearer <token>            │
└──────────────────────────────────┼───────────────────────────────────┘
                                   ▼
                       ┌────────────────────────┐
                       │  Worthly API (remote)  │
                       │  /api/{me,login,…}     │
                       │  /api/analyses (queue) │
                       └────────────────────────┘
```

---

## Requirements

- PHP **8.4+** (matches `nativephp.lock` and `composer.json`)
- Composer 2
- Node 20+
- For iOS builds: macOS, Xcode 16+, CocoaPods
- For Android builds: Android Studio (Hedgehog or newer) + JDK 17

---

## Getting started

```bash
# 1. Install dependencies
composer install
npm install

# 2. Bootstrap the env / DB / app key
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate

# 3. Build front-end assets (one-shot)
npm run build
```

### Run as a web app (fastest dev loop)

```bash
composer run dev   # spins up: php artisan serve · queue:listen · pail · vite
```

Visit <http://localhost:8000>. The whole app works in the browser — the only thing that needs a real device is the iOS Keychain bridge.

### Run on iOS / Android simulator

```bash
# Once, per platform
php artisan native:install

# Foreground build + watch
npm run build -- --mode=ios          # or --mode=android
php artisan native:run ios --watch   # or: native:run android
```

> `native:install` reads `nativephp.lock` (pinned to PHP 8.4) and downloads the matching on-device runtime.

### Test

```bash
php artisan test --compact               # full Pest suite
vendor/bin/pint --dirty --format agent   # format what you touched
```

---

## Brand assets

The icon and splash screens are generated from a single Artisan command — no Figma round-trips needed:

```bash
php artisan worthly:assets --force   # writes public/icon.png + splash@1x/2x/3x.png
php artisan native:install           # copies them into the iOS/Android projects
```

Tokens (cream `#F2EFE6`, ink `#14130F`, buy-green `#1B7A3F`) live in `resources/css/app.css`.

---

## Project map

```
app/
├── Console/Commands/GenerateBrandAssetsCommand.php
├── Http/Controllers/SessionRestoreController.php  # root entry → /onboarding
├── Livewire/
│   ├── Onboarding/Carousel.php
│   ├── Auth/{Login,Register}.php
│   ├── Home/HomePage.php                # composer + recent analyses
│   ├── Analyze/Composer.php             # async submit + polling loader
│   ├── Result/{ResultPage,SimilarPage,ReviewsPage,OffersPage}.php
│   ├── History/HistoryPage.php
│   └── Profile/ProfilePage.php
├── Services/Worthly/
│   ├── WorthlyApiClient.php             # Sanctum-aware HTTP client
│   ├── AnalysisSubmitter.php            # text + multipart image submit
│   └── Exceptions/*                     # Unauthorized / Validation / Upstream / NotFound
├── Support/
│   ├── AnalysisPipeline.php             # l1..l5 ↔ UI step state
│   ├── Verdict.php                      # API decision → Buy/Wait/Skip
│   └── Storage/NativeSecureTokenStorage.php
└── Contracts/SecureTokenStorage.php

nativephp/ios/NativePHP/Bridge/Functions/
├── EdgeFunctions.swift                  # native UI updates
└── SecureStorageFunctions.swift         # iOS Keychain handlers (token)

resources/views/livewire/                 # Blade templates for every screen
resources/views/components/ui/            # design-system primitives
resources/css/app.css                     # Worthly color + type tokens

docs/
├── project-description.md                # product spec
├── project-phases.md                     # build plan + test contract
├── user-stories.md
└── worthly-handoff/                      # source-of-truth HTML/JSX prototypes
```

---

## API contract highlights

- **Auth:** `POST /api/register`, `POST /api/login`, `POST /api/logout`, `GET /api/me` — all bearer-token based.
- **Analyses (async):** `POST /api/analyses` returns **202** with `status: pending` and `current_step: null`. Clients poll `GET /api/analyses/{id}` every ~1 s until `status` is `completed` or `failed`.
- **Pipeline steps** are surfaced as `current_step ∈ {l1, l2, l3, l4, l5}` and mirrored in `App\Support\AnalysisPipeline::STEPS`.
- **Full OpenAPI:** `GET /api/openapi.yaml` on the API host.

---

## License

MIT.
