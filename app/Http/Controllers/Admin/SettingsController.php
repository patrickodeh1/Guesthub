<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Services\GuestAlertService;
use App\Services\MediaService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings', [
            'settings' => [
                'gps_radius_meters' => Setting::getValue('gps_radius_meters', 150),
                'site_logo' => Setting::getValue('site_logo'),
                'favicon' => Setting::getValue('favicon'),
                'brand_color' => Setting::getValue('brand_color', '#0f766e'),
                'contact_phone' => Setting::getValue('contact_phone', '+1 555 123 4567'),
                'contact_email' => Setting::getValue('contact_email', 'guestservices@example.com'),
                'default_intro' => Setting::getValue('default_intro', 'Your arrival details and local guide are ready when you are.'),
                'gps_verify_message' => Setting::getValue('gps_verify_message', "It's Go Time!"),
                'lock_message' => Setting::getValue('lock_message', "If you'd like quicker access to the unit, you can download the August Home app."),
            ],
            'alertEvents' => GuestAlertService::EVENTS,
            'alertConfig' => GuestAlertService::config(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'gps_radius_meters' => ['required', 'integer', 'min:25', 'max:5000'],
            'site_logo' => ['nullable', 'image', 'max:2048'],
            'existing_site_logo' => ['nullable', 'string'],
            'favicon' => ['nullable', 'image', 'mimes:ico,png,jpg,jpeg,svg', 'max:512'],
            'existing_favicon' => ['nullable', 'string'],
            'brand_color' => ['required', 'string', 'max:20'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'default_intro' => ['nullable', 'string'],
            'gps_verify_message' => ['nullable', 'string', 'max:500'],
            'lock_message' => ['nullable', 'string', 'max:500'],
        ]);

        if ($request->hasFile('site_logo')) {
            $file = $request->file('site_logo');
            $data['site_logo'] = $file->store('brand', 'public');
            MediaService::register($data['site_logo'], $file->getClientOriginalName(), $file->getSize(), 'Settings');
        } elseif ($request->filled('existing_site_logo')) {
            $data['site_logo'] = $request->input('existing_site_logo');
        } else {
            unset($data['site_logo']);
        }
        unset($data['existing_site_logo']);

        if ($request->hasFile('favicon')) {
            $file = $request->file('favicon');
            $data['favicon'] = $file->store('brand', 'public');
            MediaService::register($data['favicon'], $file->getClientOriginalName(), $file->getSize(), 'Settings');
        } elseif ($request->filled('existing_favicon')) {
            $data['favicon'] = $request->input('existing_favicon');
        } else {
            unset($data['favicon']);
        }
        unset($data['existing_favicon']);

        foreach ($data as $key => $value) {
            Setting::putValue($key, $value);
        }
        ActivityLog::record('settings_updated', 'Brand and system settings were updated.', 'settings');

        return back()->with('success', 'Settings saved.');
    }

    /**
     * Save the guest lifecycle alert templates and per-event send toggles
     * (tasks 30/31). A separate form/route from the main settings save so
     * this large structured section doesn't get tangled up with the
     * brand/logo save, mirroring the pattern used for property-level
     * per-section admin forms elsewhere (e.g. parking rates).
     */
    public function updateAlerts(Request $request)
    {
        $data = $request->validate([
            'alerts' => ['required', 'array'],
            'alerts.*.message' => ['required', 'string', 'max:1000'],
            'alerts.*.guest_sms' => ['nullable', 'boolean'],
            'alerts.*.guest_email' => ['nullable', 'boolean'],
            'alerts.*.admin_sms' => ['nullable', 'boolean'],
            'alerts.*.admin_email' => ['nullable', 'boolean'],
        ]);

        $config = [];
        foreach (GuestAlertService::EVENTS as $key => $meta) {
            $row = $data['alerts'][$key] ?? [];
            $config[$key] = [
                'message' => $row['message'] ?? $meta['default_message'],
                'guest_sms' => (bool) ($row['guest_sms'] ?? false),
                'guest_email' => (bool) ($row['guest_email'] ?? false),
                'admin_sms' => (bool) ($row['admin_sms'] ?? false),
                'admin_email' => (bool) ($row['admin_email'] ?? false),
            ];
        }

        GuestAlertService::putConfig($config);

        ActivityLog::record('guest_alerts_updated', 'Guest lifecycle alert messages and send preferences were updated.', 'settings');

        return back()->with('success', 'Guest alert settings saved.');
    }
}
