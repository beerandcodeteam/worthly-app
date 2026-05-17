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

