# Meta Conversions API setup

The Laravel application sends supported commerce events directly from successful server actions.
The integration deliberately fails open: Meta outages are logged and never block carts, checkout,
registration, or payment confirmation.

## Server environment

Add these values to the production `.env` file. Generate a **new** access token in Meta Events
Manager; never expose it in Blade, JavaScript, source control, screenshots, or chat.

```env
META_CAPI_ENABLED=true
META_CAPI_DATASET_ID=3592270860927181
META_CAPI_ACCESS_TOKEN=replace_with_a_new_server_only_token
META_CAPI_API_VERSION=v26.0
META_CAPI_TEST_EVENT_CODE=
META_CAPI_TIMEOUT=4
```

After changing `.env`, run `php artisan config:clear` (or rebuild the production config cache).

## Implemented triggers

- `ViewContent`: successful product detail response
- `Search`: valid product search, deduplicated per query within the visitor session
- `AddToWishlist`: a newly-created wishlist record
- `AddToCart`: successful add-to-cart and buy-now actions
- `InitiateCheckout`: checkout opens with a non-empty cart
- `AddPaymentInfo`: a pending Razorpay order is successfully created
- `Purchase`: Razorpay signature verification succeeds; a stable event ID prevents duplicates
- `CompleteRegistration`: regular registration and checkout-created customer accounts
- `Subscribe`: a new newsletter subscription

`Contact`, `FindLocation`, `StartTrial`, and `Schedule` are accepted by the shared service, but are
not emitted because this application currently has no corresponding successful customer action.
They should be connected only when those features exist, not on page views.

## Customer information

Available email, phone, name, date of birth, city, state, postcode, country, and external ID values
are normalized and SHA-256 hashed. Client IP, user agent, `_fbc`, and `_fbp` are sent without hashing,
as required by Meta. Missing values (including gender, which the site does not collect) are omitted.

Use `META_CAPI_TEST_EVENT_CODE` from Events Manager during verification, check Test Events and
Diagnostics, then remove the test code for production traffic.
