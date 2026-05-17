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

