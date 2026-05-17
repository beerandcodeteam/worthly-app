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

