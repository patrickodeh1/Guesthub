<?php

namespace App\Http\Controllers;

use App\Models\EarlyAccessLead;
use App\Models\Setting;
use Illuminate\Http\Request;

class EarlyAccessController extends Controller
{
    public function show()
    {
        return view('early-access', [
            'siteLogo' => Setting::getValue('site_logo'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['nullable', 'in:host,guest,other'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        EarlyAccessLead::create($data);

        // TODO: once Resend is wired up, notify the admin by email here as well
        // (see PROD_INSTRUCTIONS.md / TASKS.md — deferred until Resend task).

        return back()->with('success', "Thanks. We've received your info and will be in touch soon.");
    }
}
