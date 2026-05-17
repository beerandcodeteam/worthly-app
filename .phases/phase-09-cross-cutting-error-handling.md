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

