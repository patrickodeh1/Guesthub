# Guesthub — Handoff Summary for Repo-Connected AI / Copilot

## Purpose of this document

This session (with a different AI, working from pasted terminal output only — no direct repo access) has been making a series of UI/UX changes to the Guesthub Laravel app based on a client's change request list. Because that AI could only see pasted snippets (not the live repo), **the true current state of some files is uncertain** — some patches may have applied fully, partially, or not at all due to shell-escaping issues encountered mid-session (see "Known Risk Area" below).

**Your job:** Read the actual repo state directly, diff it against what's described below as "intended end state," identify any drift/incomplete patches, and produce a precise fix-instruction for Copilot (or apply the fixes yourself) so the file matches the intended end state exactly. Do not assume any change described below was successfully applied — verify against the real file first.

---

## Primary file affected

`resources/views/guest/show.blade.php`

This is a large (~2000+ line) Blade view rendering the guest-facing check-in/pre-checkin flow, with multiple numbered "steps" (Step 1: contact/parking/consent, Step 2: ID capture + legal, Step 3: smart lock, etc.), plus significant inline JavaScript handling step navigation, form validation, and submission via `fetch`/`FormData`.

---

## Changes completed and verified earlier in session (should already be correct)

These were patched and confirmed working via direct testing on the client's server before the session moved to the item below — treat these as done unless your repo read shows otherwise:

1. **Guest name field made non-editable.** Removed the pencil-icon edit toggle (`#name-edit-pencil`), the hidden editable input block (`#name-input-block`), and its JS click listener. Name is now shown as plain text with the value submitted via a hidden input (`<input type="hidden" name="guest_name" value="{{ $booking->guest_name }}">`) so existing form-submission JS referencing `guest_name` doesn't break.

2. **"# of Nights" removed from all 4 guest-facing display locations.** Removed `{{ $booking->nightsLabel() }}` calls after check-out date displays throughout the file (was appearing standalone in 3 spots, and combined with `effectiveCheckoutTimeFormatted()` in a 4th spot — that 4th spot kept the checkout time, only removed the nights part).

3. **Admin "Add/Edit Guest" form** (`resources/views/admin/bookings/form.blade.php`) — separate file, confirmed fully patched and working:
   - Booking ID input removed from the form entirely (still auto-generated on the backend)
   - Phone, Email, Early Check-in/Late Checkout fields, Requested Check-in/out Time, Parking, and Incidentals Charge — all wrapped in `@if($booking->exists)` so they only show when editing, not when creating a new guest
   - **Status field was originally hidden this way too, then explicitly un-hidden again** — it's required by the controller/validation and must always be visible on both create and edit
   - Property dropdown given a blank/disabled default option so nothing is pre-selected
   - Phone input gets a live `(000) 123-4567` format mask via inline JS (`id="guest-phone-input"`)
   - **Related backend fix:** `app/Http/Controllers/Admin/BookingController.php` line ~92 — changed `$data['booking_id'] = $data['booking_id'] ?: 'BK-'...` to `$data['booking_id'] = ($data['booking_id'] ?? null) ?: 'BK-'...` because removing the field from the form meant the array key no longer existed at all, causing an `Undefined array key "booking_id"` 500 error on submit. **Verify this fix is present** — it's a correctness fix, not optional.

---

## Change in progress — INCOMPLETE, needs your verification and completion

### Goal
Client wants Terms of Service, Privacy Policy, and the Rental Contract acceptance **consolidated into a single checkbox on Step 1**, instead of two separate acceptance checkboxes on two different steps (currently: Terms/Privacy checkbox on Step 1, separate Rental Contract checkbox later near the ID-capture step).

### Original state (before this session's edits)

**Step 1** (~line 271-282 originally) had:
```blade
@php
    $termsUrl = route('legal.terms');
    $privacyUrl = route('legal.privacy');
@endphp

@if(! $booking->terms_accepted_at)
<div class="mt-6 rounded-xl border border-slate-200 p-4">
    <p class="mb-2 text-sm font-semibold text-slate-900">Terms of Service &amp; Privacy Policy</p>
    <label class="mt-1 flex items-start gap-2 text-sm text-slate-700">
        <input type="checkbox" name="terms_accepted" id="terms-accepted-checkbox" value="1" required class="mt-0.5 rounded border-slate-300">
        <span>I agree to the <a href="{{ $termsUrl }}" class="font-medium underline" target="_blank" rel="noopener">Terms of Service</a> and <a href="{{ $privacyUrl }}" class="font-medium underline" target="_blank" rel="noopener">Privacy Policy</a>.</span>
    </label>
</div>
@endif
```

**Later, near ID capture** (~line 406-418 originally) had a SEPARATE block:
```blade
@php
    $rentalContract = \App\Models\Setting::getValue('rental_contract', '');
    $termsUrl = route('legal.terms');
    $privacyUrl = route('legal.privacy');
@endphp
@if(filled($rentalContract) && ! $booking->contract_accepted_at)
<div class="mt-6 rounded-xl border border-slate-200 p-4">
    <p class="mb-2 text-sm font-semibold text-slate-900">Terms & Rental Contract</p>
    <div class="max-h-40 overflow-y-auto rounded-lg bg-slate-50 p-3 text-xs leading-5 text-slate-600">{!! $rentalContract !!}</div>
    <label class="mt-3 flex items-start gap-2 text-sm text-slate-700">
        <input type="checkbox" name="contract_accepted" id="contract-accepted-checkbox" value="1" required class="mt-0.5 rounded border-slate-300">
        <span>I have read and agree to the rental contract shown above.</span>
    </label>
</div>
@endif
```

Plus corresponding JS:
- Step 1 JS (~line 1225-1246 originally): validated `#terms-accepted-checkbox` is checked, appended `terms_accepted` to a `FormData` called `loginFd`
- Later-step JS (~line 1295-1311 originally): validated `#contract-accepted-checkbox` is checked, appended `contract_accepted` to a separate `FormData` called `fd`

### Intended end state (what we're trying to reach)

1. **Step 1 block** merged into ONE box with ONE checkbox covering all three documents:
```blade
@php
    $rentalContractStep1 = \App\Models\Setting::getValue('rental_contract', '');
@endphp
@if(! $booking->terms_accepted_at || (filled($rentalContractStep1) && ! $booking->contract_accepted_at))
<div class="mt-6 rounded-xl border border-slate-200 p-4">
    <p class="mb-2 text-sm font-semibold text-slate-900">Terms of Service, Privacy Policy &amp; Rental Contract</p>
    @if(filled($rentalContractStep1))
    <div class="max-h-40 overflow-y-auto rounded-lg bg-slate-50 p-3 text-xs leading-5 text-slate-600 mb-3">{!! $rentalContractStep1 !!}</div>
    @endif
    <label class="mt-1 flex items-start gap-2 text-sm text-slate-700">
        <input type="checkbox" name="terms_accepted" id="terms-accepted-checkbox" value="1" required class="mt-0.5 rounded border-slate-300">
        <span>I agree to the <a href="{{ $termsUrl }}" class="font-medium underline" target="_blank" rel="noopener">Terms of Service</a>, <a href="{{ $privacyUrl }}" class="font-medium underline" target="_blank" rel="noopener">Privacy Policy</a>, and the Rental Contract shown above.</span>
    </label>
</div>
@endif
```
   (Note: `$termsUrl` and `$privacyUrl` are still defined earlier in the same `@php` block that already existed just above this section — don't duplicate them, they should already be in scope.)

2. **The later ID-capture-step block is REMOVED entirely** (the whole `@php ... @endphp` + `@if(filled($rentalContract)...) ... @endif` shown in "Original state" above, including its own duplicate `$rentalContract`/`$termsUrl`/`$privacyUrl` definitions).

3. **Step 1 JS** — the validation alert text should read: `"Please agree to the Terms of Service, Privacy Policy, and Rental Contract to continue."` (previously just mentioned Terms/Privacy). And the `loginFd` submission should append BOTH:
```js
if (step1TermsCheckbox) {
    loginFd.append("terms_accepted", step1TermsCheckbox.checked ? "1" : "0");
    loginFd.append("contract_accepted", step1TermsCheckbox.checked ? "1" : "0");
}
```
   (single checkbox drives both backend flags, since there's now only one checkbox for the user to check)

4. **Later-step JS — REMOVED entirely:**
   - The `contractCheckbox` validation block (`var contractCheckbox = document.getElementById("contract-accepted-checkbox"); if (contractCheckbox && !contractCheckbox.checked) { alert(...); return; }`)
   - The corresponding `fd.append("contract_accepted", ...)` block

### KNOWN RISK AREA — verify carefully

Mid-session, patches were applied via inline `python3 -c "..."` one-liners run through a bash terminal. **Bash's history expansion (`!`) corrupted two of the intended patch strings** because they contained literal `!` characters (e.g. `!step1TermsCheckbox.checked`) before `set +H` was run to disable it. This means:

- **Items 1 (Step 1 merge) and item "loginFd append" (part of item 3) were confirmed successfully written** to the file by the tool used in-session.
- **Item 3's alert-text change and item 4 (later-step JS removal) were confirmed successfully written** in a follow-up corrected patch.
- **Item 2 (removing the later ID-capture-step markup block) was NOT confirmed written.** The last check in-session (`grep -n "Terms &amp; Rental Contract"`) returned **zero matches**, which could mean either: (a) it was already successfully removed by an earlier attempt, or (b) the grep pattern itself no longer matches because the surrounding text changed in a partial/unexpected way. **This was never conclusively resolved before the session ended.**

**Action required from you:** Directly inspect the current live content of `resources/views/guest/show.blade.php`:
1. Search for `rental_contract` (case-insensitive) and confirm there is only ONE occurrence of `Setting::getValue('rental_contract'` in the entire file (should be inside the Step 1 `@php` block only, as `$rentalContractStep1`) — if there are two, the old block wasn't removed and needs to be deleted.
2. Search for `contract-accepted-checkbox` — should return **zero results** anywhere in the file (markup or JS). If found, that's leftover from the old block and needs removal.
3. Search for `contract_accepted` — should appear exactly twice: once in the Step 1 JS `loginFd.append(...)` line, and once wherever the backend/controller reads it from the request (that part is untouched, not in this file).
4. Confirm the merged Step 1 markup block matches the "Intended end state" shown above exactly — no duplicate `$termsUrl`/`$privacyUrl` definitions, no leftover orphaned `@endif` or `@php` tags from a partial removal.
5. Confirm there is exactly one `<input ... name="contract_accepted" ...>` OR that it's fully removed from markup and only sent via the JS `loginFd.append` as a synthetic form field (per intended end state — the single checkbox no longer has a real `contract_accepted` input, JS sends it programmatically).

---

## Not yet started (from the client's original request list — separate items, lower priority than the above)

These were explicitly flagged as needing investigation/scoping before touching, not yet started:

- **Vehicle info (make/model, license plate) prompt timing** — client wants this to NOT block initial guest flow, instead prompt guest ~1 day before arrival (unless same-day booking, in which case prompt later in-flow). This requires new scheduling/notification logic — a scheduled job or time-based check — not just a UI toggle. Needs scoping.
- **Guest-facing phone country code default (+1) and formatting** — completed. The guest-facing `input[name="phone"]` fields in `resources/views/guest/show.blade.php` now default to `+1 `, include a broad fallback list of countries with flags and dialing codes when the remote API is unavailable, and apply a live `(000) 123-4567` format mask, matching the admin form behavior.
- **Exclamation/"processing" icon on hard-stop screens** (e.g. background-check-pending) — not started, need to locate the relevant conditional block(s) in `show.blade.php` first.
- **Stripe payment page decluttering** (remove Link/CashApp/Amazon Pay/Bank/save-info buttons, single sliding card-only input, Pay Here vs Pay on Airbnb choice) — not started, larger scope, separate Stripe-related file/component not yet located in this session.
- **Property address truncation at zip code** — not started.
- **Quick Contact button on every page** (confirmed missing on at least one page per client) — not started, need to audit all guest-facing pages.
- **Property images filling their container** — not started, likely a CSS `object-fit`/sizing issue similar to the mobile wizard-card bug already fixed elsewhere in this project (see note below).

## Related, separately-completed work (different files, not part of this handoff's file)

For context, in case relevant to other files you inspect:
- `resources/css/app.css` — mobile check-in/checkout instruction card scroll + sizing bug already fixed (`.wizard-body` given `overflow-y:auto`, `.wizard-image-card img` given `max-height`, card box styling removed per client request). This is DONE and separate from the guest/show.blade.php work above.
- `app/Services/SmsConsentService.php` — opt-in confirmation SMS added (Pass A/Twilio compliance work). DONE.
- SMS/TCR compliance (Pass A) — fully shipped, zipped, deployment doc written. DONE, unrelated to current UI work.

---

## Resolution / corrected status

The rental contract was intentionally already managed in the main settings page, but the final UX requirement was to move it to the legal/privacy page flow and make the guest-facing acceptance link to a dedicated public page. That has now been implemented and verified:

- `routes/web.php` now includes `/rental-contract` with the route name `legal.rental-contract`.
- `app/Http/Controllers/LegalPageController.php` now includes a `rentalContract()` page renderer.
- `resources/views/admin/settings-legal.blade.php` is the single place where admins manage the rental contract title + content.
- `resources/views/public/legal-page.blade.php` includes the Rental Contract link in header/footer navigation.
- `resources/views/guest/show.blade.php` uses a link-only acceptance copy instead of showing the contract inline.
- `app/Http/Controllers/Admin/SettingsController.php` no longer treats the rental contract as a main settings field; it is now a legal-page setting only.
- `resources/views/admin/settings.blade.php` no longer shows the contract editor in general settings.

This resolves the earlier mistake and leaves the contract living in the legal settings flow, while still preserving the single-checkbox guest acceptance flow.

## What to produce

1. Read the actual current state of `resources/views/guest/show.blade.php` in the repo.
2. Diff it against the "Intended end state" section above for the Terms/Privacy/Rental Contract consolidation.
3. Identify exactly what (if anything) still needs to change to reach that intended end state.
4. Write precise, copy-pasteable fix instructions (or a diff/patch) suitable for Copilot to execute — referencing exact current line numbers and exact current text from the real file (not the possibly-stale line numbers/snippets in this document, since those were accurate only at time of writing and the file may have shifted).
5. Do NOT re-apply changes 1-3 from "Completed and verified" section above without first confirming they're actually missing — re-applying already-applied changes could create duplicate blocks or break the file.
