<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GuestController;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/webhooks/seam', [App\Http\Controllers\SeamWebhookController::class, 'handle'])->name('webhooks.seam');

Route::prefix('guest/{booking_id}/{token}')->name('guest.')->group(function () {
    Route::get('/', [GuestController::class, 'show'])->name('show');
    Route::post('/identity', [GuestController::class, 'submitIdentity'])->name('identity');
    Route::post('/parking', [GuestController::class, 'parking'])->name('parking');
    Route::post('/verify-gps', [GuestController::class, 'verifyGps'])->name('gps');
    Route::post('/confirm-checkin', [GuestController::class, 'confirmCheckin'])->name('confirm-checkin');
    Route::post('/confirm-checkout', [GuestController::class, 'confirmCheckout'])->name('confirm-checkout');
    Route::post('/unlock-door/{lock}', [GuestController::class, 'unlockDoor'])->name('unlock-door');
    Route::post('/lock-door/{lock}', [GuestController::class, 'lockDoor'])->name('lock-door');
    Route::get('/lock-status/{lock}', [GuestController::class, 'lockStatus'])->name('lock-status');
    Route::get('/gps-status', [GuestController::class, 'gpsStatus'])->name('gps-status');
    Route::get('/guide/{category:slug}', [GuestController::class, 'category'])->name('category');
});

Route::middleware(['auth', 'role'])->prefix('admin')->name('admin.')->group(function () {

    // ─── Dashboard ───────────────────────────────────────────────────────────
    Route::get('/', DashboardController::class)->name('dashboard');

    // ─── Tour ────────────────────────────────────────────────────────────────
    Route::post('tour/complete', function () {
        request()->user()->forceFill(['admin_tour_completed_at' => now()])->save();
        ActivityLogService::admin('tour_completed', request()->user()->name.' completed the admin onboarding tour.', 'users', ['severity' => 'success']);
        return response()->json(['ok' => true]);
    })->name('tour.complete');

    Route::post('tour/restart', function () {
        request()->user()->forceFill(['admin_tour_completed_at' => null])->save();
        ActivityLogService::admin('tour_restarted', request()->user()->name.' restarted the admin tour.', 'users');
        return back()->with('success', 'The interactive tour will appear again on your next visit.');
    })->name('tour.restart');

    Route::post('tour/dashboard/complete', function () {
        request()->user()->forceFill(['dashboard_tour_completed_at' => now()])->save();
        return response()->json(['ok' => true]);
    })->name('tour.dashboard.complete');

    Route::post('tour/dashboard/restart', function () {
        request()->user()->forceFill(['dashboard_tour_completed_at' => null])->save();
        return response()->json(['ok' => true]);
    })->name('tour.dashboard.restart');

    // ─── Static pages ────────────────────────────────────────────────────────
    Route::view('guide', 'admin.guide')->name('guide');
    Route::view('security', 'admin.security')->name('security');

    // ─── Guest Guide (per-property categories) ──────────────────────────────
    Route::get('guest-guide', [PropertyController::class, 'guideIndex'])->name('guest-guide.index');
    Route::get('properties/{property}/categories', [PropertyController::class, 'guide'])->name('guest-guide.show');

    // ─── Properties ──────────────────────────────────────────────────────────
    Route::resource('properties', PropertyController::class)->except(['show']);
    Route::post('properties/{property}/checkout-time', [PropertyController::class, 'updateCheckoutTime'])->name('properties.checkout-time');

    // ─── Guests / Bookings ────────────────────────────────────────────────────
    Route::resource('guests', BookingController::class)->parameters(['guests' => 'booking']);
    Route::get('guests/{booking}/preview/{state}', [BookingController::class, 'preview'])->name('guests.preview');
    Route::post('guests/{booking}/override-checkin', [BookingController::class, 'overrideCheckin'])->name('guests.override');
    Route::post('guests/{booking}/override-checkout', [BookingController::class, 'overrideCheckout'])->name('guests.override-checkout');
    Route::post('guests/{booking}/override-gps', [BookingController::class, 'overrideGps'])->name('guests.override-gps');
    Route::post('guests/{booking}/archive', [BookingController::class, 'archive'])->name('guests.archive');
    Route::post('guests/{booking}/unarchive', [BookingController::class, 'unarchive'])->name('guests.unarchive');
    Route::post('guests/{booking}/mark-id', [BookingController::class, 'markIdReceived'])->name('guests.mark-id');
    Route::post('guests/{booking}/approve', [BookingController::class, 'approveBooking'])->name('guests.approve');
    Route::post('guests/{booking}/background-check', [BookingController::class, 'markBackgroundCheckComplete'])->name('guests.background-check');
    Route::post('guests/{booking}/deposit-verified', [BookingController::class, 'markDepositVerified'])->name('guests.deposit-verified');
    Route::post('guests/{booking}/update-status', [BookingController::class, 'updateStatus'])->name('guests.update-status');
    Route::post('guests/{booking}/decline', [BookingController::class, 'declineBooking'])->name('guests.decline');
    Route::put('guests/{booking}/welcome-message', [BookingController::class, 'updateWelcomeMessage'])->name('guests.welcome-message');
    Route::get('guests/{booking}/photo-id', [BookingController::class, 'photoId'])->name('guests.photo-id');
    Route::get('guests/{booking}/photo-id-back', [BookingController::class, 'photoIdBack'])->name('guests.photo-id-back');
    Route::get('guests/{booking}/photo-id/view', [BookingController::class, 'photoIdView'])->name('guests.photo-id-view');
    Route::get('guests/{booking}/photo-id-back/view', [BookingController::class, 'photoIdBackView'])->name('guests.photo-id-back-view');

    // ─── Categories ───────────────────────────────────────────────────────────
    Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::post('categories/assign', [CategoryController::class, 'assign'])->name('categories.assign');
    Route::get('categories/{category}/preview/{property}', [CategoryController::class, 'preview'])->name('categories.preview');

    // ─── Content / Pages ──────────────────────────────────────────────────────
    Route::get('content/{property}/{category}/edit', [ContentController::class, 'editPage'])->name('content.edit');
    Route::put('content/{property}/{category}', [ContentController::class, 'updatePage'])->name('content.update');
    Route::put('content/{property}/{category}/assignment', [ContentController::class, 'updateAssignment'])->name('content.assignment.update');
    Route::get('content/{property}/amenities', [ContentController::class, 'amenitiesIndex'])->name('amenities.index');
    Route::get('content/{property}/amenities/create', [ContentController::class, 'createAmenity'])->name('amenities.create');
    Route::post('content/{property}/amenities', [ContentController::class, 'storeAmenity'])->name('amenities.store');
    Route::get('amenities/{amenity}/edit', [ContentController::class, 'editAmenity'])->name('amenities.edit');
    Route::put('amenities/{amenity}', [ContentController::class, 'updateAmenity'])->name('amenities.update');
    Route::delete('amenities/{amenity}', [ContentController::class, 'deleteAmenity'])->name('amenities.destroy');

    // ─── Instruction Steps ───────────────────────────────────────────────────────
    Route::get('properties/{property}/steps', [App\Http\Controllers\Admin\InstructionStepController::class, 'forProperty'])->name('instructions.show');
    Route::post('instructions/reorder', [App\Http\Controllers\Admin\InstructionStepController::class, 'reorder'])->name('instructions.reorder');
    Route::delete('instructions/images/{image}', [App\Http\Controllers\Admin\InstructionStepController::class, 'destroyImage'])->name('instructions.images.destroy');
    Route::resource('instructions', App\Http\Controllers\Admin\InstructionStepController::class)->except(['show']);

    // ─── Media Library ───────────────────────────────────────────────────────────
    Route::get('media', [App\Http\Controllers\Admin\MediaController::class, 'index'])->name('media.index');
    Route::get('media/picker', [App\Http\Controllers\Admin\MediaController::class, 'picker'])->name('media.picker');
    Route::post('media/folders', [App\Http\Controllers\Admin\MediaController::class, 'storeFolder'])->name('media.folders.store');
    Route::delete('media/folders/{folder}', [App\Http\Controllers\Admin\MediaController::class, 'destroyFolder'])->name('media.folders.destroy');
    Route::post('media/files', [App\Http\Controllers\Admin\MediaController::class, 'storeFile'])->name('media.files.store');
    Route::delete('media/files/{file}', [App\Http\Controllers\Admin\MediaController::class, 'destroyFile'])->name('media.files.destroy');
    Route::post('properties/{property}/duplicate', [App\Http\Controllers\Admin\PropertyController::class, 'duplicate'])->name('properties.duplicate');
    Route::post('properties/{property}/locks', [App\Http\Controllers\Admin\PropertyLockController::class, 'store'])->name('properties.locks.store');
    Route::put('properties/{property}/locks/{lock}', [App\Http\Controllers\Admin\PropertyLockController::class, 'update'])->name('properties.locks.update');
    Route::delete('properties/{property}/locks/{lock}', [App\Http\Controllers\Admin\PropertyLockController::class, 'destroy'])->name('properties.locks.destroy');
    Route::post('media/bulk-move', [App\Http\Controllers\Admin\MediaController::class, 'bulkMove'])->name('media.bulk-move');
    Route::delete('media/bulk-delete', [App\Http\Controllers\Admin\MediaController::class, 'bulkDelete'])->name('media.bulk-delete');

    // ─── Settings ─────────────────────────────────────────────────────────────
    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');


    // ─── Users / Team ─────────────────────────────────────────────────────────
    Route::middleware('role:owner')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });

    // ─── Activity Logs ────────────────────────────────────────────────────────
    Route::middleware('role:owner,manager')->group(function () {
        Route::get('logs', [LogController::class, 'index'])->name('logs.index');
        Route::get('logs/{log}', [LogController::class, 'show'])->name('logs.show');
    });

    // ─── Global Search ────────────────────────────────────────────────────────
    Route::get('search', function (\Illuminate\Http\Request $request) {
        $q = trim($request->q ?? '');
        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $guests = \App\Models\Booking::with('property')
            ->where(fn ($query) => $query
                ->where('guest_name', 'like', "%{$q}%")
                ->orWhere('booking_id', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
            )->take(5)->get()
            ->map(fn ($b) => [
                'type'  => 'Guest',
                'label' => $b->guest_name.' ('.$b->booking_id.')',
                'url'   => route('admin.guests.show', $b),
            ]);

        $properties = \App\Models\Property::where('name', 'like', "%{$q}%")->take(4)->get()
            ->map(fn ($p) => [
                'type'  => 'Property',
                'label' => $p->name,
                'url'   => route('admin.properties.edit', $p),
            ]);

        $categories = \App\Models\Category::where('title', 'like', "%{$q}%")->take(3)->get()
            ->map(fn ($c) => [
                'type'  => 'Category',
                'label' => $c->title,
                'url'   => route('admin.categories.edit', $c),
            ]);

        $users = \App\Models\User::where(fn ($query) => $query
            ->where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
        )->take(3)->get()
            ->map(fn ($u) => [
                'type'  => 'User',
                'label' => $u->name.' ('.$u->email.')',
                'url'   => route('admin.users.show', $u),
            ]);

        return response()->json([
            'results' => $guests->concat($properties)->concat($categories)->concat($users)->values(),
        ]);
    })->name('search');
});
Route::get('/img/{path}', [App\Http\Controllers\ImageController::class, 'show'])->where('path', '.*');
