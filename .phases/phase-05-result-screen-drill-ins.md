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

