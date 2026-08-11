# GuestHub — Full Task List (Reorganized)

Reordering principle: design/layout and self-contained backend tasks first;
status-flow rebuild next (several things depend on it); the guest
authentication rework last, since it's the highest-risk, most
architecturally-entangled work and shouldn't be built twice if design
tasks touch the same templates first.

---

## PHASE 1 — DESIGN / LAYOUT (no dependencies, safe to build immediately)


1. Text placement: check-in step text should appear below the check-in details; hero image shown first — **DONE**
2. CTA button: "Begin Check In" (green) if all required steps done, else "Begin Registration" (blue); bigger, with arrow — **DONE**
3. Step indicator: large circled current-step number, smaller dash-separated others, centered at top of card — **DONE**
4. Hero image stays static across every check-in screen — **DONE**
5. Welcome guide icon spacing/margin consistency — **DONE**
6. Guide nav icons enlarged to smartphone-icon size (main menu + in-guide scroll nav) — **DONE**
7. Active/current guide page highlighted + slightly larger in scroll nav — **DONE**
8. Fix inconsistent header image sizes across guide pages (checkout/contact banners currently bigger than rest) — **DONE**
9. "Back to Guide" button: move below content, relabel "Dashboard," full width, subtle non-blue/green color — **DONE**
10. Remove redundant NAME/PROPERTY/PHONE cards on registration step — **DONE**
11. Category name displayed under each icon, small bold print, app-icon style (from New_Text_Document.txt) — **DONE**
12. Layout shift bug: page shifts left/right on mobile scroll despite no horizontal content — **DONE**
13. Remove shaded background behind some categories (restaurants, checkout, bars) — **DONE**
14. Door lock category: remove "no lock configured" message; show lock category's own text instead when unconfigured — **DONE**
15. Guest detail page card: remove name/status/dates info, keep contact — make contact a hover button appearing on every page — Not Started (new, found in chat log) — **DONE**
16. Editor content spacing on guest-facing view doesn't match what admin entered — Not Started (new, found in chat log) - DONE
17. Steps page spacing: too much space pushes nav buttons down; buttons should stay static, only page content scrolls — Not Started (new, found in chat log) - DONE
18. Property name/lat-long/edit-property info above guide should be reduced to just property name + image — Not Started (new, found in chat log) - DONE
19. Remove property name from image header on guest guide category pages entirely — Not Started (new, found in chat log) - DONE
20. Main guide icon grid: icons bigger and closer together, minimal spacing — **CLOSED, superseded by task 5** (client reversed direction; see task 5 for standing instruction)

## PHASE 2 — BACKEND / DATA (clear spec, self-contained)

21. Conditional ID type: admin selects state-ID vs passport at guest/booking setup (resolved away from auto-detect or guest-choice per chat) — DONE
22. Use provided frontID.jpg/backID.jpg as upload placeholders; camera-capture only, no camera-roll upload — DONE (NOTE: remember to include public/id_icons/frontID.jpg and public/id_icons/backID.jpg in the zip when shipping this fix — cPanel prod, no npm build, static files must be uploaded manually)
23. Skip back-photo step entirely when ID type is passport — DONE
24. On-image capture instructions: "Take a picture of the front of ID" / "...back of ID," shown on-camera — DONE
25. Camera capture flow: tap ID icon → camera opens with alignment border → capture → shows captured image with retake option → repeat for back — DONE
26. Exception checkbox per booking: skip ID upload entirely if already collected off-platform; guest sees "ID already received" instead of upload prompts — DONE
27. Settings-page category reorder (drag-and-drop) — currently only works per-property, not on the global Settings category list — Not Started (new, found in chat log) - DONE
28. Lockbox code stored on properties table, editable via property edit screen (quick-code field) — DONE
29. ID display: modal popup instead of new tab — DONE
30. ID display: zoom capability — DONE
31. ID capture quality: blurry/excess background — DONE (guide-border crop-on-capture + Laplacian blur/text-density check, admin verification remains backstop)
32. Full-size ID/guest-detail page redesign — deprioritized per client, not urgent — DONE

## PHASE 3 — BUGS

33. Lock status desync: UI/Seam reports "locked" but door not physically locked, requiring repeated button presses — Not Started (new, found in chat log — needs investigation into whether GuestHub polls actual device state after sending a command, or trusts an optimistic response)
34. Parking-needed toggle doesn't save on first attempt — **DONE**
35. Booking status doesn't auto-update — guest-submitted check-in/out time, parking, ID status not reflecting in admin — DONE , needs per-field investigation
36. GPS location verification false negatives despite guest being on-site; review override logic for a more reliable primary check — DONE (new, found in New_Text_Document.txt)- DONE
37. "Try Again" button after GPS override doesn't work — guest must manually refresh browser; also make override auto-refresh the guest's browser — DONE (new, found in New_Text_Document.txt)
38. Email field (step 2 of 3) doesn't validate format — Done (already implemented: native type="email" validation + server-side rule + styled error block; confirmed via guest-flow test)
39. Time field (step 2 of 3) should be a selectable time picker in 30-min increments, required — currently a free-text field — Done (replaced with required <select> dropdown, 30-min increments, 12h display/24h storage; selected time now also drives canViewAddress() and all guest-facing check-in-time displays, replacing hardcoded 3:00 PM; falls back to new default_checkin_time Setting for pre-existing bookings)
40. Delete/archive guests from bookings; rename "Bookings" to "Guests" site-wide — DONE (new, found in New_Text_Document.txt)
41. Image inserted via TinyMCE editor shows broken/404 to guest — **DONE**
42. Clean up already-saved content with old broken relative image paths — **DONE** (no code fix needed; client re-inserts affected images post-deploy, must be noted in deployment instructions)
72. Admin "mark photo ID as received" bypass didn't set approved_at, and the approved-state guest Continue button was a plain link that skipped form submission — edits to name/phone/email/parking/checkin-time during bypass flow were silently discarded — **DONE** (new, found in chat log)

## PHASE 4 — STATUS FLOW REBUILD (foundational — several later tasks depend on this)

43. Reconcile and implement full status flow: Pending → Pre-Checkin Complete → Awaiting Deposit → Guest Approved → Pending Check In → Currently Hosting → Checked Out (exact spec + guest-facing copy in guest_hubu.txt) — DONE, blocked on reconciling with existing approve/decline build first
44. Deposit/incidentals-hold tracking — admin manually marks paid/verified — DONE
45. Guest-facing status messages per stage, exact copy from client, stored as static text for now (customizable layer later) — DONE (added awaiting_deposit/checkout_notice/checkout_available/post_checkout states with exact client copy in state() + show.blade.php; fixed waiting/arrival copy to match spec; tested and verified in browser)
46. Guest status not auto-updating to "checked out" after checkout process completes — DONE (root cause: step-wizard fetch had no response.ok check, silently treated 419/failed requests as success; fixed with proper error handling + inline retry; also fixed reload-restarts-wizard bug by checking status===checked_out directly in state() and view; tested and verified)
47. Checkout page needs to expire — currently guest can revisit indefinitely; must redirect to standalone "Thank You" post-stay page — DONE (added isPastCheckoutDay() to Booking model fixing the >= vs > bug that made checkout day and every day after indistinguishable; added post_checkout state with client's exact thank-you/discount copy; tested and verified)
48. Checkout day behavior: full menu access in morning; "Begin Checkout" button/popup appears at 6pm local property time; after checkout time fully passed, guest loses all access except checkout page — DONE (added checkout_time field to properties with admin-editable standalone save; added checkout_notice/checkout_available/checkout_locked states with property-timezone-aware time gating; fixed inverted isCheckoutDayBeforeSixPM logic; fixed banner not propagating to guide sub-pages due to hardcoded state guide value and missing state prop on GuestLayout component; tested and verified)
49. Text notification to admin/host: guest completes pre-checkin + ID upload — DONE, needs SMS provider (e.g. Twilio) confirmed
50. Text notification to admin/host: guest checks in — DONE

51. Text notification to admin/host: guest checks out — DONE


## PHASE 5 — DASHBOARD REBUILD

52. Priority section: today's check-in or next upcoming, one per property, own card — DONE
53. Copy logic: "Checking in today" (bold) vs "in X days" (normal) vs nothing if already checked in — DONE
54. Card shows guest name, phone, status (focal point), property name — DONE
55. Outstanding requirements as bullet points, or "ready for check-in" if none; nothing shown if already checked in — DONE
56. Status progression on card: upcoming → currently hosting → checkout date (must align with Phase 4 status model) — DONE
57. Dashboard "at a glance" — open for suggestions, client's call — DONE
58. Smart lock status card per property: locked/unlocked + battery % (August locks) — DONE
59. August lock spelling/config note: confirm `august_lock` device-type string matches Seam exactly — resolved in chat, verify still correct -DONE

## PHASE 6 — CONTENT / MISC

60. Internal page link picker in editor (this site vs external URL, select page by name not raw URL) — Done; adds separate toolbar button in category content editor, picker lists property's guide category pages, inserts stable ID-based internal reference (survives category rename), resolves to real guest booking URL at render time. Shorter guest-facing slugs (e.g. `guesthub.us/checkin/pool`) deferred to auth rework phase — current long booking-ID-carrying URLs unchanged for now.
61. WYSIWYG editor: expand toward full-featured editor (client: "every editor I've ever used already has these") — Done
62. Local events category — ZIP or lat/long + radius API integration — Done
63. Live temperature + weather condition in guest page header, all page - DONE, needs API research
64. Date range display: abbreviated month, no repeat if same month (e.g. "July 1–4") — **DONE for admin views**; guest-facing tiles still show as two separate labels, deferred to combine with Phase 1 layout work

## PHASE 7 — AUTH REWORK (build last — highest risk, most architecturally entangled)

65. Deep-link URLs to specific guest pages (checkout, amenities, etc.) — e.g. `guesthub.us/checkin/checkout` — Not Started; routing-only part can be built standalone
66. Guest stays logged in for full duration of stay via link — Not Started, real session/auth system needed
67. Access banned/revoked at checkout (overlaps Phase 4 checkout-expiry work) — Not Started
68. Security beyond a simple token in the URL — Not Started
69. Pre-checkin guests log in via phone number + email combo — Not Started
70. Explore shortening guest URL / storing token in session — same underlying mechanism as auth rework, build once — Not Started
71. Block guest access with custom reason: free-text now, pre-selectable dropdown later per client's stated end goal — Partially built (decline_reason exists for ID decline specifically; general-purpose version not yet built)
72. GPS/location step: icon + map + customizable message; auto-advance once granted — Not Started
73. Step 1 (address/directions) shown before GPS check, only within 1hr of check-in time (property-level default check-in time if guest hasn't specified one) — Not Started
74. Ask guest desired check-in/check-out times, store, use for timer logic — DONE
75. [OPEN QUESTION] Requested time outside property's normal window — reject/flag/accept? — DONE
76. Returning registered guest sees homepage-style view (guide or first page + Begin Check In button) — DONE
77. Welcome text shown during registration — DONE

---

## CLOSED — confirmed shipped in chat, no longer active tasks
- Property "View"/"Edit" nav wording clarity — shipped
- Parking steps visibility (migration issue) — shipped
- WiFi category display (two cards: network + password) — confirmed already working
- Lock button: single button, LOCK DOOR/UNLOCK DOOR text, red/green status color — shipped
- Lock test-as-guest flow — shipped
- "AI-looking stars" / sparkle icons removed site-wide — shipped
- Category drag-and-drop (per-property) — shipped
- Edit / Edit Content button de-duplication — shipped
- Parking-unknown → prompt guest — shipped
- ID approval system (Tasks 35-40 legacy numbering): admin approve/decline, "ID Received/under review" state, re-upload with reason, archive old ID, photo_id_received bug fix, continue-button gating — all **DONE**
- Admin nav restructure (Properties → per-property submenu) — **DONE**
- ID images shown directly on page, no download required — **DONE**
- ID stored at full original resolution — **DONE**, verified no code change needed
- Downloaded ID file showing generic "FILE" type instead of high-res JPG — **DONE**
- Spellcheck: reverted to native `browser_spellcheck: true` (right-click-to-fix), matches client's originally-confirmed-working baseline — **DONE**

## CLOSED — out of scope / won't-fix
- Lock activity report showing "Seam"/device brand instead of guest name — confirmed via chat this occurs on the August/Seam app's own console, not GuestHub's activity page; outside GuestHub's control

