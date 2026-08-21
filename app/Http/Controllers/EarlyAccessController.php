<?php

namespace App\Http\Controllers;

use App\Mail\EarlyAccessAdminNotificationMail;
use App\Mail\EarlyAccessConfirmationMail;
use App\Models\EarlyAccessLead;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

        $lead = EarlyAccessLead::create($data);

        $adminEmail = Setting::getValue('contact_email');
        if ($adminEmail) {
            Mail::to($adminEmail)->send(new EarlyAccessAdminNotificationMail($lead));
        }

        Mail::to($lead->email)->send(new EarlyAccessConfirmationMail($lead));

        return back()->with('success', "Thanks. We've received your info and will be in touch soon.");
    }
}
