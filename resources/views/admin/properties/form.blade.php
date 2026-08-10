<x-admin-layout :title="$property->exists ? 'Edit Property' : 'Add Property'">
    <div class="page-header">
        <div>
            <p class="eyebrow">Property setup</p>
            <h1 class="page-title">{{ $property->exists ? 'Edit property' : 'Add property' }}</h1>
            <p class="page-subtitle">Configure the public guest page, GPS arrival point, maps, images, and all arrival instructions for this unit.</p>
        </div>
        <a href="{{ $returnTo ?? route('admin.properties.index') }}" class="btn-secondary">Back</a>
    </div>

    <form method="post" enctype="multipart/form-data" action="{{ $property->exists ? route('admin.properties.update', $property) : route('admin.properties.store') }}" class="grid gap-6 xl:grid-cols-[1fr_360px]">
        @csrf @if($property->exists) @method('put') @endif
        <input type="hidden" name="return_to" value="{{ $returnTo ?? '' }}">
        <section class="card card-pad">
            <h2 class="section-title">Property details</h2>
            <p class="section-copy">This information appears throughout the guest welcome experience.</p>
            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <label class="field-label">Name <span class="text-red-600">*</span><input name="name" value="{{ old('name', $property->name) }}" required placeholder="Lumina Hotel & Residences" class="input">@error('name')<span class="mt-1 block text-xs text-red-700">{{ $message }}</span>@enderror</label>
                <label class="field-label">Slug<input name="slug" value="{{ old('slug', $property->slug) }}" placeholder="Auto-generated from name" class="input"><span class="field-help">Used in admin references and future public property URLs.</span></label>
                <label class="field-label md:col-span-2">Street address <span class="text-red-600">*</span><input name="address" id="address_input" value="{{ old('address', $property->address) }}" required placeholder="123 Aura Way" class="input"></label>
                <label class="field-label">City <span class="text-red-600">*</span><input name="city" id="city_input" value="{{ old('city', $property->city) }}" required placeholder="City" class="input"></label>
                <label class="field-label">State<input name="state" id="state_input" value="{{ old('state', $property->state) }}" placeholder="ST" class="input"></label>
                <label class="field-label">ZIP<input name="zip" id="zip_input" value="{{ old('zip', $property->zip) }}" placeholder="ZIP Code" class="input"></label>
                <label class="field-label">Phone<input name="contact_phone" value="{{ old('contact_phone', $property->contact_phone) }}" placeholder="+1 555 123 4567" class="input"></label>
                
            </div>
        </section>

        <aside class="grid gap-6">
            <section class="card card-pad">
                <h2 class="section-title">Publishing</h2>
                <p class="section-copy">Disable a property to hide it from new guest setup.</p>
                <label class="mt-5 flex items-center justify-between rounded-xl border border-slate-200 p-4 text-sm font-semibold"><span>Active property</span><input type="checkbox" name="active" value="1" @checked(old('active', $property->active ?? true)) class="rounded border-slate-300"></label>
                <x-media-image-field
                    name="header_image"
                    label="Header image"
                    :value="$property->header_image"
                    preview-class="mt-2 h-36 w-full rounded-xl border border-slate-200 object-cover"
                    help="Upload a polished property hero image."
                />
                <button class="btn-primary mt-6 w-full">Save property</button>
            </section>

            <section class="card card-pad">
                <h2 class="section-title">GPS and maps</h2>
                <p class="section-copy">Coordinates are used for guest location verification.</p>
                <label class="field-label mt-5">Latitude<input name="latitude" id="latitude_input" value="{{ old('latitude', $property->latitude) }}" placeholder="32.715736" class="input" readonly></label>
                <label class="field-label mt-4">Longitude<input name="longitude" id="longitude_input" value="{{ old('longitude', $property->longitude) }}" placeholder="-117.161087" class="input" readonly></label>
                <label class="field-label mt-4">
                    Timezone
                    <select name="timezone" class="input mt-1">
                        @foreach(timezone_identifiers_list() as $tz)
                            <option value="{{ $tz }}" {{ old('timezone', $property->timezone ?? 'America/New_York') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                    </select>
                    <span class="field-help">Auto-detected from coordinates. Override if needed.</span>
                </label>
            </section>
            <section class="card card-pad">
                <h2 class="section-title">Check-out time</h2>
                <p class="section-copy">Guests lose full menu access after this time on their check-out day. Saves independently of the form above.</p>
                <form id="checkout-time-form" class="mt-4 flex items-end gap-3">
                    <label class="field-label flex-1">
                        Check-out time
                        <input type="time" name="checkout_time" id="checkout_time_input" value="{{ $property->checkout_time ?? '11:00' }}" class="input mt-1">
                    </label>
                    <button type="submit" class="btn-secondary">Save</button>
                    <span id="checkout-time-status" class="text-sm font-semibold"></span>
                </form>
                <script>
                    document.getElementById('checkout-time-form').addEventListener('submit', function(e) {
                        e.preventDefault();
                        var statusEl = document.getElementById('checkout-time-status');
                        var value = document.getElementById('checkout_time_input').value;
                        statusEl.textContent = 'Saving...';
                        statusEl.className = 'text-sm font-semibold text-slate-500';
                        fetch("{{ route('admin.properties.checkout-time', $property) }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ checkout_time: value }),
                        }).then(function(response) {
                            if (!response.ok) throw new Error('Save failed');
                            statusEl.textContent = 'Saved';
                            statusEl.className = 'text-sm font-semibold text-emerald-600';
                        }).catch(function() {
                            statusEl.textContent = 'Failed to save. Try again.';
                            statusEl.className = 'text-sm font-semibold text-red-600';
                        });
                    });
                </script>
        </aside>

        <input type="hidden" name="map_embed_url" id="map_embed_url_input" value="{{ old('map_embed_url', $property->map_embed_url) }}">
        <input type="hidden" name="map_directions_url" id="map_directions_url_input" value="{{ old('map_directions_url', $property->map_directions_url) }}">
    </form>

    @if($property->exists)
    {{-- ══════════════════ SMART LOCKS ══════════════════ --}}
    <div class="mb-3 mt-10 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-slate-950">Smart Locks</h2>
        <span class="text-sm text-slate-500">{{ $property->locks->count() }} lock{{ $property->locks->count() === 1 ? '' : 's' }}</span>
    </div>
    <div class="card card-pad mb-10">
        @if($property->locks->count())
            <div class="mb-5 grid gap-3">
                @foreach($property->locks as $lock)
                    <div class="rounded-xl border border-slate-200 p-4" id="lock-row-{{ $lock->id }}">
                        <div class="flex items-center justify-between gap-3" id="lock-view-{{ $lock->id }}">
                            <div>
                                <p class="font-bold text-slate-950">{{ $lock->label }}</p>
                                <p class="text-sm text-slate-500">
                                    Device ID: {{ $lock->seam_device_id }}
                                    @if($lock->manufacturer) &middot; {{ ucfirst($lock->manufacturer) }} @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" class="btn-secondary" onclick="toggleLockEdit({{ $lock->id }})">Edit</button>
                                <form method="post" action="{{ route('admin.properties.locks.destroy', [$property, $lock]) }}" onsubmit="return confirm('Remove this lock?');">
                                    @csrf @method('delete')
                                    <button class="btn-secondary text-red-600">Remove</button>
                                </form>
                            </div>
                        </div>

                        <form method="post" action="{{ route('admin.properties.locks.update', [$property, $lock]) }}" class="mt-4 hidden grid gap-4 md:grid-cols-3" id="lock-edit-{{ $lock->id }}">
                            @csrf @method('put')
                            <label class="field-label md:col-span-1">Label<input name="label" value="{{ $lock->label }}" placeholder="Front Door" class="input"></label>
                            <label class="field-label md:col-span-1">Seam Device ID <span class="text-red-600">*</span><input name="seam_device_id" value="{{ $lock->seam_device_id }}" required placeholder="device_xxxxxxxx" class="input"></label>
                            <label class="field-label md:col-span-1">Manufacturer<input name="manufacturer" value="{{ $lock->manufacturer }}" placeholder="august, yale" class="input"></label>
                            <div class="md:col-span-3 flex gap-2">
                                <button class="btn-primary">Save</button>
                                <button type="button" class="btn-secondary" onclick="toggleLockEdit({{ $lock->id }})">Cancel</button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
            <script>
                function toggleLockEdit(id) {
                    document.getElementById('lock-view-' + id).classList.toggle('hidden');
                    document.getElementById('lock-edit-' + id).classList.toggle('hidden');
                }
            </script>
        @else
            <p class="mb-5 text-slate-500">No locks configured yet for {{ $property->name }}.</p>
        @endif
        <form method="post" action="{{ route('admin.properties.locks.store', $property) }}" class="grid gap-4 border-t border-slate-200 pt-5 md:grid-cols-3">
            @csrf
            <label class="field-label md:col-span-1">Label<input name="label" placeholder="Front Door" class="input"></label>
            <label class="field-label md:col-span-1">Seam Device ID <span class="text-red-600">*</span><input name="seam_device_id" required placeholder="device_xxxxxxxx" class="input"></label>
            <label class="field-label md:col-span-1">Manufacturer<input name="manufacturer" placeholder="august, yale" class="input"></label>
            <div class="md:col-span-3">
                <button class="btn-primary">Add Lock</button>
            </div>
        </form>
    </div>
    @endif

<script>
// Force gmp-place-autocomplete's shadow DOM open so we can style its real
// internal input/focus-ring instead of the host element (which was causing
// the "double box" look — our old CSS styled the host AND the shadow content
// both rendered their own bordered boxes).
(function() {
    if (window.__gmpShadowPatched) return;
    window.__gmpShadowPatched = true;
    const originalAttachShadow = Element.prototype.attachShadow;
    Element.prototype.attachShadow = function(init) {
        if (this.localName === 'gmp-place-autocomplete') {
            const shadow = originalAttachShadow.call(this, { ...init, mode: 'open' });
            const style = document.createElement('style');
            style.textContent = `
                .input-container {
                    border: 1px solid #cbd5e1 !important;
                    border-radius: 0.375rem !important;
                    box-shadow: none !important;
                    background-color: #ffffff !important;
                    padding-left: 0.75rem !important;
                    padding-right: 0.75rem !important;
                }
                .input-container:focus-within {
                    border-color: #2563eb !important;
                    box-shadow: 0 0 0 4px #dbeafe !important;
                }
                .focus-ring {
                    display: none !important;
                }
                input {
                    outline: none !important;
                    box-shadow: none !important;
                    font-size: 0.875rem !important;
                }
            `;
            shadow.appendChild(style);
            return shadow;
        }
        return originalAttachShadow.call(this, init);
    };
})();

async function initAutocomplete() {
    const { PlaceAutocompleteElement } = await google.maps.importLibrary("places");
    const oldInput = document.getElementById('address_input');

    const autocompleteEl = new PlaceAutocompleteElement({
        includedPrimaryTypes: ['street_address', 'premise', 'subpremise'],
    });
    autocompleteEl.id = 'address_input_new';
    oldInput.type = 'hidden';
    oldInput.parentNode.insertBefore(autocompleteEl, oldInput);

    if (oldInput.value) {
        autocompleteEl.value = oldInput.value;
    }

    // Keep the hidden required field in sync with whatever the user types,
    // even if they never click a dropdown suggestion. Prevents the form
    // from silently blocking submit on a hidden required input.
    autocompleteEl.addEventListener('input', () => {
        oldInput.value = autocompleteEl.value || '';
    });

    autocompleteEl.addEventListener('gmp-select', async ({ placePrediction }) => {
        const place = placePrediction.toPlace();
        await place.fetchFields({
            fields: ['displayName', 'formattedAddress', 'location', 'addressComponents']
        });

        oldInput.value = place.formattedAddress || '';

        const lat = place.location.lat();
        const lng = place.location.lng();
        document.getElementById('latitude_input').value = lat;
        document.getElementById('longitude_input').value = lng;
        document.getElementById('map_embed_url_input').value =
            'https://www.google.com/maps?q=' + lat + ',' + lng + '&output=embed';
        document.getElementById('map_directions_url_input').value =
            'https://www.google.com/maps/dir/?api=1&destination=' + lat + ',' + lng;

        let city = '', state = '', zip = '';
        for (const component of (place.addressComponents || [])) {
            const types = component.types;
            if (types.includes('locality')) city = component.longText;
            if (types.includes('administrative_area_level_1')) state = component.shortText;
            if (types.includes('postal_code')) zip = component.longText;
        }
        if (city) document.getElementById('city_input').value = city;
        if (state) document.getElementById('state_input').value = state;
        if (zip) document.getElementById('zip_input').value = zip;

        // Auto-detect timezone from coordinates
        const tzUrl = 'https://maps.googleapis.com/maps/api/timezone/json?location=' + lat + ',' + lng + '&timestamp=' + Math.floor(Date.now()/1000) + '&key={{ config("services.google_maps.key") }}';
        fetch(tzUrl).then(r => r.json()).then(data => {
            if (data.timeZoneId) {
                const sel = document.querySelector('select[name="timezone"]');
                if (sel) sel.value = data.timeZoneId;
            }
        });
    });
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&callback=initAutocomplete&loading=async" async defer></script>
<style>
gmp-place-autocomplete {
    width: 100%;
    display: block;
    color-scheme: light;
}
</style>

</x-admin-layout>
