# Session Task List

## Resolved clarifications
- Email: use Resend.
- SMS: currently configured for admin only; guest SMS "not working" = Twilio
  misconfigured by client on their end, not a code bug. No SMS-sending code fix needed
  unless investigation finds an actual bug — will verify config wiring only, not assume broken.
- "Guest ID" in admin views = same field as booking ID, just renamed at some point.
  Do NOT remove it — only confirm consistent naming/display as "booking ID" per task language.
- Task 33 (deposit paid flips status to checked-in) — client already knows the undo path
  (booking edit page). NO FIX NEEDED. Marked resolved/no-op below.

## Needs clarification / access before starting
- Q3: Incidentals charge field, parking rate tiers, early check-in tiers (8am/12pm), late
  checkout rates (authorized vs unauthorized) — need exact dollar structure / business rules,
  or just the DB fields + admin UI and you fill in real numbers later?
- Q4: "Coming soon" landing page — need copy/highlights/benefits text, or should I draft it?
- Q5: Early-access registration form — payment required? Which processor (Stripe?).
- Q6: Custom-named verification step — confirm this is the same as existing "extra steps"
  feature seen in migrations (`add_excluded_steps_to_bookings`)?

## Process note
Before coding each task, we restate the client's original wording + our interpretation,
get sign-off, then implement. See PROD_INSTRUCTIONS.md for anything requiring prod access
or client confirmation — do not guess at prod state.

## Critical fixes
1. ~~Session/"page doesn't exist" after inactivity~~ — DIAGNOSED, no code bug found;
   added trustProxies() fix; rest is a prod .env checklist (see PROD_INSTRUCTIONS.md #5)
2. ~~Root domain shows admin login instead of coming-soon page — NEW landing page (Q4/Q5)~~ — DONE
   (payment deferred per direction; contact form + admin leads list built instead)
3. Inner iframe/scroll issue on steps pages
4. Text alerts not working (Q2)
5. ~~Check-in time showed system's checkout time (10am) as recommended~~ — DONE.
   Guest check-in dropdown now recommends 4pm (shown first), helper text corrected.
   Handled together with task 7.
6. Pending check-in page missing property image header
7. ~~Checkout time picker should only allow 7am–2pm~~ — DONE (with task 5). Range
   narrowed to 7am-2pm, 10am recommended and shown first.
8. ~~Flip front/back ID review + independent approve/reject + resubmit alert~~ — DONE (merged with 22)
9. ~~ID capture: auto-fill frame + auto-capture, no manual button~~ — DONE (combined
   with 10 + 11, see PROD_INSTRUCTIONS.md for tuning/self-hosting follow-ups)
10. ~~ID photos showing overlay/haze ("Hayes film") on every guest~~ — DONE (combined
    with 9). Root cause: capture fired before camera autofocus/exposure settled.
11. ~~ID photos blurry — blur detection not working~~ — DONE (combined with 9). Replaced
    untested hand-rolled threshold with OpenCV.js-based detection; needs real-world
    threshold tuning (see PROD_INSTRUCTIONS.md).
12. Checkout status not updating day-before / warning before final "checked out" press
13. Admin guest list: full name visibility, rectangular hero image, no truncation
14. ~~Remove guest ID from list views; only booking ID on full detail page~~ — DONE.
    No second ID existed — "Guest ID" label on the add/edit guest form was a rename
    of booking ID; relabeled back to "Booking ID" (field/name unchanged). Booking ID
    and reservation ID removed from the admin guest list row entirely (only shown on
    the full detail page).
15. Replace view/edit buttons with single action-menu (quick actions)
16. Guest list landing: remove stat cards, add "Add Guest", surface action items only
17. Single unified search bar (name/reservation ID) replacing filters
18. Action menu = all quick actions + status changes from edit screen
19. 24h → 12h time display in guest details
20. Parking auto-calculated charge, admin-editable override
21. Welcome guide: restructure into cards (logo/status, weather, icons, guest info)
22. ~~ID decline DB exception (Q1) + missing guest notification/reupload flow~~ — DONE
    (notification/reupload built; DB exception itself still needs prod diagnosis, see
    PROD_INSTRUCTIONS.md #1 — no code defect found locally)

## Other fixes
23. Lock/unlock button visible only check-in→checkout time; auto-checkout 30min after
24. Admin-only incidentals charge field (per guest)
25. Auto parking rate calc from per-day property pricing + override
26. Early check-in tiers (8am/12pm) + late checkout rates (authorized/unauthorized, hourly)
27. Show "(X nights)" next to date ranges everywhere
28. Global 12h time audit across all pages
29. "Check out" button: immediate status change + redirect, fix revert-to-menu bug
30. Text alert system: 6 lifecycle messages, globally customizable templates
31. Settings: per-alert checkboxes (owner receives / guest receives)
32. Customizable name + instructions for extra verification steps; name reflected in alert settings
33. ~~Fix: marking deposit paid re-triggers "checked in" status incorrectly (no undo path)~~
    — RESOLVED, no fix needed (undo exists via booking edit page, client already informed)
34. Parking flow: collect make/model + license plate photo when guest opts in

Status: not started (0/33 remaining, 1 resolved as no-op)
