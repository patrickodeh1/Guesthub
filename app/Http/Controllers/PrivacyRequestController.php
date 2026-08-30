<?php

namespace App\Http\Controllers;

use App\Models\PrivacyRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PrivacyRequestController extends Controller
{
    public function index()
    {
        return view('public.privacy-request', [
            'contactEmail' => Setting::getValue('contact_email', 'needhelp@guesthub.us'),
        ]);
    }

    public function contact()
    {
        return view('public.contact', [
            'contactEmail' => Setting::getValue('contact_email', 'needhelp@guesthub.us'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'request_type' => ['required', 'in:access,correction,deletion,portable_copy,opt_out,appeal'],
            'details' => ['nullable', 'string', 'max:4000'],
        ]);

        $data['status'] = 'new';
        $data['ip_address'] = $request->ip();

        $privacyRequest = PrivacyRequest::create($data);

        $adminEmail = Setting::getValue('contact_email', 'needhelp@guesthub.us');
        if ($adminEmail) {
            $phoneDisplay = $privacyRequest->phone ?? 'Not provided';

            // The request is already saved to the DB above regardless of
            // whether this notification email succeeds. A transient SMTP
            // failure should never turn into a 500 for the guest, or block
            // them submitting a privacy/deletion request -- log and move
            // on, same pattern as GuestAlertService's mail sends.
            try {
                Mail::raw(
                    "Privacy request received\n\n".
                    "Name: {$privacyRequest->name}\n"
                    ."Email: {$privacyRequest->email}\n"
                    ."Phone: {$phoneDisplay}\n"
                    ."Request type: {$privacyRequest->request_type}\n"
                    ."Details:\n{$privacyRequest->details}\n",
                    function ($message) use ($adminEmail) {
                        $message->to($adminEmail)
                            ->subject('Privacy request received');
                    }
                );
            } catch (\Throwable $e) {
                Log::error('Privacy request admin notification email failed: '.$e->getMessage());
            }
        }

        return redirect()->route('privacy-request')->with('success', 'Your privacy request has been received. We will review it and respond as soon as possible.');
    }
}
