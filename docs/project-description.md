# Overview

**Worthly App** is a native mobile application that helps users decide whether a product is worth buying.

The app allows an authenticated user to generate a product analysis from different types of input, such as a product photo or a text question. The input is sent to the Worthly API, which analyzes the product using an LLM and returns a structured recommendation.

Based on the user input, the app displays a clear verdict about the product — **Buy**, **Wait**, or **Skip** — followed by supporting details: product summary, estimated price range, similar alternatives, review highlights, and offers from different retailers.

All main features require authentication via Sanctum bearer tokens. Each analysis is linked to the authenticated user, allowing the app to show previous analyses, organize user activity, and prepare the product for future features such as saved products, favorites, price alerts, personalized recommendations, and usage limits per plan.

The first version of the app is intentionally simple. The MVP focuses on allowing the user to log in, submit a product for analysis, view the verdict, drill into similar products / reviews / offers, and access previous analyses.

The main goal of the Worthly App is to make product research faster, simpler, and more useful for everyday buying decisions, turning long review reading into a single clear answer.

# Key Concepts

## Native Mobile App

Worthly App is a native mobile application built with Laravel and NativePHP, running a full PHP runtime on the device.

The app communicates with the Worthly API over authenticated HTTPS requests and renders all UI natively on iOS and Android.

## Authenticated Usage

The user must be logged in before generating product analyses.

Authentication is handled through the Worthly API using Sanctum personal access tokens. The token is stored securely on the device and sent on every protected API request.

Authentication allows the app to associate each analysis with a specific user, protect user data, and enforce future plan limits.

## Product Analysis Input

The app allows the user to generate a product analysis from different input types.

Supported input types in the MVP:

- Text question or product description
- Product photo (jpeg, png or webp, up to 8 MB)

Text and image are not separate product features. They are two different ways to start the same analysis workflow, both submitted to `POST /api/analyses` with an `input_type` discriminator.

## Buy / Wait / Skip Verdict

Worthly compresses the API recommendation into one of three clear verdicts so the user gets an immediate answer:

- **Buy** — strong recommendation to purchase now
- **Wait** — good product, but timing or price is off
- **Skip** — better alternatives exist or the product is not worth it

The API recommendation decisions (`buy`, `buy_if_price_is_good`, `consider_alternatives`, `wait`, `do_not_buy`) are mapped into these three verdict buckets on the client.

## Product Analysis Result

The app sends the user input to the Worthly API and receives a structured product analysis.

The analysis includes:

- Product identification (name, category, estimated price range)
- Plain-language summary
- Up to 5 similar products with reasons
- Cost-benefit analysis text
- Final buying recommendation with a short reason
- Original image URL (for image analyses)

## Similar Products

Worthly surfaces similar products that may offer better value, better reviews, lower price, or stronger features.

The goal is to help the user compare options at a glance without manually researching every alternative.

## Reviews and Reputation

The result screen exposes a reviews drill-in that summarizes public reputation: common positive points, common complaints, and who the product is best suited for.

## Offers and Price Evaluation

The result screen exposes an offers drill-in that shows current retailer prices, the best price right now, and a short price-history view to help the user judge whether the moment is right to buy.

## Analysis History

The authenticated user can revisit any previous analysis.

History is paginated (15 per page) and served by `GET /api/analyses`. Each history row shows the product name, the verdict, the input type, and the date — and opens the full result on tap.

## Simple MVP Experience

The first version focuses on a direct user flow:

1. Onboarding
2. Sign in
3. Submit a product input (text or photo)
4. Watch the analysis run
5. Read the verdict
6. Drill into similar products, reviews, or offers
7. Access previous analyses

# Tech Stack

## Mobile App

- PHP 8.5
- Laravel 13
- Livewire 4 (UI layer)
- NativePHP Mobile (native iOS / Android runtime)

## API Communication

- Authenticated REST API (Worthly API)
- OpenAPI 3.1 contract (`/api/openapi.yaml`)
- Sanctum bearer token authentication (`Authorization: Bearer <token>`)
- JSON request and response bodies
- Multipart upload support for product images

## Testing

- Pest 4 (feature, unit, and browser tests)

## State and Data Handling

- Secure on-device token storage
- Authenticated user profile via `GET /api/me`
- Paginated analysis history
- Loading states for in-flight analyses
- Error states for validation, authentication, not-found, and upstream LLM failures
- Image download from the API's private storage disk

# Core Workflows

## 1. Onboarding

The first time the user opens the app, a three-slide carousel introduces what Worthly does:

1. Snap a photo or paste a product name and Worthly tells you if it's a good buy
2. Friendly second opinion that reads every review for you
3. Three clear verdicts: Buy, Wait, Skip

The onboarding ends on a CTA to **Get started** (sign in or create an account) and a secondary action **I already have an account**.

## 2. User Authentication

Before using the product analysis features, the user must be authenticated.

The user enters email and password in the app. The app calls the API and receives a Sanctum bearer token.

Example request:

```json
POST /api/login
{
  "email": "user@example.com",
  "password": "password"
}
```

Example response:

```json
{
  "token": "1|plain-text-sanctum-token",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Yan Stein",
    "email": "user@example.com"
  }
}
```

After login, the app stores the token securely and sends it in all protected API requests:

```http
Authorization: Bearer 1|plain-text-sanctum-token
```

Registration uses the same flow with `POST /api/register` and requires `name`, `email`, `password`, and `password_confirmation`.

## 3. Home Screen

After authentication, the user lands on the home screen.

The home screen makes the main action obvious: ask Worthly about any product.

The user can start an analysis by:

- Typing a product name, model, link, or buying question in the composer
- Tapping the camera chip to upload or take a photo of a product

The screen also shows:

- A short list of suggested questions ("Try one")
- The user's most recent analyses (tappable to reopen the result)
- A plan usage indicator (e.g. `32 / 50` on the FREE plan)

## 4. Submit a Product Analysis

The user submits a product input to generate an analysis through `POST /api/analyses`.

The input can be text or image, but both follow the same analysis flow on the server.

Example text input:

```json
{
  "input_type": "text",
  "query": "Is the Logitech MX Master 3S worth buying?"
}
```

Example image input (multipart/form-data):

```
input_type=image
image=@product-photo.jpg
```

The app sends the authenticated request to the API. The API identifies the product, searches for relevant information, evaluates cost-benefit, and returns a structured recommendation.

## 5. Analysis Loading State

While the API processes the request, the app shows a multi-step loader so the user understands what is happening:

1. Identifying product
2. Searching the web
3. Reading reviews
4. Comparing alternatives
5. Forming a verdict

The loader also echoes the user's question (or thumbnail of the uploaded image) and a small mono caption indicating the model and web search are running.

## 6. Result Screen

The result screen is the heart of the app.

It displays the verdict first, followed by supporting details:

- Verdict hero card (Buy / Wait / Skip) with product image, name, category, estimated price range, and a one-line advisor summary
- Long-form summary paragraph
- "Price right now" card with current retailer, current price vs. estimated range, and a price band visualization
- "Why" card with reasons for and reasons against
- Drill-in rows for Similar products, Reviews & reputation, Offers & price history
- Bottom CTAs: **New analysis** and **See best offer**

Example response rendered by the app:

```json
{
  "data": {
    "id": 42,
    "product": {
      "name": "Logitech MX Master 3S",
      "category": "Wireless mouse",
      "estimated_price_range": "$80 - $110"
    },
    "summary": "The Logitech MX Master 3S is a premium wireless mouse focused on productivity, comfort, and precision.",
    "similar_products": [
      {
        "name": "Logitech MX Master 2S",
        "reason": "Older model with lower price and similar productivity features.",
        "price_reference": "$60 - $80"
      },
      {
        "name": "Razer Pro Click",
        "reason": "Alternative focused on ergonomics and professional use.",
        "price_reference": "$80 - $100"
      }
    ],
    "cost_benefit_analysis": "The MX Master 3S is worth it if the user values ergonomics, silent clicks, and productivity features.",
    "recommendation": {
      "decision": "buy_if_price_is_good",
      "reason": "Strong product, but the best decision depends on the current price compared to alternatives."
    },
    "input_type": "text",
    "image_url": null,
    "created_at": "2026-05-14T10:30:00Z"
  }
}
```

The app maps `recommendation.decision` to one of the three verdicts:

- `buy` → Buy
- `buy_if_price_is_good` → Buy (with price-conditional copy)
- `wait` → Wait
- `consider_alternatives` → Wait
- `do_not_buy` → Skip

## 7. Similar Products Screen

Tapping the Similar drill-in opens a dedicated screen.

Each similar product is shown with:

- Product name and brand
- Relation label (e.g. "Cheaper alternative", "Premium upgrade")
- Reference price and delta versus the analyzed product
- Cost-benefit score
- Short reason / trade-off note

A small comparison table at the bottom puts the analyzed product side by side with the top alternatives across price, score, and verdict.

## 8. Reviews Screen

Tapping the Reviews drill-in opens a dedicated screen with:

- Aggregate rating and total review count
- Sentiment breakdown (positive / mixed / negative)
- Top pros with a representative quote
- Top cons with a representative quote
- List of review sources

## 9. Offers Screen

Tapping the Offers drill-in opens a dedicated screen with:

- "Best price right now" callout (lowest available price + retailer)
- Price history sparkline
- Full list of retailers with price, shipping, stock state, and badges (Best price, Lowest, etc.)

For image-based analyses, the original uploaded image can be fetched from `GET /api/analyses/{id}/image` (private storage, served as `image/jpeg`, `image/png`, or `image/webp`).

## 10. History Tab

The History tab lists previous analyses for the authenticated user, fetched from `GET /api/analyses?page={n}` (15 per page).

The screen supports:

- Filter chips by verdict: All / Buy / Wait / Skip
- Day-based grouping (Today, Yesterday, This week, Earlier)
- Tap a row to reopen the full result

Example response:

```json
{
  "data": [
    {
      "id": 1,
      "product_name": "Logitech MX Master 3S",
      "input_type": "text",
      "recommendation": {
        "decision": "buy_if_price_is_good",
        "reason": "Strong product but depends on price."
      },
      "created_at": "2026-05-14T10:30:00Z"
    },
    {
      "id": 2,
      "product_name": "Sony WH-1000XM5",
      "input_type": "image",
      "recommendation": {
        "decision": "consider_alternatives",
        "reason": "Great sound, but a cheaper alternative covers most users."
      },
      "created_at": "2026-05-14T11:45:00Z"
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "per_page": 15, "total": 32 }
}
```

The user can also delete an analysis from history via `DELETE /api/analyses/{id}`.

## 11. Profile Tab

The Profile tab shows:

- Authenticated user (name, email, avatar initial) — sourced from `GET /api/me`
- Usage stats: total analyses, saved products, money saved
- Current plan card with usage progress and an upgrade CTA
- Settings: Saved products, Notifications, Currency, Region, About Worthly
- Sign out action (calls `POST /api/logout` to revoke the current token)

## 12. Error Handling

The app handles errors clearly and simply by mapping the API's error envelopes:

- `401 Unauthenticated.` → push the user back to the login screen and clear the stored token
- `404 Not Found.` → the analysis was deleted or does not belong to the user
- `422 Validation error` → render field-level messages from `errors`
- `502 Server error` → show a friendly "Worthly is having trouble right now" message with retry

Example validation response rendered by the app:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "image": ["The image must be a file of type: jpeg, png, webp."]
  }
}
```

## 13. Empty States

The app includes simple empty states for screens with no data yet.

Example for history:

```text
Nothing here yet.
Send a product photo or type a product name to get your first verdict.
```

## 14. Final Buying Decision

The verdict is the most visible part of the result screen.

The user should not need to read the full report to understand the answer. The hero verdict card shows, in order:

- The verdict pill (Buy / Wait / Skip) with color and code
- A one-line advisor TLDR
- The product name, category, and estimated price range
- A short reason from the API recommendation

# Main Screens

## 1. Onboarding

Three-slide carousel introducing Worthly and the Buy / Wait / Skip model.

## 2. Login

Email and password authentication, with optional SSO buttons (Apple, Google) reserved for a future iteration. Calls `POST /api/login`.

## 3. Home

Composer with text input and camera chip, suggestion chips, recent analyses, and plan usage indicator.

## 4. Analyzing

Multi-step loader displayed while the API processes a new analysis.

## 5. Result

Verdict hero, advisor summary, price band, reasons for / against, and drill-in rows for Similar / Reviews / Offers.

## 6. Similar Products

List of alternatives plus a side-by-side comparison table.

## 7. Reviews

Aggregate rating, sentiment breakdown, top pros, top cons, and sources.

## 8. Offers

Best price callout, price history sparkline, and full retailer list.

## 9. History

Paginated list of previous analyses with verdict filter chips and day grouping.

## 10. Profile

User info, usage stats, plan card, settings, and sign out.

# MVP Requirements

The MVP must include:

- User registration and login against the Worthly API
- Secure on-device storage of the Sanctum bearer token
- Authenticated requests on every protected endpoint
- Product analysis by text (`input_type=text`)
- Product analysis by image upload (`input_type=image`, multipart)
- Multi-step analyzing screen while the API responds
- Result screen with the Buy / Wait / Skip verdict and supporting cards
- Similar products drill-in
- Reviews drill-in
- Offers drill-in
- Original image preview for image analyses (via `GET /api/analyses/{id}/image`)
- Paginated analysis history with verdict filtering
- Delete an analysis from history
- Profile screen with `GET /api/me` and sign out (`POST /api/logout`)
- Basic error handling for 401, 404, 422, and 502 responses
- Empty states for history and offline scenarios

# Future Improvements

Future versions may include:

- Saved products and favorite analyses
- Personalized recommendations based on prior verdicts
- Price alerts and push notifications
- Barcode scanning
- Product link / URL analysis
- Native share sheet integration
- Advanced comparison screen across many similar products
- User purchase preferences (budget, brand bias, must-have features)
- Subscription tiers with higher analysis quotas
- Voice input for the composer
- Offline cache of previous analyses
- Currency and region settings beyond the defaults
