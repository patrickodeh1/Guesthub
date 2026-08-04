# GuestHub — Change Tracker

RULE: update this file as work progresses. Never mark Done without files touched + what was tested. Never move to a new task while another is In Progress/Blocked without explicit sign-off (see claude.md).

Status values: `Not Started` | `In Progress` | `Blocked` | `Done`

---

## DONE

### Task 8 — "Parking needed" toggle not saving on first attempt
**Status:** Done
**Files changed:** `app/Http/Controllers/Admin/BookingController.php` (added confirming comment, code was already correct); `app/Http/Controllers/GuestController.php` (real bug found and fixed: guest identity submission unconditionally overwrote `parking_needed`, even when already set at booking creation — now only sets it if `is_null($booking->parking_needed)`)
**Tested:** Reproduced live with a real booking (set parking=Yes at creation, completed guest pre-checkin, confirmed it stayed Yes instead of flipping to No)
**Notes:** Two separate bugs under one report — admin form itself was fine; the overwrite happened via guest submission.

### Tasks 35–40 — ID Approval System (built beyond original scope, replaces old status-label-only task)
**Status:** Done
**Files changed:**
- `database/migrations/*_add_approval_fields_to_bookings_table.php` — new `approved_at` (timestamp), `decline_reason` (text) columns
- `app/Models/Booking.php` — added `isApproved()`, `isDeclined()`, `needsIdApproval()` helpers, fillable/casts updated
- `app/Http/Controllers/Admin/BookingController.php` — added `approveBooking()`, `declineBooking()` methods
- `routes/web.php` — added `bookings.approve`, `bookings.decline` routes
- `resources/views/admin/bookings/show.blade.php` — Approve/Decline UI added
- `app/Http/Controllers/GuestController.php` — fixed real bug: `photo_id_received` was never being set true on guest submission (only ever set via manual admin action before); status-setting logic fixed to reflect actual ID upload instead of check-in-day proximity; decline_reason cleared on resubmission; old ID archived (not deleted) to `photo-ids-archive/{booking_id}-{slug}/` on new upload
- `resources/views/guest/show.blade.php` — step 3 of the wizard now shows "ID already received" + decline reason banner if present; Continue button disabled until `isApproved()`, auto-jumps guest to step 3 on page load if awaiting approval
**Tested:** Full loop tested live — upload → under review state → admin decline with reason → guest sees reason, re-uploads → admin approve → guest proceeds
**Notes:** ⚠️ This status model (approved_at/decline_reason layered on old 5-status enum) needs to be reconciled with the new full status flow requested in the client's later message (Pending → Pre-Checkin Complete → Awaiting Deposit → Guest Approved → Pending Check In → Currently Hosting → Checked Out). Do not build the new flow as a second parallel system — see Task 68 in task-list.md.

### Task 58 — Display ID images directly on page, no download required
**Status:** Done
**Files changed:** `app/Http/Controllers/Admin/BookingController.php` (`photoIdView()`, `photoIdBackView()` added, serve via `response()->file()`), `routes/web.php` (2 new routes), `resources/views/admin/bookings/show.blade.php` (inline `<img>` thumbnails added)
**Tested:** Verified live against a real guest's uploaded ID

### Task 59 — Store uploaded ID at full original resolution
**Status:** Done — verified already correct, no code change was needed
**Notes:** Confirmed `store('photo-ids')` never compressed/resized anything; this was already satisfied by existing code.

### Task 61 — BUG: downloaded ID file shows generic "FILE" type instead of JPG
**Status:** Done
**Files changed:** Same as Task 58 — root cause was `response()->download()` filename missing a file extension; fixed by appending `pathinfo($path, PATHINFO_EXTENSION)`.
**Tested:** Verified live — downloaded file now opens correctly as high-res JPG.

---

## DONE (continued)

### Task 64 — Gallery/editor-inserted image shows broken/404 to guest
**Status:** Done (fixed going forward — see Task 64b for cleanup of already-broken existing content)
**Real root cause (confirmed):** TinyMCE's default `relative_urls: true` setting strips the domain from same-origin image URLs when content is saved — e.g. `http://localhost:8000/img/media-library/xxx.png` gets saved as just `img/media-library/xxx.png`. This works fine only if content is always viewed on the same page path it was edited on. Since guest pages live at a completely different path (`/guest/{booking_id}/{token}/...`), the relative path resolves incorrectly there, producing broken image URLs like `/guest/img/media-library/xxx.png`.
**Files changed:** Added `relative_urls: false, remove_script_host: false` to every `tinymce.init({...})` call across:
- `resources/views/admin/settings.blade.php`
- `resources/views/admin/content/page-form.blade.php`
- `resources/views/admin/instructions/form.blade.php`
- `resources/views/admin/bookings/show.blade.php`
**Tested:** Confirmed by client directly against the actual failing image — root cause verified correct.
**Notes:** This fix only affects new image insertions going forward. Existing content saved before this fix (e.g. the original welcome page image) still has the broken relative path baked into stored HTML — see Task 64b.

**⚠️ Investigation dead ends — logged so they are not repeated:**
1. Initially misdiagnosed `MediaFile::url()` (`return url('/img/' . $this->path)`) as broken, believing no `/img/{path}` route existed. This was **wrong** — the route does exist (`routes/web.php:191`, handled by `App\Http\Controllers\ImageController`), it was simply missed by an incomplete grep search that didn't search for the term "img" directly. `MediaFile::url()` was changed to use `Storage::disk('public')->url()` and then **correctly reverted back to its original, correct form** once this was discovered. No code change was ultimately needed in `MediaFile.php` or `ImageController.php` — both were correct all along.
2. While chasing the above incorrect theory, local Docker's `public/storage` symlink was unnecessarily touched (renamed away, `storage:link` re-run, causing temporary local-only 404s from what's suspected to be an nginx symlink-following quirk specific to this local Docker setup). This was fully reverted. **Unrelated to the real bug, does not need any action on production.**
3. `.env` `APP_URL` was changed from blank to `http://localhost:8000` while chasing the above — this change remains in place locally but was not actually the fix; worth noting it's a reasonable thing to have corrected regardless (blank `APP_URL` is generally not desirable), but was not the root cause of this specific bug.

---

## NOT STARTED / BLOCKED — carried over, see task-list.md for full descriptions

- Task 17 (partially built — general block-with-reason not yet generalized beyond ID decline)
- Task 28 — open question, check-in/out time outside window
- Task 34 — open question, desktop camera fallback
- Task 54 — blocked, need client answer on Seam log location
- Task 60b/60c/60d — modal/zoom/capture-quality for ID viewer
- Task 64b — cleanup already-saved content with old broken relative image paths
- Task 68 — full status flow reconciliation (blocks 50, 62, 69, 70)
- All Group A guest-flow items not yet started
- All Group B/C/D admin nav, editor, dashboard items not yet started
- All new Groups I–N (status flow, session persistence, checkout overhaul, SMS, smart lock battery, lockbox code)

---

## Open questions log (do not resolve without client input)
- Task 28: what happens if guest-requested check-in/out time is outside property's normal window?
- Task 34: desktop fallback when no camera is available for ID capture?
- Task 54: is "Seam" appearing on GuestHub's activity page or Seam's own console?

## Deployment notes
- Migrations added so far: `add_approval_fields_to_bookings_table` (approved_at, decline_reason on bookings) — needs `php artisan migrate` on deploy
- Cache/config clear required: yes, standard `route:clear`, `config:clear`, `view:clear` after any deploy from this session
- `.env` `APP_URL` should be checked/set correctly on production as part of investigating Task 64 — do not assume it's already correct there
- Nothing from the local Docker symlink tangent needs to be replicated on production — that was local-only and reverted