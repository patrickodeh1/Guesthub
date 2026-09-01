# Guest Hub — UI/UX Change List (Client Requests, Recovered)

Recovered from client conversation export (Google AI chat, dated in export). Grouped by screen/area. Check off as each is implemented and verified.

---

## 1. Guest Check-In Process (Guest-Facing)

- [x] Show **Check-In / Check-Out Dates only** initially; times appear only once approved/selected
- [x] Remove **"# Nights"** from guest view entirely
- [x] **Guest Name** field — read-only / non-editable by guest
- [x] Phone input — default country code **USA (+1)**, include a broad country dropdown with flags and dialing codes, and auto-format as guest types: `(000) 123-4567`
- [ ] Vehicle info (Make/Model + license plate photo) — do **not** block initial flow. Prompt guest ~1 day before arrival instead (unless same-day booking, then prompt later in-flow)
- [x] Add **Terms of Service**, **Privacy Policy**, and a **Rental Agreement signature step** to the guest flow
- [ ] On any "hard stop" screen (e.g. waiting on background check) — add a subtle exclamation/alert icon + short copy (e.g. *"This is all for now — your details are processing!"*) so the pause reads as intentional, not broken

### Finalized legal/rental-contract note
- [x] Rental contract content now lives under the legal settings page and guests are linked to a dedicated public `/rental-contract` page instead of an inline scroll panel.
- [x] The guest FormData submission still sends both `terms_accepted` and `contract_accepted` when the single checkbox is accepted.

## 2. Stripe / Payment Flow (Guest-Facing)

- [ ] Replace default Stripe Elements layout with a **single, minimalist sliding card input**:
  - Card number → slides to show only card logo + last 4 digits → Expiration Date → slides → CVV → slides → Billing Zip
- [ ] Remove Link autofill, Cash App, Amazon Pay, Bank payment buttons, and "save my info" — card only
- [ ] Show incidentals total + breakdown to guest **regardless of payment path**
- [ ] After showing total, present explicit choice: **[Pay Here with Card]** or **[Pay on Airbnb]**
  - Pay Here → loads sliding card input
  - Pay on Airbnb → shows custom instructions screen (text configurable by admin on backend)

## 3. Property & Contact Info (Guest-Facing)

- [ ] Property addresses — truncate display **at zip code** (drop redundant "USA", city/state suffix)
- [ ] Remove phone numbers from checkout cards — rely solely on the **Quick Contact button**
- [ ] Ensure Quick Contact button is visible on **every page** of registration and pre-checkin (currently missing on at least one page)
- [ ] Property images — should fill their allotted container space (currently showing gaps/cutoff — check `object-fit`/container sizing)

## 4. Admin — Guest Details Page, First Card

- [ ] Layout order: **Guest Name (bold, large)** → Property Name → Dates & Times → (# of Nights — admin-only) → Status
- [ ] Remove: "Current Status" label text, **Booking ID**, **RID**
- [ ] Replace "Edit Guest" button with a small **pencil icon**

## 5. Admin — Guest Details Page, Second Card

- [ ] Remove from default view (edit-view only): duplicate dates, email, phone, ID type, early check-in, GPS
- [ ] Parking status — show **green checkmark** if approved, **red X** if not
- [ ] Requested check-in/out times — only show if **outside normal operating hours** (i.e., would incur a fee)
- [ ] Add **one-click approve** on those out-of-range time requests — commits both times, triggers billing recalculation, updates guest's breakdown before incidentals payment screen

## 6. Admin — Guest Details Page, General

- [ ] Remove "Guest Message Templates" section entirely
- [ ] Remove "Custom Welcome Message" section entirely
- [ ] Quick Actions — reduce to exactly 3, in order: **[Background Passed] → [Deposit Verified] → [Override GPS]**
- [ ] Move Manual Check-In/Out, Photo ID review, and Block Access controls to a sub-menu (off the main quick-actions row)

## 7. Admin — Guests List / Search

- [ ] Guest search bar — typing should show a **live dropdown** of matching results instantly (not just filter a table below)

## 8. Admin — Dashboard "This Week" Card

- [ ] Column 1: **Guest First/Last Name** (bold, larger) → Property (lightweight italics) → Dates → # of Nights
- [ ] Column 2: Status
- [ ] Column 3: Compact **3-dot (⋯)** actions menu
- [ ] Sort order: (1) Checked-in / checking in today — show dynamic text like *"Checks out in 2 days"* instead of total stay length → (2) Recently checked out → (3) Upcoming stays

## 9. Admin — Dashboard "Next Week" Card

- [ ] Same column layout as "This Week"
- [ ] Highlight countdown to arrival, e.g. *"Arriving in 5 days"*
- [ ] Cap at **5–6 records**, with a **"Show More"** link that dynamically loads more in batches (no full page reload / no slowdown)

## 10. Data / ID Mapping (Cross-cutting)

- [ ] Hide **Booking ID** completely from every admin view
- [ ] **ReservationID** field should be remapped to pull the actual **Airbnb Booking ID** (currently importing something else)
- [ ] Guest greetings anywhere on the guest-facing site use **first name only** (full name still captured/stored)
- [ ] **Phone & Email** — hidden on the "Create Record" (new booking) screen; visible only when editing an existing guest
- [ ] **Property dropdown** (admin, when creating a record) — must **never** have a default/pre-selected option
- [ ] All other guest-entered fields — stay hidden on admin's initial view until the guest actually fills them in via pre-checkin
- [ ] **Photo ID Received** field — leave visible for now (testing purposes, per client)

---

## Notes

- These are separate from Pass A (SMS/TCR compliance) and Pass B (privacy/security) — this is a UI/UX and admin-workflow pass.
- Client indicated there are **more items to add** — this doc should be updated as those come in rather than starting a second list.
- Airbnb/Channex availability issue (Priority 2) is tracked separately — not part of this UI list, but was discussed in the same recovered conversation. Summary: Channex is one-way push (Channex → Airbnb) for availability; dates must be opened in Channex's own Inventory grid (Availability, or Rate+Availability if no price set), not on Airbnb directly.
