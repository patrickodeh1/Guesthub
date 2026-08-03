#!/bin/bash
# Run from ~/Guesthub and paste the full output back.
# This gathers everything needed to write real, codebase-accurate implementation steps
# instead of generic Laravel guesses.

cd ~/Guesthub

echo "=================================================="
echo "1. ROUTES (guest + admin)"
echo "=================================================="
cat routes/web.php

echo "=================================================="
echo "2. GUEST CONTROLLER (full file)"
echo "=================================================="
cat app/Http/Controllers/GuestController.php

echo "=================================================="
echo "3. ADMIN CATEGORY CONTROLLER (full file)"
echo "=================================================="
cat app/Http/Controllers/Admin/CategoryController.php

echo "=================================================="
echo "4. ADMIN PROPERTY LOCK CONTROLLER (full file)"
echo "=================================================="
cat app/Http/Controllers/Admin/PropertyLockController.php

echo "=================================================="
echo "5. ADMIN BOOKING CONTROLLER (full file)"
echo "=================================================="
cat app/Http/Controllers/Admin/BookingController.php

echo "=================================================="
echo "6. BOOKING MODEL"
echo "=================================================="
cat app/Models/Booking.php

echo "=================================================="
echo "7. SEAM SERVICE"
echo "=================================================="
cat app/Services/SeamService.php

echo "=================================================="
echo "8. ADMIN DASHBOARD VIEW"
echo "=================================================="
cat resources/views/admin/dashboard.blade.php

echo "=================================================="
echo "9. GUEST SHOW VIEW (main guest landing/registration page)"
echo "=================================================="
cat resources/views/guest/show.blade.php

echo "=================================================="
echo "10. GUEST CATEGORY VIEW"
echo "=================================================="
cat resources/views/guest/category.blade.php

echo "=================================================="
echo "11. GUIDE PANEL / STEP WIZARD COMPONENTS"
echo "=================================================="
cat resources/views/components/guide-panel.blade.php
echo "--------------------------------------------------"
cat resources/views/components/step-wizard.blade.php

echo "=================================================="
echo "12. ADMIN LAYOUT (for nav restructure task)"
echo "=================================================="
cat resources/views/layouts/admin.blade.php

echo "=================================================="
echo "13. INSTRUCTION STEP CONTROLLER + MODEL"
echo "=================================================="
cat app/Http/Controllers/Admin/InstructionStepController.php
echo "--------------------------------------------------"
find app/Models -iname "*InstructionStep*" -exec cat {} \;

echo "=================================================="
echo "14. CATEGORY / CONTENT MODELS"
echo "=================================================="
find app/Models -iname "*Category*" -o -iname "*Content*" | xargs -I{} sh -c 'echo "--- {} ---"; cat {}'

echo "=================================================="
echo "15. MIGRATIONS TABLE LIST (to see current schema shape)"
echo "=================================================="
ls -la database/migrations/ | tail -30

echo "=================================================="
echo "16. bookings TABLE SCHEMA (search migrations mentioning bookings)"
echo "=================================================="
grep -rl "Schema::table('bookings'\|Schema::create('bookings'" database/migrations/ | xargs -I{} sh -c 'echo "--- {} ---"; cat {}'

echo "=================================================="
echo "17. property_locks TABLE SCHEMA"
echo "=================================================="
grep -rl "property_locks" database/migrations/ | xargs -I{} sh -c 'echo "--- {} ---"; cat {}'

echo "=================================================="
echo "18. RICH TEXT EDITOR usage (for link-picker + spellcheck tasks)"
echo "=================================================="
grep -rln "trix\|tiptap\|quill\|ckeditor\|tinymce" resources/ | head -20

echo "=================================================="
echo "19. ACTIVITY LOG SERVICE (for the Seam-vs-guest-name bug)"
echo "=================================================="
find app -iname "*ActivityLog*" -exec cat {} \;

echo "=================================================="
echo "20. LOGS ADMIN VIEWS (where 'Seam' shows instead of guest name)"
echo "=================================================="
cat resources/views/admin/logs/index.blade.php
echo "--------------------------------------------------"
cat resources/views/admin/logs/show.blade.php
