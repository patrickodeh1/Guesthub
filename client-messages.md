# GuestHub — Original Client Messages (Verbatim, Unparaphrased)

Companion to task-list.md. Same numbering. Quotes are copied exactly as
the client wrote them (including typos) from the chat export and uploaded
text files. No rephrasing. If a task has no direct quote, it's marked
"(inferred from context / dev response only)".

---

## PHASE 1 — DESIGN / LAYOUT

1. "The text should appear below the check-in details. The hero image is what we want guests to see first. Welcome guide banner but if one doesn't exist then use property image."

2. "Button at the bottom should say begin check in if all the required stuff is done already if not, it should say, begin registration. If it's begin check-in the button should be green and if it's registeration then blue is fine. Button needs to be bigger and it should have an arrow. Both states of the button should look user-friendly and welcoming."

3. "The steps 12 and three should have a big number for which step that they are on with a circle around it and then a dash separating the other numbers which will be smaller, and this should be centered at the top of the card."

4. "The hero image needs to be static on each screen of the check in process . It looks too plain."

5. "The icons on the welcome guide screen are too close together. They need to have a nice little margin around each of them so that way they appear to be all symmetrical and all the same size."

6. "When they are in a particular guide section, the scrolling icons at the top of the page are too small. They need to be made bigger (think icon size on a smart phone) that's also the size and spacing they need to be for the main menu"

7. "Whichever page they are currently on should be highlighted and slightly bigger in the scrolling menu to get to other pages of the guide"

8. "Double check to make sure that all the header images for each page of the guide are showing up the same size currently, I noticed that the checkout and contact banners is bigger and not the same size as the rest of them."

9. "The back to guide button should be below the information and should say dashboard and be the same width as the page and a different color that stands out (not green or blue) but a subtle color. (For now)"

10. "I also added an image to the gallery on the editor for the welcom page but it doesnt show to the guest, it displays as a broken link, also remove the cards NAME, PROPERTY, PHONE, those arent needed since they are shown/edited/captured on the next page."

11. "Make the name of the category appear underneath each icon in small bold print similar to the size of an app icon on an iPhone"

12. "also the layout needs fixed for the entire site, as im scrolling up and down on my phone the page moves left or right sometimes, even though theres no content there"

13. "aslo some categories have a shaded background behind them remove that (restaturants checkout and bars)"

14. "For door lock the message no lock configured should not appear. If there is no lock configured it should simply show the message that is in the lock category text and then if the lock is configured, it will show the lock and unlock button"

15. ". on each page is also a card that says guest name status and dates and contact. all that infor needs removed. except the contact. that can stay but i feel it should almost be a hover button that appears on every page"

16. "also the wysig editor looks fine, but the spacing when the guest views is too much and doesnt look like it does when i initially entered the info"

17. "smae thing for spacing on the steps. too much spacing and the buttons to navigate get pushed down and those shuld remain static so the user only scrolls the page but doesnt have to scroll the page to get to the button"

18. "also above step by step part or guest guid should just be the property name, no need for the property details or lat/long or edit properrty just the name and the image"

19. "also remove the property name from the image header for the guest guid categories altogether"

20. "on the main page that displays all icons they should be BIGGER and close together, almost touching. theres way too much space"

## PHASE 2 — BACKEND / DATA

21. "i also need to be able to tell guests that are here from the united states to upload state issued id and not passport, and only allow passports from international guests." ... "i guess let them choose? but i dont want USA people to select passport. is their a way to detect if the image is a passport then skip the back part image portion?" ... "if not then i will choose what type of id in the setup of new guest" (dev: "Alright that will be better than letting the guest choose")

22. "use these images for the photo id place holders and make it so that only a camera can be used (no upload from camera roll option)"

23. "also when guest uploads picture of their passport, there is no back of passport, so it only needs to take a front of picture of the passport."

24. "When guest uploads their ID, it needs to say right on the image or camera take a picture of the front of ID and then when it goes to the back of ID needs to say take picture of back of ID because right now it's unclear."

25. "then next is upload id (which i changed in admin to id uploaded on back end but it still made me take a picture.) page should say upload image of front id and there shuold be an icon image of an id front, then they tap that and it opens their camera with a border that they need to align the id in, then the same thing when done that is committed and then the back of id is done the same manner. then both images appear with option to retake either one."

26. "before you send the update please add in there an exception that i can check off for any booking to where they dont have to submit their ID. i will use this during the changeover. currently i am collecting IDs on the platform and if they already uploaded the ID then they wont even see the pages about the ID, or they can see the page but that the ID has already been received. either way, i dont want them to have to do it twice"

27. "for categories i need a way to drag and drop them to arrange the order" ... "on the settings page where i enter a new category you cannot, but on each property you can"

28. "last thing the Lock box code (for the property, if theres a lockbox attached to property) needs to be stored in the database and acesible via quickcode because it will need to be changed frequently and instead of changing the steps, we will just edit the property and update the code there"

29. "also id should pop up in a window and not a new tab"

30. "can you please fix those in the next update... i cannot zoom in on the image"

31. "ok the ID is blurry and theres alot of background behind the ID"

32. "the guest details needs a way better layou, theres way too mcuh going onther and it looks super confusing, there info i dont need and thers infor i need to see at a glance, and its bad, please make a better layou but thats not priorotiy."

## PHASE 3 — BUGS

33. "now the lock actually works while in front of the door but the status told me locked when i unlock it saud unlocked and then i locked it and then it says command sent and then it said locked but the door wasnt actually locked so i had to press the button again 2 additonal times."

34. "Also, I see a glitch when I first assign the guest to their booking and then I select the parking is needed. It's not saving it the first time and then it ends up needing edited and then it saves it the second time." — **DONE**

35. "The status of the bookings needs to change automatically also any information that the guest inputs will need to update currently doesn't look like it's updating so this includes like the times of their check-in and check out and then if they need parking or not, and then the status of their ID being submitted"

36. "There has been issues with verification of location, even though the guest is at the location. The GPS is saying they are not. I know there's an override which does work, but please look at that code to make sure and see if there might be a better way to code it to where they don't experience that issue if they are at the property"

37. "The try again button doesn't work after GPS has been overwritten. The guest manually has to refresh their browser." ... "also for overriding once I override, can you have it send a command to their browser to refresh it automatically so that they don't have to refresh it themselves"

38. "On step two of three the email address does not check for proper formatting to let the guest know."

39. "Also, the time field needs to be required, and it should be a selectable time not a blank text field. Selectable range should only have half hour increments and not every single minute"

40. "Also, I need to be able to delete guests and archive them from the booking and also while you're at it, change the word bookings to guests"

41. "I also added an image to the gallery on the editor for the welcom page but it doesnt show to the guest, it displays as a broken link" — **DONE**

42. (inferred from context / dev response only — cleanup of pre-existing broken image paths, confirmed handled as manual client re-save post-deploy) — **DONE**

## PHASE 4 — STATUS FLOW REBUILD

43. "The status of the bookings needs to change automatically" + full status list from guest_hubu.txt:
"Statuses / Pending (once I enter the info) ... Pre-Checkin Complete (once the guest has entered their details and uploads their ID) ... Awaiting Deposit ... Guest approved ... Pending Check in ... Currently Hosting ... Checked out"

44. "Then I will let them know to make their deposit and once I verify it's been made I will update the booking and the status will be Guest approved"

45. "The guest will see these status messages when they revisit the link: [full message copy in guest_hubu.txt — see that file for exact wording per stage] ... These messages should be customizable layer, but for now it's okay for static text just to get the site done quicker."

46. "It's not updating guest status to checked out after they completed the process and it needs to"

47. "Upon checking after the day after checkout was indicated it's still showing me the checkout message I need for this page to expire and it takes the user back to the homepage or better yet a message page that says thank you for staying. We appreciate it if they would like to stay with us again please contact us directly for a discount"

48. "Also on checking out on the day of checkout, there should be a big button that says check out or something... they should still be able to access the menu until the checkout time of the unit in local time has passed once that time hits then the only thing that they can see is the checkout button page"

49. "I need to get text notifications once a guest has completed their pre-check-in and uploaded their ID."

50. "Also a notification after the guest has checked in."

51. "And a notification once the guest has checked out"

## PHASE 5 — DASHBOARD REBUILD

52. "I want to see today's check-in or if there's not any check-in today the next upcoming ones I only want to see the next check-in at each property. Each property should be on its own card"

53. "it should say checking in today if it's today and that should be highlighted bold if they are checking in in a couple of days, it should say checking in in like three days or one day or whatever, but not be as bold or highlighted"

54. "then it should say the guest name the phone number their status should be a focal point of the card"

55. "if there's any thing that's required of them that should be indicated by bullet points guest needs to upload ID or whatever if there are no items that need completed, it should say guest is ready for check-in and then their status but if the guest is checked in already, the guest is ready for check-in. Shouldn't even appear in text."

56. "If there's another check in the next day, once the current guest has checked in, it should indicate a status that they are currently hosting and then change the status to their checkout date"

57. "Other ideas for the guest dashboard will be things we need to do be able to view at a glance so please give me suggestions what you think those might be"

58. "Need to get the battery percentage of August locks on the dashboard. Separate card that says smart lock status for each property (locked or unlocked) and the current battery percentage."

59. "the only thing i did differnt in the type of lock i put August. and in the properties on the seam page it says august_lock so im not sure if i need to do that spelling" (dev: "should be august_lock")

## PHASE 6 — CONTENT / MISC

60. "when i highlight it should give me a pop up that asks for a link on this site or outside url. if link on this site, i should be able to select the page to link to" ... "it should be using the slug. so like guesthub.us/checkin/pool or whatever" ... "why is it so long?" (dev: "It has to Carry the booking ID... That was how the route was built by the initial developer.")

61. "this editor should be a full blown thing i have never seen where you have to keep adding features. every editor i have ever used already has these. thats what i would like"

62. "See about an api to add a category for local events happening that I can specify a ZIP Code and a perimeter or latitude longitude, and then like a certain mile radius"

63. "Also, I would like to indicate the temperature and current weather condition on the header page so guests can see it no matter which page they're on"

64. "Any dates that appear should be abbreviated months and should not list the month twice for instance, if their check-in is July 1 and July 4 is their checkout. It should say July 1 through the fourth. Without repeating July."

## PHASE 7 — AUTH REWORK

65. "i need to be able to have an automatic url sent to the guest for certain messages like checkout, amenities, etc. Where it will take them to the direct page... for instance https://guesthub.us/checkin/checkout"

66. "then if they have already completed the process and are checked in then they will remain logged in"

67. "and ban access at the end" / (see also Phase 4, item 48, checkout access cutoff)

68. (inferred — client did not specify beyond wanting more than "a simple token in the URL"; original task-list note, no direct quote found in provided chat)

69. "but in cases where its the precheck in steps needed to complete like upload ID, etc, they can login using their phone number and email together as a combination so that the correct dataset can be viewed"

70. (inferred from dev/task-list context — same underlying session mechanism as 65/66/69, not a separately-worded client ask)

71. "Also, I need to be able to disallow access if for some reason, the guest hasn't completed another step that's not associated with this guest guide system with a reason why they cannot access the site. This reason will be pre-selectable but at first you can just use a text box so that I can type in the reason manually"

72. "For step two, it will check their GPS location and again it needs to be just an icon with a map with some text that I can customize for that message. Then they will allow their location and it will show them the next step."

73. "The first step should be shown always before verifying the GPS location because it has the address and how to get to the building but only show the first step if it's within one hour of their check-in time. So their check-in time is whatever specified on their account and if nothing is specified the check-in default should be set for each property in the local time"

74. "i need to ask the guest their desired check in and check out times and store those and then authorize a time to check in for the timer portion to work correctly."

75. (inferred — no direct client quote resolving this; flagged as open in original task list, no follow-up found in chat)

76. "if the guest has registered and they go back to that same link, it should be like a homepage that says either their guest guide or if not then it should be the first page that is currently shown with a begin check-in button."

77. "The welcome text should be shown when registering."

