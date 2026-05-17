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

