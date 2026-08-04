# GuestHub — Full Task List (Updated)

## ⚠️ SCOPE RECONCILIATION NEEDED — READ FIRST
The client's latest message (`guest hubu.txt`) describes a **new, richer booking status flow**:
`Pending → Pre-Checkin Complete → Awaiting Deposit → Guest Approved → Pending Check In → Currently Hosting → Checked Out`

This is a bigger model than the approve/decline system already built earlier today (`approved_at`, `decline_reason` layered on the original 5-status enum: `pending, id_uploaded, waiting_checkin, checked_in, checked_out`). These two need to be reconciled into one coherent state machine before more status-related work is built — do not build the new flow as a second parallel system. This is Task 61 below and should likely be done early, since several other new tasks depend on the final status model.

---

## GROUP A: GUEST-FACING FLOW — ACCESS, REGISTRATION, CHECK-IN/CHECKOUT, ID UPLOAD

1. Build automatic URLs sent to guests for specific messages (checkout, amenities, etc.) — **NEEDS CLIENT RECONFIRMATION** (scoping revealed real ambiguity: amenities can deep-link via existing guest.category route, but checkout is state-driven not destination-driven — link can't force a specific screen right now. Need to confirm: does the checkout message need to force-show checkout content ahead of the actual checkout day, or does it only fire once checkout day naturally arrives? Deferred until clarified.)
2. Guest stays logged in for full duration of stay via this link — **DEFERRED, bundled with Task 1/4/5** (real guest auth/login system needed, client indicated this changes the whole approach — handle together as one auth rework, not piecemeal)
3. Access banned/revoked at checkout — Not Started (see Task 63, checkout page behavior, overlaps here)
4. Add security beyond a simple token in the URL — **DEFERRED, bundled with Task 1/2/5**
5. Pre-checkin guests log in via phone number + email combo — **DEFERRED, bundled with Task 1/2/4** (client's own words: "they can login using their phone number and email together as a combination so that the correct dataset can be viewed")
6. Explore shortening guest URL / storing token in session — **DEFERRED, bundled with Task 1/2/4/5** (same underlying mechanism as real guest auth — session-based identification is the auth system, not a separate workaround. Build once, as part of the auth rework, not twice.)
7. Day before check-in: keep menu access + add "Begin Checkout" button; lock down after checkout complete — Not Started (superseded/refined by Task 64 below, same underlying feature, more detail now provided)
8. BUG: "parking needed" toggle doesn't save on first attempt — **DONE**
9. Text placement below check-in details — Not Started
10. Hero image priority: welcome guide banner, fallback to property image — Not Started
11. CTA button: Begin Check In (green) / Begin Registration (blue), bigger, with arrow — Not Started
12. Step indicator: large circled current-step number, smaller dash-separated others, centered — Not Started
13. Hero image persists/static across every check-in screen — Not Started
14. Returning registered guest sees homepage-style view (guide or first page + Begin Check In) — Not Started
15. Welcome text shown during registration — Not Started
16. Step 1 (address/directions) shows before GPS, only within 1hr of check-in time — Not Started
17. Host can block guest access with custom free-text reason — Partially built (decline_reason mechanism exists for ID decline specifically; general-purpose block-with-reason not yet generalized beyond that)
18. Step 2 GPS: simplify to icon + map + customizable message — Not Started
19. Auto-advance to next step once location granted — Not Started
20. Each step's text in its own card, above image card — Not Started
21. Welcome guide icons need consistent spacing/margin — Not Started
22. Smart lock: dedicated lock/unlock button on main check-in view — Not Started
23. Scrolling guide nav icons: enlarge to smartphone size — Not Started
24. Active/current guide page highlighted and slightly larger in scroll nav — Not Started
25. Fix inconsistent header image sizes across guide pages — Not Started
26. "Back to Guide" button: move below content, relabel "Dashboard," full width, subtle color — Not Started
27. Ask guest desired check-in/check-out times, store, use for timer logic — Not Started
28. [OPEN QUESTION] Requested time outside property's normal window — reject/flag/accept? — Blocked, unanswered
29. Remove redundant NAME/PROPERTY/PHONE cards — Not Started
30. Conditional ID type: US = state ID, international = passport — Not Started (method confirmed, see #31)
31. [RESOLVED] Admin manually selects ID type when setting up guest booking — Ready to implement
32. Use provided frontID.jpg / backID.jpg as placeholders in upload UI — Not Started
33. Camera-capture only for ID photos, no camera-roll upload — Not Started
34. [OPEN QUESTION] Desktop fallback when no camera available — allow upload, or block step? — Blocked, unanswered
34b. On-image capture instructions ("Take a picture of the front/back of ID") — Not Started
34c. Skip back-photo step entirely when ID type is passport — Not Started

### ID Approval System (built today, functioning, see tracker for details)
35. Admin approve/decline action on uploaded ID — **DONE**
36. Guest sees "ID Received, under review" state within wizard step 3 (not a separate page) — **DONE**
37. Guest sent back to re-upload with visible reason if declined — **DONE**
38. Old ID archived (not wiped) on re-upload — **DONE**
39. `photo_id_received` correctly set on guest submission (was a real bug, fixed) — **DONE**
40. Continue button on step 3 disabled until approved, enabled + functional once approved — **DONE**

## GROUP B: ADMIN NAVIGATION RESTRUCTURE
41. "Properties" nav expands into submenu listing each property — Not Started
42. Each property click shows Check In/Out Details + Guest Guide entry points — Not Started

## GROUP C: RICH TEXT / CONTENT EDITOR
43. Internal page link picker (this site vs external URL, select page) — Not Started
44. Spell-check needs dictionary library with clickable correction suggestions — Not Started

## GROUP D: DASHBOARD REBUILD
45. Priority section: today's check-ins or next upcoming, one per property — Not Started
46. Each property = its own card — Not Started
47. Copy logic: "Checking in today" (bold) vs "in X days" (normal) vs nothing if checked in — Not Started
48. Card shows guest name, phone, status (prominent) — Not Started
49. Outstanding requirements as bullet points, or "ready for check-in" if none — Not Started
50. Status progression: upcoming → currently hosting → checkout date — Not Started (must align with Task 61's reconciled status model)
51. Guests needing attention surfaced, sorted chronologically — Not Started
52. [OPEN] Dashboard "at a glance" suggestions — Not Started, your call
53. NEW: Smart lock status card per property — locked/unlocked + battery percentage (August locks) — Not Started, needs Seam API check for whether battery % is exposed for August devices specifically

## GROUP E: DASHBOARD / ADMIN BUGS
54. Lock status activity report shows "Seam" instead of guest name — Blocked, waiting on client: GuestHub's activity page or Seam's own console?
55. NEW/CRITICAL: Booking status doesn't auto-update — guest-submitted info (check-in/out time preference, parking, ID status) not reflecting/updating correctly in admin — Not Started, likely several distinct bugs bundled under one report, needs investigation per field

## GROUP F: GUEST DETAIL / ID REVIEW
56. Improve findability — clicking guest shows full details on one page — Not Started
57. Inline edit button per section on same detail page — Not Started
58. Display ID images directly on page, no download required — **DONE**
59. Store uploaded ID at full original resolution, no compression — **DONE** (verified already correct, no code change needed)
60. Show normal preview size by default, full-size view option — Partially done (opens in new tab, not a modal/lightbox)
60b. Full-size ID view should open in modal, not new tab — Not Started
60c. Zoom capability on full-size ID view — Not Started
60d. Capture quality issue — blurry photos, too much background; needs framing guide/quality check at capture, separate from display fix — Not Started
61. BUG: downloaded ID file shows generic "FILE" type instead of proper high-res JPG — **DONE**
62. BUG: guest with ID uploaded shows wrong status — **SUPERSEDED by full status flow rebuild (Task 63), do not fix in isolation**
63. Full guest-detail layout redesign — Explicitly NOT priority, deprioritized per client instruction

## GROUP G: GALLERY / MEDIA
64. BUG: image inserted via TinyMCE editor shows as broken/404 to guest — **DONE** (fixed going forward; see 64b for cleanup of already-broken existing content)
64b. NEW: Clean up already-saved content containing old broken relative image paths (e.g. welcome page) — **DONE (no code fix needed — client will manually re-insert/re-save affected images after this update deploys; must be included as a note in deployment instructions)**

## GROUP H: NEW ITEMS (local events, weather, dates)
65. Local events category — ZIP or lat/long + radius API integration — Not Started, needs API research
66. Live temperature + weather condition in guest page header, all pages — Not Started, needs API research
67. Date range display formatting — abbreviated month, no repeat if same month — **DONE for admin-facing views (dashboard, bookings list, booking detail) — added `Booking::stayRangeLabel()` helper**
67b. Guest-facing check-in/check-out tiles currently shown as two separate labels, not a combined range — deferred to be handled together with Group A guest-flow redesign tasks (9-26), since combining them is a layout change, not just a formatting fix

## GROUP I: FULL STATUS FLOW REBUILD (NEW — from guest_hubu.txt)
68. **Reconcile and implement the full status flow** (see scope note at top of this doc):
    - Pending (admin enters booking info)
    - Pre-Checkin Complete (guest submits details + ID)
    - Awaiting Deposit (after background check marked complete by admin)
    - Guest Approved (after admin verifies deposit payment)
    - Pending Check In (check-in day reached, provided guest approved; otherwise reflects actual incomplete state)
    - Currently Hosting (guest checked in)
    - Checked Out (after checkout)
   Not Started — blocked on reconciliation with today's approve/decline build first.
69. Guest-facing status messages per stage, exact copy provided by client (see guest_hubu.txt for exact wording) — Not Started, copy should be stored as editable/customizable text per client's note ("should be customizable layer, but static text is okay for now")
70. Deposit/incidentals-hold tracking — admin manually marks paid/verified — Not Started, new field needed

## GROUP J: SESSION / LOGIN PERSISTENCE (NEW, admin-side — distinct from guest session work in Group A)
71. **CRITICAL BUG:** Admin/staff getting logged out unexpectedly, hitting "page not available"/419 (CSRF token expired) errors, forced back to login and losing their place — Not Started. Likely Laravel session lifetime/CSRF token expiry misconfiguration. Needs investigation into `config/session.php` lifetime settings and possibly `VerifyCsrfToken` exclusions or session driver issues.
72. Any user with site access should stay logged in until explicitly logged out (not auto-expire) — Not Started, related to above

## GROUP K: CHECKOUT FLOW OVERHAUL (NEW — detailed spec from guest_hubu.txt)
73. **CRITICAL BUG:** Guest status not auto-updating to "checked out" after they complete the checkout process — Not Started
74. Checkout page needs to actually expire — currently guest can revisit and still sees checkout message indefinitely; needs to redirect to a dedicated post-stay "Thank You" page once checkout is truly complete — Not Started
75. Post-checkout Thank You page: standalone, guest sees ONLY this page and nothing else on the site afterward, friendly copy inviting them to book again / contact for discount (exact copy in guest_hubu.txt) — Not Started
76. Checkout day behavior clarified: guest keeps full menu access in the morning (e.g. still wants pool info), "Begin Checkout" appears as a button/popup starting 6pm local property time — Not Started
77. Once local checkout time has fully passed, guest loses all access except the checkout button/page — Not Started

## GROUP L: SMS NOTIFICATIONS (NEW)
78. Text notification to admin/host when guest completes pre-checkin + ID upload — Not Started, needs SMS provider (e.g. Twilio) — no provider/account confirmed yet
79. Text notification to admin/host when guest checks in — Not Started
80. Text notification to admin/host when guest checks out — Not Started

## GROUP M: SMART LOCK / BATTERY (NEW)
81. August lock battery percentage on dashboard — Not Started, needs to confirm Seam API exposes battery data for August devices specifically (not all lock brands report this identically)
(See also Task 53, the dashboard card itself — this and 53 are the same feature, listed once here for the API/data question, once there for the UI)

## GROUP N: LOCKBOX CODE (NEW)
82. Store lockbox code per property in the database, editable via property edit screen (quick-code field) instead of requiring instruction-step content edits every time the code changes — Not Started. Straightforward: new column on `properties` table, exposed in property edit form, referenced wherever lockbox instructions currently hardcode or reference a step-embedded code.

---

## Summary counts
- **Done:** 10 items (Tasks 8, 35–40, 58, 59, 61, 64)
- **In Progress:** 0 items
- **Partially Done:** 3 items (17, 60, 62-superseded)
- **Blocked (need client answers):** 28, 34, 54
- **Blocked (needs internal reconciliation first):** 68 (status flow) — several other tasks depend on this being resolved first (50, 62, 69, 70)
- **Everything else:** Not Started