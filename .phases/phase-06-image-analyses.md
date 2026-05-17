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

