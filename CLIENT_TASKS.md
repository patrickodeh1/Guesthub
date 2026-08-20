# CLIENT_TASKS.md — Original Client Wording

This file preserves the client's own words for each numbered task in
`TASKS.md`, unedited. Our interpretation, questions, and implementation notes
live in `TASKS.md` — this file exists purely so anyone (human or AI) can go
back to what was actually said, without relying on a summary that might have
drifted from the original ask.

When the client replies/clarifies a task later, **append** their reply under
that task's entry (with a date if possible) rather than editing the original
down. Keep the history, don't overwrite it.

---

## Critical fixes (original batch)

**1. Session/idle logout**
> "every time I go back to a page after a while, it says that page doesn't
> exist and then I have to go back to the admin which also is a problem I
> need to stay logged in always if that option is selected."

**2. Root domain shows admin login**
> "But it's also a problem that I noticed if you just go to the domain URL,
> you see the admin page so that needs changed to like a nice friendly coming
> soon. Page with highlights of what the website will do and what it's
> capable of and benefits and then also pay registration for early access
> that captures name what their industry is if they are a host or whatever
> phone number and email."
>
> Follow-up clarification (when task started): "draft it as a normal page
> read the code to understand what the site does its a property management
> platform property owners or guest can sign up fetch the site logo and use
> for the page and for payment no need for now just add the admin contact
> number or something to learn more or make a reservation or an email form is
> much easier and better just a contact form so you can build this and when
> we handle the resend task we can do that to receive users input from that
> early access form via the admin's email so it is a straight forward task"
>
> Further note: "no need for text fallback but proceed" (re: logo fallback
> in the header).

**3. Inner iframe / scroll on steps pages**
> "you still have the inner frame on the steps pages and that needs to go
> away so that the user is only scrolling one page. Currently they have to
> scroll down to see the text and that should not be happening."

**4. Text alerts not working**
> "text alerts not working"
>
> Later clarification: "on the task he said sms isnt working sms is
> currently configured for admin only it works it is because he did not
> configure the twilio correctly hence why he said not working"

**5. Check-in/check-out time mislabeled — SUPERSEDED, see below**
> Original: "recommend check in time is check in time for the system. You
> currently have it as check out time"
>
> Client follow-up (this is the ACTUAL task 5 scope going forward — the
> original wording above turned out to be about something else / was
> already addressed separately by the client directly with a prior
> assistant; do not treat the "mislabeled" framing as the task):
> "we need both email and text notifications working and user selectable to
> receive email or text alerts. text are prefered. the checkin time is 4pm
> and checkout time is 10am. that is what i have set in the dbase currently."
>
> Client explicitly noted: "the task isnt a mislabelled checkin time or
> misslabeled anything and not in admin"

**6. Pending check-in page missing property image header**
> "pending check in page doesn't have the property image header"

**7. Checkout time window**
> "checkout time should include 7am and only go thru 2pm"

**8. Front/back ID flip + independent approval — DONE (merged with 22)**
> "need to be able to flip between front and back of id and approve both.
> This will trigger a text alert to be sent to the guest letting them know
> their id picture needs to be resubmitted and if one was accepted in the
> other one wasn't the other one will still remain in the system, prompting
> the guest when they log back in to only upload, whichever one was
> rejected"
>
> Confirmation when task started: "i think that is in place only thing not
> in place is the fron and back as we handle it as 1 approval you can handle
> this together so we can trigger notifications"

**9. ID capture auto-fill + auto-capture**
> "need to make sure the image of id takes up the entire square automatically
> and will not allow picture to be taken unless it is. Once its taking up the
> whole square then the camera will need to automatically capture the image.,
> so there's no need for a capture button"

**10. ID photos showing overlay/haze**
> "guest IDs are showing up with a Hayes film over each image. I thought it
> was maybe one person's camera but it's showing up that way pretty much
> every guest. How do we avoid this?"

**11. ID photos blurry, blur detection not working**
> "also guest images are blurry, and the system is supposed to be detecting
> that everything is readable and not blurry"

**12. Checkout not updating day-before + warning before final checkout press**
> "guest complaints that checkout is not updating the day before. Remember,
> they should be able to see the checkout button along with the instructions
> and it should alert them not to press the I've checked out button on the
> last page until they have completely checked out so like a warning or
> something should should pop up letting them know that they will lose
> access to the guest hub site and it also will alert the cleaning staff
> that it's OK to come in"

**13. Admin guest list: truncation, square image, full name**
> "on the admin guest view list page I can't see the full guest name the
> hero image is also a square and it should be a rectangle and I need to see
> the full details. Everything is truncated."

**14. Remove guest ID (rename), booking ID only on full detail**
> "remove the guest ID. I don't need that. I only need the booking ID to
> identify the guest, but I also don't need to see the booking ID on any
> pages except for maybe the full details pages but any list pages I don't
> need to see that information, all I need to see is the guest name the
> dates the property and then I need a button that has action menu so when I
> tap the button I'll be able to do quick actions so would that being done? I
> won't need the view or edit buttons on any of the screens either."
>
> Clarification (later session): "guest id is same as booking id i renamed
> it just rename it to booking id do not remove it"

**15. Action-menu replacing view/edit buttons**
> (Same message as 14 — "I need a button that has action menu so when I tap
> the button I'll be able to do quick actions... I won't need the view or
> edit buttons on any of the screens either.")

**16. Guest list landing page decluttered**
> "on the guest view screen where it listed all the guests I don't need all
> the other crap at the top that says number of gas number of check-in. I
> don't need any of those cards. What I need is to be able to add a guest
> and then see the upcoming guests or the guest that are currently hosting
> and anything that needs my attention should be highlighted as well so if
> there's a guest checking in two months later, I don't need to see that on
> this landing page, but if the guest checking in two months later has just
> submitted his ID and needs verified then I should see action item or maybe
> a card that says action items needed and then I tap that and then once I
> take those actions it clears the alert. The page is just too cluttered,
> but I do need access to the priorities for the business."

**17. Single unified search bar**
> "I don't need to filter out the guest to remove that part. You can put
> that in like a search menu to where I just have one search bar where I can
> search the guest and then I start typing in the name or the reservation ID
> or anything and it pulls up that guest immediately."

**18. Action menu = all quick actions incl. status changes**
> "the action items that I will need on that menu will be the ones that are
> already there along with the view and also the status changes so basically
> any of the hot buttons that I can do in the edit screen, I should be able
> to do from that menu"

**19. 12-hour time format in guest details**
> "when I'm viewing the guest details, you still have the 24 hour times
> being reported instead of the 12 hour times so I need a.m. or p.m. for the
> checkout time and check-in times that the guest desires"

**20. Parking auto-calculated + editable**
> "I need The parking to be calculated. And also shown if the guest needs
> parking, it will show the amount that they should be charged, but I will
> need the option to edit that just in case it's incorrect."

**21. Welcome guide cards restructure**
> "make the cards on the welcome guide like you have for preregistration.
> Guest hub logo and status on one card then weather in next card. Then
> remove welcome guide and subsequent text so next card just has the icons.
> Then last card has the guest information (name and check out date/time)"

---

## Other fixes (original batch)

**23. Lock/unlock button time-gated + auto-checkout**
> "Make the lock and unlock button only visible between the check-in time
> and the checkout time. This is just a fallback in case they don't go
> through the steps to check out the system should automatically check them
> out 30 minutes after the checkout time, but the lock and unlocked door
> button expires exactly at the checkout time."

**24-26. Incidentals charge, parking rate calc, early-checkin/late-checkout tiers**
> "ADD Field for the admin use only of how much we charge the guest for
> incidentals it will be different for each guest. Also, for guests with
> parking, we need to calculate automatically the rates accordingly those
> rates will be specified in the property management pages each day of the
> week will be potentially a different price. This automatically calculated
> price will need to go into the guests details for the admin only to see on
> the backend and we should be able to override the auto calculated price
> just in case it's wrong. Also, we have different tears for early check-in
> so we need to be able to specify those in the property details as well.
> The tears for early check-in is 8 AM, 12 noon and then late checkout if
> it's authorized is one rate and if it's unauthorized, it's another rate.
> That rate is charged per hour so in order to calculate that we will need
> to specify on unauthorized checkouts what time the guest actually checked
> out."

**27. Show nights count next to dates**
> "I would like to see number of nights guest is staying after their date on
> any page the dates appear (in brackets)"

**28. Global 12-hour time audit**
> "All times need to be formatted correctly. We use the 12 hour clock. I
> noticed somewhere there is 24 hour time. I can't remember what page"

**29. "Check out" button immediate status change**
> "On check out last button should say "check out" this changes their status
> and show them check out page and they won't be able to view anything else
> now. Currently it takes them back to the main menu and then when clicking
> a menu item it reverts them to all checked out page but this should happen
> right away after updating status"

**30. Text/email alert lifecycle + settings**
> "Need text alert sent to guest once information is updated each time.
> Custom messages set globally
> -Registration received
> -Background check complete
> -Fully approved
> --contains check in time and check out time and confirms parking (this
> also displays when they visit the site before their check in date
> -Time to check in
> -Check in completed
> -Check out completed
>
> In the settings should be alerts to send check boxes for each owner to
> specify which text alerts they want to receive and which ones they want
> the guests to receive"
>
> Client reply during task 5 discussion (applies here too — see task 5
> above): "we need both email and text notifications working and user
> selectable to recieve email or text alerts. text are prefered."

**31. Per-owner alert preference checkboxes**
> (Same message as 30 — "In the settings should be alerts to send check
> boxes for each owner to specify which text alerts they want to receive and
> which ones they want the guests to receive")

**32. Customizable verification step naming**
> "For the extra steps in verification, there will need to be a customizable
> name for that step as well as text instructions that are customize all as
> well, and whatever the name for that step is for registration will need to
> appear in the user settings under text alerts as that stepped name"

**33. Deposit-paid incorrectly flips to checked-in — RESOLVED, no fix needed**
> "I accidentally marked the guest as checked in, even though they're not
> checked in, but you can't undo this, even though it changed the status
> when I marked their deposit as paid it still says guest is checked in"
>
> Client clarification (this task needs no fix): "on 33 i thing i already
> address that" / "on 33 he meant he mustakenly marked a guest checked in
> and no way to undo but there is in the booking edit page i already told
> him that so 33 needs no fix or anything"

**34. Parking vehicle info collection**
> "If a guest selects, yes they like to get parking then we will need to
> know the make and model of their vehicle and take a picture of their
> license plate"

---

## Added mid-session (not in original batch)

**22. ID decline DB error + guest notification — DONE (merged with 8)**
> "this is a laravel error when declining the ID, upon entering notes and
> submitting i getdatabase query exception error. also i dont knowwhat
> happens after this, but the desired rult is to text/email the guest and
> tell them thei id wasnt approved and that the reason why is what was typed
> in the reason for rejecting box. this must also appear when the guest
> attempts to access the site again. this same message that was emailed
> should also state that they need to reupload the id because it was
> rejected and the reason it was rejected."
>
> "not this error above when declining an id isnt noticed locally just prod
> and since i dont have prods access just diagnose if there is any reason
> for it to fail if not leave it i will ask him to provide the exact error
> when online we would just note down any task that we need some access or
> confirmations or certain things are unclear"

## Session-level clarifications (apply across multiple tasks)

> "on email we would be using resend"
>
> "also like the migration status and things i need to do in prod or ask the
> client create an instruction file with them in it and push it"
>
> "before you clean up any deadcode or things confirm fully that it is
> useless so nothing breaks"
>
> "please reread the task or ask for my interpretation of what the client
> means before making any change as you tend to miss it"
