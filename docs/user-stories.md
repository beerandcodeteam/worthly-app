# Worthly App — User Stories

## Overview

This document contains user stories for **Worthly App**, a native mobile application (Laravel + NativePHP Mobile + Livewire 4) that helps users decide whether a product is worth buying. The user submits a product as text or as a photo, the Worthly API analyzes it with an LLM, and the app renders a **Buy / Wait / Skip** verdict with supporting details.

All protected features authenticate via Sanctum bearer tokens against the Worthly API (`/api/openapi.yaml`).

**User Types:**
- **Visitor** — Unauthenticated user opening the app for the first time (onboarding, login, register)
- **Authenticated User** — Logged-in user who can submit analyses, browse history, manage their profile

> **Scope note:** These stories cover the **MVP only**. The Reviews and Offers drill-ins are scoped to what the existing `AnalysisResource` exposes (no rating breakdown, no retailer list, no price history). The FREE plan / usage indicator is display-only — there is no plan or quota endpoint in the API yet.

---

## 1. Onboarding & Authentication

### US-1.1: View Onboarding Carousel
**As a** Visitor opening the app for the first time
**I want to** see a short carousel explaining what Worthly does
**So that** I understand the value of the app before signing in

**Acceptance Criteria:**
- [ ] Three-slide carousel is shown on first launch only:
    1. "Snap a photo or paste a product name and Worthly tells you if it's a good buy"
    2. "Friendly second opinion that reads every review for you"
    3. "Three clear verdicts: Buy, Wait, Skip"
- [ ] The user can swipe between slides and skip to the end
- [ ] The final slide presents a primary CTA **Get started** and a secondary CTA **I already have an account**
- [ ] **Get started** routes to Register; **I already have an account** routes to Login
- [ ] Onboarding is not shown again after the user has authenticated at least once (persisted on device)

**Expected Result:** Visitor understands the Buy / Wait / Skip model and is routed into the auth flow.

---

### US-1.2: Register a New Account
**As a** Visitor
**I want to** create an account using name, email, and password
**So that** I can start generating product analyses

**Acceptance Criteria:**
- [ ] Registration form collects: `name`, `email`, `password`, `password_confirmation`
- [ ] App calls `POST /api/register` with a JSON body
- [ ] Email must be a valid format; password must be at least 8 characters; `password_confirmation` must match
- [ ] On `201`, the returned Sanctum token is stored on secure on-device storage and the user is routed to the Home screen
- [ ] On `422`, field-level error messages are shown next to the corresponding inputs (e.g. "The email has already been taken.")
- [ ] The auth response payload (`token`, `token_type`, `user`) is persisted so `GET /api/me` does not need to be called on first launch

**Expected Result:** A new authenticated session is created, the bearer token is stored securely, and the user lands on the Home screen.

---

### US-1.3: Log In with Email and Password
**As a** Visitor with an existing account
**I want to** log in using my email and password
**So that** I can resume using Worthly

**Acceptance Criteria:**
- [ ] Login form collects: `email`, `password`
- [ ] App calls `POST /api/login` with a JSON body
- [ ] On `200`, the returned token is stored securely and the user is routed to the Home screen
- [ ] On `401`, a generic "Invalid email or password" message is shown (no field-level hint)
- [ ] On `422`, field-level error messages are shown
- [ ] A **Forgot password?** link is rendered but disabled in MVP (no reset endpoint exists yet)
- [ ] Apple / Google SSO buttons are visually present but disabled with a "Coming soon" tooltip

**Expected Result:** A valid bearer token is stored on the device and used for all subsequent protected requests.

---

### US-1.4: Stay Signed In Across App Launches
**As an** Authenticated User
**I want to** remain logged in when I reopen the app
**So that** I do not have to type my password every time

**Acceptance Criteria:**
- [ ] The stored Sanctum token is read from secure on-device storage at app launch
- [ ] If a token is present, the app calls `GET /api/me` to validate it and hydrate user info
- [ ] If `GET /api/me` returns `200`, the user is routed directly to the Home screen
- [ ] If `GET /api/me` returns `401`, the token is wiped and the user is routed to the Login screen
- [ ] The token is never logged or exposed in plain-text logs

**Expected Result:** Returning users skip authentication entirely when their token is still valid.

---

### US-1.5: Sign Out
**As an** Authenticated User
**I want to** sign out from the Profile tab
**So that** my session is revoked on this device

**Acceptance Criteria:**
- [ ] A **Sign out** action is available on the Profile tab
- [ ] Tapping it shows a confirmation prompt ("Sign out of Worthly?")
- [ ] On confirm, the app calls `POST /api/logout` with the current bearer token
- [ ] On `204` or `401`, the local token is cleared and the user is routed to the Login screen
- [ ] Cached profile, history, and any in-flight analysis state are cleared on sign out

**Expected Result:** The current Sanctum token is revoked server-side, the device no longer has a stored token, and the user returns to Login.

---

## 2. Home Screen

### US-2.1: Land on Home After Authentication
**As an** Authenticated User
**I want to** land on a home screen that makes the main action obvious
**So that** I can quickly start a new product analysis

**Acceptance Criteria:**
- [ ] Home screen shows a greeting (e.g. "Hi, {first_name}") sourced from the cached `/api/me` user
- [ ] A composer is visible with:
    - A multi-line text input (placeholder: "Ask Worthly about any product…")
    - A camera chip to upload or take a photo
    - A primary **Ask** button (disabled until the composer has text or an attached image)
- [ ] A "Try one" row shows 3–5 suggestion chips (static suggestions in MVP, e.g. "Logitech MX Master 3S", "Sony WH-1000XM5")
- [ ] A "Recent analyses" section shows up to 3 of the user's most recent analyses (from the first page of `GET /api/analyses`)
- [ ] A FREE plan usage indicator is rendered (e.g. `32 / 50`) — see US-9.3
- [ ] Bottom tab bar exposes Home, History, and Profile tabs

**Expected Result:** The user has a clear, single-action entry point to start an analysis.

---

### US-2.2: Reopen a Recent Analysis from Home
**As an** Authenticated User
**I want to** tap a recent analysis card on the Home screen
**So that** I can review a verdict without going through History

**Acceptance Criteria:**
- [ ] Each recent-analysis card shows: product name, verdict pill (Buy / Wait / Skip), input type icon, relative date
- [ ] Tapping a card calls `GET /api/analyses/{id}` and routes to the Result screen
- [ ] Loading state is shown while the request is in flight
- [ ] On `404`, the user sees an "Analysis no longer available" toast and stays on Home
- [ ] On `401`, the auth flow described in US-9.1 kicks in

**Expected Result:** The full Result screen for the chosen analysis is rendered.

---

### US-2.3: Use a Suggestion Chip
**As an** Authenticated User
**I want to** tap a "Try one" chip
**So that** I can quickly submit a sample query without typing

**Acceptance Criteria:**
- [ ] Tapping a suggestion chip prefills the composer text input with the chip's text
- [ ] The composer's primary CTA (**Ask**) becomes enabled
- [ ] The user can edit the prefilled text before submitting
- [ ] No request is sent until the user explicitly taps **Ask**

**Expected Result:** A suggestion is loaded into the composer and the user can refine and submit it.

---

## 3. Product Analysis — Submission

### US-3.1: Submit a Text Analysis
**As an** Authenticated User
**I want to** type a product question and send it to Worthly
**So that** I can receive a Buy / Wait / Skip verdict

**Acceptance Criteria:**
- [ ] The composer accepts up to 1000 characters (matches `query` `maxLength`)
- [ ] On **Ask**, the app calls `POST /api/analyses` with JSON `{ "input_type": "text", "query": "..." }`
- [ ] While the request is in flight, the multi-step loader (US-3.3) is shown
- [ ] On `201`, the returned `AnalysisResource` is rendered on the Result screen (US-4.1)
- [ ] On `422`, field-level errors are shown on the composer (e.g. "The query field is required.")
- [ ] On `502`, a friendly "Worthly is having trouble right now" screen is shown with a **Try again** button (US-9.4)
- [ ] On `401`, the user is bounced to Login (US-9.1)

**Expected Result:** A new analysis is persisted server-side, owned by the authenticated user, and rendered on the Result screen.

---

### US-3.2: Submit an Image Analysis
**As an** Authenticated User
**I want to** take or upload a product photo and send it to Worthly
**So that** I can get a verdict without typing the product name

**Acceptance Criteria:**
- [ ] Tapping the camera chip opens an action sheet with **Take photo** and **Choose from library** options (via NativePHP camera/file picker)
- [ ] Accepted formats: `jpeg`, `png`, `webp`; max size 8 MB (enforced client-side before upload)
- [ ] A thumbnail of the selected image is shown in the composer, with a remove (✕) affordance
- [ ] On **Ask**, the app calls `POST /api/analyses` as `multipart/form-data` with `input_type=image` and `image=<file>`
- [ ] The same loader, success, and error handling as US-3.1 apply
- [ ] If the user provides both text and an image, the image takes precedence (`input_type=image` and `query` is omitted) — OR — the **Ask** button is disabled with a hint to remove one (final decision documented in the Result screen story)
- [ ] On `422` with `errors.image`, the message is rendered inline next to the thumbnail (e.g. "The image must be a file of type: jpeg, png, webp.")

**Expected Result:** A new image-based analysis is created, the original image is stored on the API's private disk, and the verdict is rendered on the Result screen.

---

### US-3.3: View the Multi-Step Analyzing Loader
**As an** Authenticated User
**I want to** see a multi-step loader while my analysis is being processed
**So that** I know the app is working and roughly how long it will take

**Acceptance Criteria:**
- [ ] While `POST /api/analyses` is in flight, a full-screen loader replaces the composer
- [ ] The loader cycles through five labeled steps:
    1. Identifying product
    2. Searching the web
    3. Reading reviews
    4. Comparing alternatives
    5. Forming a verdict
- [ ] The loader echoes the user's input (text query as a quote, or thumbnail of the image)
- [ ] A subtle mono caption indicates "Model + web search running"
- [ ] Step transitions are time-based animations on the client (they do not reflect real API progress)
- [ ] The loader does not expose a cancel action in MVP — the request runs to completion
- [ ] If the request resolves before the animation finishes, the loader skips to the final step before transitioning to the Result screen

**Expected Result:** The user perceives steady progress and is never staring at an unmoving spinner.

---

## 4. Result Screen — Verdict & Details

### US-4.1: View the Buy / Wait / Skip Verdict
**As an** Authenticated User
**I want to** see a single clear verdict at the top of the Result screen
**So that** I can decide without reading the full report

**Acceptance Criteria:**
- [ ] A verdict hero card is rendered first, containing in order:
    - Verdict pill colored by bucket: **Buy** (green), **Wait** (amber), **Skip** (red)
    - One-line advisor TLDR
    - Product name, category, estimated price range (from `product.*`)
    - The `recommendation.reason` string
- [ ] The API's `recommendation.decision` is mapped to a verdict bucket on the client:
    - `buy` → **Buy**
    - `buy_if_price_is_good` → **Buy** (with price-conditional secondary copy)
    - `wait` → **Wait**
    - `consider_alternatives` → **Wait**
    - `do_not_buy` → **Skip**
- [ ] If `product.category` or `product.estimated_price_range` is `null`, the field is hidden rather than showing "null"
- [ ] The hero card is the first thing the user sees — no spinner or other content above it

**Expected Result:** The verdict bucket and core product identification are visible immediately when the Result screen opens.

---

### US-4.2: View the Long-Form Summary and Reasons
**As an** Authenticated User
**I want to** read the summary and the reasons behind the verdict
**So that** I understand why Worthly recommended what it did

**Acceptance Criteria:**
- [ ] Below the hero, an "Advisor summary" section renders `summary` (paragraph text)
- [ ] A "Why" card renders the `cost_benefit_analysis` text split into:
    - A "Reasons for" column (positive points)
    - A "Reasons against" column (negative points)
- [ ] Splitting `cost_benefit_analysis` into pros/cons is done client-side using simple heuristics (sentence-level sentiment / connector keywords). If splitting fails, the full text is rendered as a single paragraph
- [ ] If `summary` is `null`, the section is hidden
- [ ] If `cost_benefit_analysis` is `null`, the "Why" card is hidden

**Expected Result:** The user can read a brief explanation under the verdict without leaving the Result screen.

---

### US-4.3: View the "Price Right Now" Card
**As an** Authenticated User
**I want to** see a price reference card on the Result screen
**So that** I can judge whether the product is in a reasonable price range

**Acceptance Criteria:**
- [ ] A "Price right now" card is rendered using `product.estimated_price_range`
- [ ] The card shows:
    - The estimated price range as the headline (e.g. "$80 – $110")
    - A static "estimated by Worthly" caption (no live retailer data in MVP)
    - A price-band visualization (a horizontal bar showing the range; no live current-price marker in MVP since the API does not return one)
- [ ] If `product.estimated_price_range` is `null`, the entire card is hidden

**Expected Result:** The user sees the estimated price range in a glanceable visual, even though no live retailer data is available yet.

---

### US-4.4: Bottom CTAs on the Result Screen
**As an** Authenticated User
**I want to** quickly start a new analysis or jump to offers from the Result screen
**So that** I can keep moving without going back to Home manually

**Acceptance Criteria:**
- [ ] Two CTAs are pinned to the bottom of the Result screen:
    - **New analysis** → routes back to the Home composer with a cleared input
    - **See best offer** → routes to the Offers drill-in (US-7.1)
- [ ] If there are no offers data to show (which is always true in MVP), the **See best offer** CTA still routes to the Offers screen and surfaces the price-range placeholder defined in US-7.1

**Expected Result:** The user has a single tap to either start over or drill into offers.

---

## 5. Similar Products Drill-In

### US-5.1: View Similar Products
**As an** Authenticated User
**I want to** see a list of similar products from the Result screen
**So that** I can compare alternatives at a glance

**Acceptance Criteria:**
- [ ] A "Similar" drill-in row is rendered on the Result screen, showing a count badge (e.g. "5 alternatives") sourced from `similar_products.length`
- [ ] Tapping the row opens the Similar Products screen
- [ ] The Similar Products screen renders each `similar_products[]` item with:
    - Product `name`
    - `reason` text
    - `price_reference` (or "—" if `null`)
- [ ] A simple two-column comparison table is rendered at the bottom: analyzed product vs. the first similar product, comparing name and price reference (no score/verdict columns since the API does not provide them)
- [ ] If `similar_products` is empty, the drill-in row is hidden on the Result screen and the Similar screen is not reachable
- [ ] The list respects the API's `maxItems: 5` constraint — no virtualization needed

**Expected Result:** The user can browse up to 5 alternatives, each with a reason and a reference price.

---

## 6. Reviews Drill-In

### US-6.1: View Reviews Drill-In (Derived View)
**As an** Authenticated User
**I want to** open a Reviews screen from the Result screen
**So that** I can read a curated reputation summary of the product

**Acceptance Criteria:**
- [ ] A "Reviews & reputation" drill-in row is rendered on the Result screen
- [ ] Tapping the row opens the Reviews screen
- [ ] The Reviews screen renders only what the API currently exposes:
    - A "Reputation summary" section showing `summary` (the same long-form text from the Result screen, with a different heading framing)
    - A "What Worthly considered" section that renders `cost_benefit_analysis`
    - The same heuristic pros/cons split from US-4.2 is reused here under "Top pros" and "Top cons"
- [ ] No aggregate star rating, no review count, no sentiment percentages, and no review sources are rendered — the API does not provide them; these are explicitly flagged as **post-MVP**
- [ ] If both `summary` and `cost_benefit_analysis` are `null`, the drill-in row is hidden on the Result screen

**Expected Result:** The user gets a "what people are saying"-style summary built entirely from existing `AnalysisResource` fields.

---

## 7. Offers Drill-In

### US-7.1: View Offers Drill-In (Derived View)
**As an** Authenticated User
**I want to** open an Offers screen from the Result screen
**So that** I can see a price reference for the product

**Acceptance Criteria:**
- [ ] An "Offers & price history" drill-in row is rendered on the Result screen
- [ ] Tapping the row opens the Offers screen
- [ ] The Offers screen renders only what the API exposes:
    - A "Price reference" callout reusing `product.estimated_price_range`
    - The verdict reason from `recommendation.reason` framed as price guidance
    - An "Alternatives by price" list reusing `similar_products[]` sorted by `price_reference` when present
- [ ] No live retailer list, no sparkline, no shipping/stock badges, and no "Best price" marker are rendered — the API does not provide them; these are explicitly flagged as **post-MVP**
- [ ] If `product.estimated_price_range` is `null` and `similar_products` is empty, the Offers drill-in row is hidden on the Result screen

**Expected Result:** The user sees the best price reference Worthly can offer today, with an honest empty surface where retailer data will eventually live.

---

## 8. Image Analyses

### US-8.1: View the Original Image of an Image Analysis
**As an** Authenticated User
**I want to** see the product photo I uploaded
**So that** I know which item the verdict refers to

**Acceptance Criteria:**
- [ ] When `input_type` is `image`, the Result screen renders the original image at the top of the hero card
- [ ] The image is fetched from `GET /api/analyses/{id}/image` using the same bearer token (`Authorization: Bearer …`)
- [ ] Supported MIME types are `image/jpeg`, `image/png`, `image/webp` (per OpenAPI)
- [ ] While the image is loading, a low-fidelity placeholder of the same aspect ratio is shown (no layout shift)
- [ ] On `404` (image missing), a small "Image unavailable" placeholder is rendered instead of a broken icon
- [ ] On `401`, the auth flow described in US-9.1 kicks in
- [ ] For `text` analyses, no image is fetched; the hero shows only the product name + category

**Expected Result:** Image-based analyses render the original upload alongside the verdict; text analyses do not call the image endpoint.

---

## 9. Analysis History

### US-9.1: View Paginated Analysis History
**As an** Authenticated User
**I want to** open a History tab listing my previous analyses
**So that** I can revisit verdicts I have generated before

**Acceptance Criteria:**
- [ ] The History tab calls `GET /api/analyses?page={n}` (15 per page)
- [ ] Each row shows: product name (`product_name`), verdict pill (from `recommendation.decision` → bucket), input type icon (text or image), formatted `created_at`
- [ ] Rows are grouped by day with section headers: **Today**, **Yesterday**, **This week**, **Earlier**
- [ ] Infinite scroll (or a paging control) loads the next page when the user reaches the end of the list (`links.next` is non-null)
- [ ] Pull-to-refresh resets to page 1
- [ ] On `401`, the user is bounced to Login and the local token is cleared

**Expected Result:** The user can browse their full analysis history, grouped by recency, and the list loads more rows as they scroll.

---

### US-9.2: Filter History by Verdict
**As an** Authenticated User
**I want to** filter my history by verdict bucket
**So that** I can quickly find products I marked as Buy / Wait / Skip

**Acceptance Criteria:**
- [ ] A horizontal chip row at the top of the History tab offers: **All**, **Buy**, **Wait**, **Skip**
- [ ] Filtering is client-side over the loaded pages (the API does not expose a verdict filter parameter in MVP)
- [ ] The chip shows a checked state and the list reflows when filtered
- [ ] If a filter yields zero results across loaded pages, an empty-state copy is rendered ("No {verdict} analyses yet")
- [ ] Filters are cleared on tab change

**Expected Result:** The user can drill into a verdict bucket without leaving the History tab.

---

### US-9.3: Reopen a Historical Analysis
**As an** Authenticated User
**I want to** tap any history row
**So that** I can re-read the full result of that analysis

**Acceptance Criteria:**
- [ ] Tapping a row calls `GET /api/analyses/{id}` and routes to the Result screen
- [ ] A spinner is shown only while the request is in flight (the row's data is reused as a skeleton for the hero card)
- [ ] On `404`, the row is removed from the local list and a toast "Analysis no longer available" is shown
- [ ] All Result-screen behaviors (US-4.x, US-5.1, US-6.1, US-7.1, US-8.1) apply identically to re-opened analyses

**Expected Result:** Historical analyses are indistinguishable from freshly created ones once opened.

---

### US-9.4: Delete an Analysis from History
**As an** Authenticated User
**I want to** delete an analysis from my history
**So that** I can clean up entries I no longer care about

**Acceptance Criteria:**
- [ ] A history row exposes a delete affordance (swipe-to-delete on iOS, long-press menu on Android, or an explicit ✕ button — pick one consistent pattern)
- [ ] Tapping delete shows a confirmation ("Delete this analysis? This cannot be undone.")
- [ ] On confirm, the app calls `DELETE /api/analyses/{id}`
- [ ] On `204`, the row is removed from the local list with an animation; no re-fetch is needed
- [ ] On `404`, the row is removed anyway and a toast is shown
- [ ] On `401`, the auth flow from US-9.1 kicks in

**Expected Result:** The analysis is removed both server-side and from the local cache.

---

### US-9.5: Empty State for History
**As an** Authenticated User who has never created an analysis
**I want to** see a helpful empty state in the History tab
**So that** I know what to do next

**Acceptance Criteria:**
- [ ] When `GET /api/analyses` returns an empty `data` array, the tab renders a centered empty state:
    - Headline: "Nothing here yet."
    - Body: "Send a product photo or type a product name to get your first verdict."
    - CTA button: **Start an analysis** → routes to the Home composer
- [ ] The empty state is not shown while the first page is still loading
- [ ] The empty state never flickers between page transitions

**Expected Result:** New users are not dropped into an empty list with no direction.

---

## 10. Profile Tab

### US-10.1: View Authenticated Profile
**As an** Authenticated User
**I want to** see my account info on the Profile tab
**So that** I can confirm I am signed in with the right account

**Acceptance Criteria:**
- [ ] The Profile tab calls `GET /api/me` on first display (and again after pull-to-refresh)
- [ ] Renders `name`, `email`, and a name-initial avatar derived from `name`
- [ ] On `401`, the token is cleared and the user is routed to Login
- [ ] The endpoint result is cached for the session so tab switches do not re-fetch unless invalidated

**Expected Result:** The user sees their canonical account info, sourced from the API and not from the cached login response.

---

### US-10.2: View Usage Stats and Plan Card (Display-Only)
**As an** Authenticated User
**I want to** see usage stats and my plan on the Profile tab
**So that** I have a sense of how much I have used Worthly

**Acceptance Criteria:**
- [ ] A "Usage" block shows three counters: **Total analyses**, **Saved products**, **Money saved**
    - **Total analyses** is computed from `meta.total` of `GET /api/analyses?page=1`
    - **Saved products** is rendered as `0` (no saved-products endpoint in MVP)
    - **Money saved** is rendered as `—` (no data source in MVP)
- [ ] A "Plan" card shows a static FREE plan with a usage progress bar (e.g. `Total analyses / 50`) and an **Upgrade** CTA
- [ ] The **Upgrade** CTA is visually present but disabled with a "Coming soon" tooltip (no plan/quota endpoint exists)
- [ ] No part of this story enforces a quota — submissions are never blocked client-side based on the indicator
- [ ] Settings rows are rendered as visual placeholders (Saved products, Notifications, Currency, Region, About Worthly) but only **About Worthly** is functional in MVP — it opens a static info screen

**Expected Result:** The Profile tab matches the design while being honest about which surfaces are not yet wired to a real backend.

---

## 11. Error Handling & Cross-Cutting Concerns

### US-11.1: Handle 401 Unauthenticated Globally
**As an** Authenticated User whose token has expired or been revoked
**I want to** be routed back to the Login screen cleanly
**So that** I do not get stuck on an authenticated screen with no data

**Acceptance Criteria:**
- [ ] Any response with status `401` and body `{ "message": "Unauthenticated." }` triggers a global handler
- [ ] The handler:
    - Clears the stored bearer token
    - Drops cached user, history, and in-flight analysis state
    - Routes the user to the Login screen with a single toast: "Session expired. Please sign in again."
- [ ] No protected request is retried automatically after a 401

**Expected Result:** A single, predictable behavior across the app for any expired-token scenario.

---

### US-11.2: Handle 422 Validation Errors
**As an** Authenticated User submitting an invalid payload
**I want to** see field-level error messages
**So that** I can correct the input and try again

**Acceptance Criteria:**
- [ ] A `422` response with the validation envelope `{ message, errors: { field: [msg, ...] } }` is parsed
- [ ] Each field's first message is rendered inline next to the corresponding input (composer text, image attachment, login email, register password, etc.)
- [ ] If the response payload contains a field the app does not render, a fallback toast shows `message` so the error is never silently swallowed
- [ ] No retry happens automatically — the user must edit and re-submit

**Expected Result:** All API validation messages reach the user with the right input attached.

---

### US-11.3: Handle 404 Not Found on Analysis Endpoints
**As an** Authenticated User opening a stale link to an analysis
**I want to** see a friendly "no longer available" message
**So that** I am not stuck on a broken screen

**Acceptance Criteria:**
- [ ] A `404` from `GET /api/analyses/{id}`, `DELETE /api/analyses/{id}`, or `GET /api/analyses/{id}/image` triggers a contextual handler
- [ ] On `GET /analyses/{id}` 404 from a recent-analyses card or history row: the row is removed locally and a toast is shown ("Analysis no longer available")
- [ ] On `GET /analyses/{id}/image` 404: an "Image unavailable" placeholder replaces the image (see US-8.1)
- [ ] On `DELETE /analyses/{id}` 404: treat as success (the row is removed; the user gets no error toast)
- [ ] No screen renders a raw "404" status to the user

**Expected Result:** Stale analyses degrade gracefully without confusing error noise.

---

### US-11.4: Handle 502 Upstream Failures on Analysis Creation
**As an** Authenticated User submitting an analysis when the LLM backend is failing
**I want to** see a friendly retry surface
**So that** I do not lose my input or my time

**Acceptance Criteria:**
- [ ] A `502` from `POST /api/analyses` triggers a dedicated error screen / sheet
- [ ] The screen shows:
    - Headline: "Worthly is having trouble right now"
    - Body: a short, non-technical message (the API's `error_code` is logged for diagnostics but not shown raw)
    - A **Try again** button that re-submits the same payload (text query or attached image)
    - A **Back** action that returns to the Home composer with the input preserved
- [ ] The composer's original input (text or image) is never cleared until the analysis is successfully created (`201`)

**Expected Result:** Upstream LLM failures feel like a temporary blip, not a dead-end.

---

### US-11.5: Offline / No-Connection State
**As an** Authenticated User without internet
**I want to** see a clear offline message instead of a silent failure
**So that** I know the problem is my connection, not the app

**Acceptance Criteria:**
- [ ] When a protected request fails with a network/transport error (no HTTP response at all), a neutral toast is shown: "No connection. Check your internet and try again."
- [ ] The cached recent-analyses and last-loaded history page remain visible — they are not blanked out
- [ ] Tapping a row that requires a fresh API call (e.g. opening a historical analysis) shows the offline toast and does not route
- [ ] The Home composer's **Ask** button is enabled offline but submission attempts surface the offline toast (and do not consume the input)

**Expected Result:** The app stays usable for cached content and never silently swallows a connection failure.

---

## Appendix: User Story Status

| ID | Story | Priority | Status |
|---|---|---|---|
| US-1.1 | View Onboarding Carousel | Medium | Pending |
| US-1.2 | Register a New Account | High | Pending |
| US-1.3 | Log In with Email and Password | High | Pending |
| US-1.4 | Stay Signed In Across App Launches | High | Pending |
| US-1.5 | Sign Out | High | Pending |
| US-2.1 | Land on Home After Authentication | High | Pending |
| US-2.2 | Reopen a Recent Analysis from Home | Medium | Pending |
| US-2.3 | Use a Suggestion Chip | Low | Pending |
| US-3.1 | Submit a Text Analysis | High | Pending |
| US-3.2 | Submit an Image Analysis | High | Pending |
| US-3.3 | View the Multi-Step Analyzing Loader | Medium | Pending |
| US-4.1 | View the Buy / Wait / Skip Verdict | High | Pending |
| US-4.2 | View the Long-Form Summary and Reasons | High | Pending |
| US-4.3 | View the "Price Right Now" Card | Medium | Pending |
| US-4.4 | Bottom CTAs on the Result Screen | Medium | Pending |
| US-5.1 | View Similar Products | High | Pending |
| US-6.1 | View Reviews Drill-In (Derived View) | Medium | Pending |
| US-7.1 | View Offers Drill-In (Derived View) | Medium | Pending |
| US-8.1 | View the Original Image of an Image Analysis | High | Pending |
| US-9.1 | View Paginated Analysis History | High | Pending |
| US-9.2 | Filter History by Verdict | Medium | Pending |
| US-9.3 | Reopen a Historical Analysis | High | Pending |
| US-9.4 | Delete an Analysis from History | Medium | Pending |
| US-9.5 | Empty State for History | Medium | Pending |
| US-10.1 | View Authenticated Profile | High | Pending |
| US-10.2 | View Usage Stats and Plan Card (Display-Only) | Low | Pending |
| US-11.1 | Handle 401 Unauthenticated Globally | High | Pending |
| US-11.2 | Handle 422 Validation Errors | High | Pending |
| US-11.3 | Handle 404 Not Found on Analysis Endpoints | Medium | Pending |
| US-11.4 | Handle 502 Upstream Failures on Analysis Creation | High | Pending |
| US-11.5 | Offline / No-Connection State | Medium | Pending |
