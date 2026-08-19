# Session Task List

## Needs clarification / access before starting
- Q1: ID decline DB error — need prod `migrate:status` output OR exact exception text.
- Q2: "Text alerts not working" — need to know which provider (Twilio? etc.), and whether
  it's total failure or intermittent. Check .env/config for provider — will inspect code,
  but can't test sending without prod credentials.
- Q3: Incidentals charge field, parking rate tiers, early check-in tiers (8am/12pm), late
  checkout rates (authorized vs unauthorized) — need exact dollar structure / business rules,
  or just the DB fields + admin UI and you fill in real numbers later?
- Q4: "Coming soon" landing page — need copy/highlights/benefits text, or should I draft it?
- Q5: Early-access registration form — payment required? Which processor (Stripe?).
- Q6: Custom-named verification step — confirm this is the same as existing "extra steps"
  feature seen in migrations (`add_excluded_steps_to_bookings`)?

## Critical fixes
1. Session/"page doesn't exist" after inactivity — investigate session/cache config
2. Root domain shows admin login instead of coming-soon page — NEW landing page (Q4/Q5)
3. Inner iframe/scroll issue on steps pages
4. Text alerts not working (Q2)
5. "Checkout time" mislabeled as "check-in time" in admin
6. Pending check-in page missing property image header
7. Checkout time picker should only allow 7am–2pm
8. Flip front/back ID review + independent approve/reject + resubmit alert
9. ID capture: auto-fill frame + auto-capture, no manual button
10. ID photos showing overlay/haze ("Hayes film") on every guest
11. ID photos blurry — blur detection not working
12. Checkout status not updating day-before / warning before final "checked out" press
13. Admin guest list: full name visibility, rectangular hero image, no truncation
14. Remove guest ID from list views; only booking ID on full detail page
15. Replace view/edit buttons with single action-menu (quick actions)
16. Guest list landing: remove stat cards, add "Add Guest", surface action items only
17. Single unified search bar (name/reservation ID) replacing filters
18. Action menu = all quick actions + status changes from edit screen
19. 24h → 12h time display in guest details
20. Parking auto-calculated charge, admin-editable override
21. Welcome guide: restructure into cards (logo/status, weather, icons, guest info)
22. **[NEW] ID decline DB exception (Q1) + missing guest notification/reupload flow**

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
33. Fix: marking deposit paid re-triggers "checked in" status incorrectly (no undo path)
34. Parking flow: collect make/model + license plate photo when guest opts in

Status: not started (0/34)
