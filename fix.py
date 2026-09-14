from pathlib import Path

def patch(path, replacements):
    p = Path(path)
    text = p.read_text()
    for old, new in replacements:
        count = text.count(old)
        if count != 1:
            raise SystemExit(f"FAILED in {path}: expected 1 match, found {count}, for snippet starting:\n{old[:120]!r}")
        text = text.replace(old, new)
    p.write_text(text)
    print(f"Patched {path}")

patch("resources/css/app.css", [
    (
"""    .guest-card-field iframe,
    .guest-card-field .StripeElement iframe {
        display: block !important;
        width: 100% !important;
        height: 24px !important;
        border: 0 !important;
    }""",
"""    .guest-card-field iframe,
    .guest-card-field .StripeElement iframe {
        display: block !important;
        width: 100% !important;
        /* Stripe requires a minimum height of 28px for the native Link
           button to render inside the Card Element (cardNumber field
           specifically -- expiry/cvc don't need this, but sharing the
           class keeps things consistent and gives a little breathing
           room across all three either way). Previously 24px, which sat
           just under Stripe's threshold and silently suppressed Link. */
        height: 32px !important;
        border: 0 !important;
    }"""
    ),
])

print("Patched successfully.")
