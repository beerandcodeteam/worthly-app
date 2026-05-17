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

