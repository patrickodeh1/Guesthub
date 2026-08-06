<x-admin-layout title="Guest Details">
    <div class="page-header">
        <div>
            <p class="eyebrow">Booking {{ $booking->booking_id }}</p>
            <h1 class="page-title">{{ $booking->guest_name }}</h1>
            <p class="page-subtitle">{{ $booking->property->name }} · {{ $booking->stayRangeLabel() }}</p>
        </div>
        <div class="flex flex-wrap gap-2"><a href="{{ route('admin.bookings.edit', $booking) }}" class="btn-secondary">Edit Booking</a><a href="{{ route('admin.bookings.index') }}" class="btn-ghost">Back</a></div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
        <section class="card card-pad">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-slate-700">Secure guest URL</p>
                <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                    <input id="guest-url" readonly value="{{ $booking->publicUrl() }}" class="input mt-0 min-w-0 flex-1">
                    <button type="button" data-copy="#guest-url" class="btn-primary gap-2"><x-icon name="copy" class="h-4 w-4" />Copy URL</button>
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-[#eadfc8] bg-[#fffaf1] p-4">
                <p class="text-sm font-semibold text-slate-800">Guest message templates</p>
                <textarea id="guest-message" readonly class="textarea min-h-24">Hi {{ $booking->guest_name }}, your secure check-in page is ready: {{ $booking->publicUrl() }}</textarea>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button" data-copy="#guest-message" class="btn-secondary gap-2"><x-icon name="copy" class="h-4 w-4" />Copy Full Message</button>
                    <a class="btn-secondary gap-2" href="https://wa.me/?text={{ urlencode('Hi '.$booking->guest_name.', your secure check-in page is ready: '.$booking->publicUrl()) }}" target="_blank"><x-icon name="contact-guest-services" class="h-4 w-4" />WhatsApp</a>
                </div>

                <div class="mt-5 border-t border-[#eadfc8] pt-4">
                    <p class="text-sm font-semibold text-slate-800">Custom welcome message for this guest</p>
                    <p class="mt-1 text-xs text-slate-500">Optional. If left blank, the global default intro from Settings is used instead.</p>
                    <form method="post" action="{{ route('admin.bookings.welcome-message', $booking) }}" class="mt-3">
                        @csrf
                        @method('put')
                        <textarea id="welcome-message-editor" name="welcome_message" rows="5" class="textarea">{{ old('welcome_message', $booking->welcome_message) }}</textarea>
                        <button class="btn-primary mt-3">Save Welcome Message</button>
                    </form>
                </div>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Status</p><p class="mt-2"><span class="badge badge-{{ $booking->status }}">{{ $booking->statusLabel() }}</span></p></div>
                <div class="rounded-xl border border-slate-200 p-4 sm:col-span-2">
                    <p class="text-sm text-slate-500">Photo ID</p>
                    @if($booking->photo_id_path || $booking->photo_id_back_path)
                        <div class="mt-3 grid gap-4 sm:grid-cols-2">
                            @if($booking->photo_id_path)
                                <div>
                                    <p class="text-xs font-semibold text-slate-500 mb-1">Front</p>
                                    <button type="button" onclick="openPhotoIdModal('{{ route('admin.bookings.photo-id-view', $booking) }}', 'Photo ID front')" class="block w-full text-left">
                                        <img src="{{ route('admin.bookings.photo-id-view', $booking) }}" alt="Photo ID front" class="rounded-lg border border-slate-200 max-h-64 w-auto object-contain">
                                    </button>
                                    <a class="mt-2 block text-sm font-semibold text-teal-800" href="{{ route('admin.bookings.photo-id', $booking) }}">Download original</a>
                                </div>
                            @endif
                            @if($booking->photo_id_back_path)
                                <div>
                                    <p class="text-xs font-semibold text-slate-500 mb-1">Back</p>
                                    <button type="button" onclick="openPhotoIdModal('{{ route('admin.bookings.photo-id-back-view', $booking) }}', 'Photo ID back')" class="block w-full text-left">
                                        <img src="{{ route('admin.bookings.photo-id-back-view', $booking) }}" alt="Photo ID back" class="rounded-lg border border-slate-200 max-h-64 w-auto object-contain">
                                    </button>
                                    <a class="mt-2 block text-sm font-semibold text-teal-800" href="{{ route('admin.bookings.photo-id-back', $booking) }}">Download original</a>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="mt-2 font-semibold text-slate-950">Not uploaded</p>
                    @endif
                </div>
                <div class="rounded-xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Check-in approval</p><p class="mt-2 font-semibold text-slate-950">{{ $booking->isCheckedIn() ? 'Approved' : 'Not approved' }}</p></div>
                <div class="rounded-xl border border-slate-200 p-4"><p class="text-sm text-slate-500">GPS</p><p class="mt-2 font-semibold text-slate-950">{{ $booking->gps_verified ? 'Verified by guest' : 'Not verified' }}</p></div>
                <div class="rounded-xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Parking</p><p class="mt-2 font-semibold text-slate-950">{{ is_null($booking->parking_needed) ? 'Unknown' : ($booking->parking_needed ? 'Needed' : 'Not needed') }}</p></div>
                <div class="rounded-xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Requested Check-in Time</p><p class="mt-2 font-semibold text-slate-950">{{ $booking->checkin_time_preference ?: 'Not specified' }}</p></div>
                <div class="rounded-xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Early Check-in Exception</p><p class="mt-2 font-semibold {{ $booking->early_checkin ? 'text-emerald-700' : 'text-slate-950' }}">{{ $booking->early_checkin ? 'Enabled' : 'Not enabled' }}</p></div>
                <div class="rounded-xl border border-slate-200 p-4"><p class="text-sm text-slate-500">ID Type</p><p class="mt-2 font-semibold text-slate-950">{{ $booking->id_type === 'passport' ? 'Passport' : 'State-issued ID' }}</p></div>
                <div class="rounded-xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Photo ID Already Received</p><p class="mt-2 font-semibold {{ $booking->photo_id_received ? 'text-emerald-700' : 'text-slate-950' }}">{{ $booking->photo_id_received ? 'Enabled' : 'Not enabled' }}</p></div>
                <div class="rounded-xl border border-slate-200 p-4"><p class="text-sm text-slate-500">Contact</p><p class="mt-2 font-semibold text-slate-950">{{ $booking->email ?: 'No email yet' }}</p></div>
            </div>

            @if($booking->notes)<div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4"><p class="text-sm font-semibold text-slate-700">Internal notes</p><p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $booking->notes }}</p></div>@endif
        </section>

        <aside class="grid gap-6">
            <section class="card card-pad">
                <h2 class="section-title">Admin actions</h2>
                <p class="section-copy">Use manual actions when an off-platform event has already been completed.</p>
                <div class="mt-5 grid gap-3">
                    <form method="post" action="{{ route('admin.bookings.override-gps', $booking) }}">@csrf<button class="btn-secondary w-full">Override GPS Verification</button></form>
                    <form method="post" action="{{ route('admin.bookings.override', $booking) }}">@csrf<button class="btn-primary w-full">Manually Mark Checked In</button></form>
                    <form method="post" action="{{ route('admin.bookings.mark-id', $booking) }}">@csrf<button class="btn-secondary w-full">Mark Photo ID Received</button></form>
                    @if($booking->photo_id_path || $booking->photo_id_back_path)
                        @if($booking->isApproved())
                            <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-sm text-emerald-800 font-semibold">Approved {{ $booking->approved_at->format('M j, Y g:i A') }}</div>
                        @else
                            <form method="post" action="{{ route('admin.bookings.approve', $booking) }}">@csrf<button class="btn-primary w-full">Approve for Check-In</button></form>
                            <button type="button" class="btn-secondary w-full" onclick="document.getElementById('decline-form-{{ $booking->id }}').classList.toggle('hidden')">Decline ID</button>
                            <form id="decline-form-{{ $booking->id }}" method="post" action="{{ route('admin.bookings.decline', $booking) }}" class="hidden grid gap-2">
                                @csrf
                                <textarea name="decline_reason" class="input" rows="3" placeholder="Reason for declining (shown to guest)" required>{{ old('decline_reason') }}</textarea>
                                <button class="btn-secondary w-full">Submit Decline</button>
                            </form>
                        @endif
                        @if($booking->decline_reason && !$booking->isApproved())
                            <div class="rounded-lg bg-amber-50 border border-amber-200 p-3 text-sm text-amber-800"><span class="font-semibold">Last decline reason:</span> {{ $booking->decline_reason }}</div>
                        @endif
                    @endif
                </div>
            </section>
            <section class="card card-pad">
                <h2 class="section-title">Guest progress</h2>
                <div class="mt-5 grid gap-3 text-sm">
                    @foreach([
                        ['Email received', filled($booking->email)],
                        ['Photo ID uploaded', filled($booking->photo_id_path)],
                        ['GPS verified', $booking->gps_verified],
                        ['Checked in', $booking->isCheckedIn()],
                    ] as [$label, $done])
                        <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3"><span>{{ $label }}</span><span class="badge {{ $done ? 'badge-active' : 'badge-pending' }}">{{ $done ? 'Done' : 'Open' }}</span></div>
                    @endforeach
                </div>
            </section>
            <section class="card card-pad">
                <h2 class="section-title">Preview guest flow</h2>
                <p class="section-copy">Open any guest state without changing the real booking status.</p>
                <div class="mt-4 grid gap-2">
                    @foreach(['identity' => 'Pre Check-In', 'waiting' => 'Waiting', 'arrival' => 'Check-In Day', 'guide' => 'Welcome Guide', 'checkout' => 'Checkout Day'] as $state => $label)
                        <a class="btn-secondary justify-start" href="{{ route('admin.bookings.preview', [$booking, $state]) }}" target="_blank">{{ $label }}</a>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>

    <section class="mt-6 card card-pad">
        <h2 class="section-title">Guest progress timeline</h2>
        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['properties', 'Booking created', $booking->created_at, true],
                ['security', 'Link opened', null, false],
                ['mail', 'Email submitted', $booking->updated_at, filled($booking->email)],
                ['upload', 'Photo ID uploaded', $booking->updated_at, filled($booking->photo_id_path)],
                ['calendar', 'Check-in day reached', $booking->check_in_date, $booking->isCheckinDay()],
                ['map', 'GPS verified', $booking->checked_in_at, $booking->gps_verified],
                ['security', 'Manual approval', $booking->checked_in_at, $booking->manually_checked_in],
                ['checkout-instructions', 'Checkout day', $booking->check_out_date, $booking->isCheckoutDay()],
            ] as [$icon, $label, $time, $done])
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <span class="icon-chip"><x-icon :name="$icon" /></span>
                    <p class="mt-3 font-semibold text-slate-950">{{ $label }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $done ? ($time ? $time->format('M j, Y g:i A') : 'Completed') : 'Pending' }}</p>
                    <span class="mt-3 badge {{ $done ? 'badge-active' : 'badge-pending' }}">{{ $done ? 'Done' : 'Open' }}</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Media Picker Modal (for editor image insert) --}}
    <div id="media-picker-modal" class="fixed inset-0 hidden items-center justify-center bg-slate-950/40 p-4" style="z-index:2147483000;">
        <div class="w-full max-w-2xl rounded-xl bg-white p-5 shadow-xl">
            <div class="mb-3 flex items-center justify-between gap-3">
                <p id="media-picker-breadcrumb" class="text-sm font-bold text-slate-700">Library</p>
                <div class="flex items-center gap-2">
                    <label class="btn-secondary cursor-pointer text-xs">
                        Upload
                        <input type="file" id="media-picker-upload-input" accept="image/*" class="sr-only">
                    </label>
                    <button type="button" onclick="closeMediaPickerForEditor()" class="text-slate-400 hover:text-slate-700">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>
            </div>
            <div id="media-picker-body" class="grid max-h-96 grid-cols-3 gap-3 overflow-y-auto sm:grid-cols-4"></div>
        </div>
    </div>

    {{-- Photo ID Viewer Modal --}}
    <div id="photo-id-modal" tabindex="-1" class="fixed inset-0 hidden items-center justify-center bg-slate-950/40 p-4" style="z-index:2147483000;">
        <div class="w-full max-w-2xl rounded-xl bg-white p-5 shadow-xl">
            <div class="mb-3 flex items-center justify-between gap-3">
                <p id="photo-id-modal-title" class="text-sm font-bold text-slate-700">Photo ID</p>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="photoIdZoomOut()" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-500 hover:bg-slate-50">&minus;</button>
                    <span id="photo-id-modal-zoom-level" class="w-10 text-center text-xs font-semibold text-slate-500">100%</span>
                    <button type="button" onclick="photoIdZoomIn()" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-500 hover:bg-slate-50">+</button>
                    <button type="button" onclick="photoIdZoomReset()" class="rounded-md border border-slate-200 px-2 py-1 text-xs font-semibold text-slate-500 hover:bg-slate-50">Reset</button>
                    <button type="button" onclick="closePhotoIdModal()" class="text-slate-400 hover:text-slate-700">
                        <x-icon name="x" class="h-5 w-5" />
                    </button>
                </div>
            </div>
            <div id="photo-id-modal-viewport" class="max-h-[75vh] w-full overflow-hidden rounded-lg bg-slate-100" style="cursor: grab;">
                <img id="photo-id-modal-img" src="" alt="" class="h-full w-full select-none object-contain" style="transform-origin: center center; transition: transform 0.08s ease-out; user-select:none; -webkit-user-drag:none;" draggable="false">
            </div>
        </div>
    </div>

    {{-- Force TinyMCE's floating menus/dropdowns/overflow drawer below our modal --}}
    <style>
    .tox-tinymce-aux,
    .tox.tox-silver-sink,
    .tox-dialog-wrap {
        z-index: 1000 !important;
    }
    .tox-menu.tox-collection.tox-collection--list {
        max-height: 320px !important;
        overflow-y: auto !important;
    }
    </style>

    {{-- Google Fonts loaded on the PAGE so toolbar dropdown labels render correctly --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;700&family=Montserrat:wght@400;700&family=Merriweather:wght@400;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">

    <script>
    let __mediaPickerCurrentFolder = null;

    let __photoIdZoom = 1;
    let __photoIdPanX = 0;
    let __photoIdPanY = 0;
    let __photoIdDragging = false;
    let __photoIdDragStartX = 0;
    let __photoIdDragStartY = 0;
    const PHOTO_ID_ZOOM_MIN = 1;
    const PHOTO_ID_ZOOM_MAX = 4;
    const PHOTO_ID_ZOOM_STEP = 0.25;

    function __photoIdApplyTransform() {
        const img = document.getElementById('photo-id-modal-img');
        img.style.transform = `translate(${__photoIdPanX}px, ${__photoIdPanY}px) scale(${__photoIdZoom})`;
        document.getElementById('photo-id-modal-zoom-level').textContent = Math.round(__photoIdZoom * 100) + '%';
        const viewport = document.getElementById('photo-id-modal-viewport');
        viewport.style.cursor = __photoIdZoom > 1 ? 'grab' : 'default';
    }

    function __photoIdClampPan() {
        if (__photoIdZoom <= 1) {
            __photoIdPanX = 0;
            __photoIdPanY = 0;
            return;
        }
        const viewport = document.getElementById('photo-id-modal-viewport');
        const maxPanX = (viewport.clientWidth * (__photoIdZoom - 1)) / 2;
        const maxPanY = (viewport.clientHeight * (__photoIdZoom - 1)) / 2;
        __photoIdPanX = Math.max(-maxPanX, Math.min(maxPanX, __photoIdPanX));
        __photoIdPanY = Math.max(-maxPanY, Math.min(maxPanY, __photoIdPanY));
    }

    function photoIdZoomIn() {
        __photoIdZoom = Math.min(PHOTO_ID_ZOOM_MAX, __photoIdZoom + PHOTO_ID_ZOOM_STEP);
        __photoIdClampPan();
        __photoIdApplyTransform();
    }

    function photoIdZoomOut() {
        __photoIdZoom = Math.max(PHOTO_ID_ZOOM_MIN, __photoIdZoom - PHOTO_ID_ZOOM_STEP);
        __photoIdClampPan();
        __photoIdApplyTransform();
    }

    function photoIdZoomReset() {
        __photoIdZoom = 1;
        __photoIdPanX = 0;
        __photoIdPanY = 0;
        __photoIdApplyTransform();
    }

    function openPhotoIdModal(url, title) {
        document.getElementById('photo-id-modal-img').src = url;
        document.getElementById('photo-id-modal-title').textContent = title;
        const modal = document.getElementById('photo-id-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        modal.focus();
        modal.scrollIntoView({ behavior: 'instant', block: 'center' });
        photoIdZoomReset();
    }

    function closePhotoIdModal() {
        const modal = document.getElementById('photo-id-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('photo-id-modal-img').src = '';
        document.body.style.overflow = '';
        photoIdZoomReset();
    }

    (function initPhotoIdZoomInteractions() {
        const viewport = document.getElementById('photo-id-modal-viewport');
        const img = document.getElementById('photo-id-modal-img');

        viewport.addEventListener('wheel', function (e) {
            if (document.getElementById('photo-id-modal').classList.contains('hidden')) return;
            e.preventDefault();
            if (e.deltaY < 0) {
                __photoIdZoom = Math.min(PHOTO_ID_ZOOM_MAX, __photoIdZoom + PHOTO_ID_ZOOM_STEP);
            } else {
                __photoIdZoom = Math.max(PHOTO_ID_ZOOM_MIN, __photoIdZoom - PHOTO_ID_ZOOM_STEP);
            }
            __photoIdClampPan();
            __photoIdApplyTransform();
        }, { passive: false });

        viewport.addEventListener('mousedown', function (e) {
            if (__photoIdZoom <= 1) return;
            __photoIdDragging = true;
            __photoIdDragStartX = e.clientX - __photoIdPanX;
            __photoIdDragStartY = e.clientY - __photoIdPanY;
            viewport.style.cursor = 'grabbing';
        });

        window.addEventListener('mousemove', function (e) {
            if (!__photoIdDragging) return;
            __photoIdPanX = e.clientX - __photoIdDragStartX;
            __photoIdPanY = e.clientY - __photoIdDragStartY;
            __photoIdClampPan();
            __photoIdApplyTransform();
        });

        window.addEventListener('mouseup', function () {
            if (!__photoIdDragging) return;
            __photoIdDragging = false;
            viewport.style.cursor = __photoIdZoom > 1 ? 'grab' : 'default';
        });

        img.addEventListener('dblclick', function () {
            if (__photoIdZoom > 1) {
                photoIdZoomReset();
            } else {
                __photoIdZoom = 2;
                __photoIdApplyTransform();
            }
        });
    })();

        function closeMediaPickerForEditor() {
        const modal = document.getElementById('media-picker-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openMediaPickerForEditor() {
        const modal = document.getElementById('media-picker-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        loadMediaPickerForEditor(null);
    }

    function loadMediaPickerForEditor(folderId) {
        __mediaPickerCurrentFolder = folderId;
        const url = '{{ route("admin.media.picker") }}' + (folderId ? '?folder_id=' + folderId : '');
        fetch(url).then(r => r.json()).then(data => {
            const body = document.getElementById('media-picker-body');
            const crumbText = data.breadcrumb.length ? data.breadcrumb.map(c => c.name).join(' / ') : 'Library';
            document.getElementById('media-picker-breadcrumb').textContent = crumbText;
            body.innerHTML = '';
            if (folderId !== null) {
                const up = document.createElement('button');
                up.type = 'button';
                up.className = 'col-span-full text-left text-xs font-semibold text-blue-600 hover:underline';
                up.textContent = 'Back';
                const parentId = data.breadcrumb.length > 1 ? data.breadcrumb[data.breadcrumb.length - 2].id : null;
                up.onclick = () => loadMediaPickerForEditor(parentId);
                body.appendChild(up);
            }
            data.folders.forEach(folder => {
                const el = document.createElement('button');
                el.type = 'button';
                el.className = 'flex flex-col items-center gap-1 rounded-lg border border-slate-200 p-3 hover:bg-slate-50';
                el.innerHTML = '<span class="text-xs font-semibold">' + folder.name + '</span>';
                el.onclick = () => loadMediaPickerForEditor(folder.id);
                body.appendChild(el);
            });
            data.files.forEach(file => {
                const el = document.createElement('button');
                el.type = 'button';
                el.className = 'overflow-hidden rounded-lg border border-slate-200 bg-slate-50 hover:ring-2 hover:ring-blue-400';
                el.innerHTML = '<img src="' + file.url + '" class="h-20 w-full object-contain p-1">';
                el.onclick = () => {
                    if (window.tinymce && tinymce.activeEditor) {
                        tinymce.activeEditor.insertContent('<img src="' + file.url + '" alt="' + (file.name || '') + '" style="max-width:100%;">');
                    }
                    closeMediaPickerForEditor();
                };
                body.appendChild(el);
            });
            if (!data.folders.length && !data.files.length) {
                body.innerHTML += '<p class="col-span-full text-center text-sm text-slate-400">No images in this folder yet.</p>';
            }
        });
    }

    document.getElementById('media-picker-upload-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        const formData = new FormData();
        formData.append('image', file);
        if (__mediaPickerCurrentFolder) formData.append('media_folder_id', __mediaPickerCurrentFolder);
        fetch('{{ route("admin.media.files.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData,
        }).then(() => {
            loadMediaPickerForEditor(__mediaPickerCurrentFolder);
            e.target.value = '';
        });
    });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
    <script>
    tinymce.init({
        relative_urls: false,
        remove_script_host: false,
        selector: '#welcome-message-editor',
        plugins: 'lists link code table',
        toolbar: 'undo redo | bold italic underline forecolor backcolor | alignleft aligncenter alignright | bullist numlist | customlineheight | link insertimage table | removeformat code | fontfamily fontsize',
        browser_spellcheck: true,
        contextmenu: false,
        font_size_formats: '8px 10px 12px 14px 16px 18px 20px 24px 28px 32px 36px 42px 48px 60px 72px',
        font_family_formats:
            'Arial=arial,helvetica,sans-serif;' +
            'Helvetica=helvetica,arial,sans-serif;' +
            'Times New Roman=times new roman,times,serif;' +
            'Georgia=georgia,palatino,serif;' +
            'Garamond=garamond,serif;' +
            'Verdana=verdana,geneva,sans-serif;' +
            'Tahoma=tahoma,arial,helvetica,sans-serif;' +
            'Trebuchet MS=trebuchet ms,geneva,sans-serif;' +
            'Courier New=courier new,courier,monospace;' +
            'Comic Sans MS=comic sans ms,sans-serif;' +
            'Impact=impact,sans-serif;' +
            'Lucida Sans=lucida sans unicode,lucida grande,sans-serif;' +
            'Roboto=Roboto,arial,sans-serif;' +
            'Open Sans=\'Open Sans\',arial,sans-serif;' +
            'Montserrat=Montserrat,arial,sans-serif;' +
            'Merriweather=Merriweather,georgia,serif;' +
            'Playfair Display=\'Playfair Display\',georgia,serif',
        color_map: [
            '000000','Black', '424242','Dark Gray', '757575','Gray', 'BDBDBD','Light Gray', 'FFFFFF','White',
            'B71C1C','Dark Red', 'E53935','Red', 'F44336','Bright Red', 'FF7043','Orange Red', 'FB8C00','Orange',
            'FDD835','Yellow', 'C0CA33','Olive', '7CB342','Light Green', '43A047','Green', '00897B','Teal',
            '00ACC1','Cyan', '1E88E5','Blue', '3949AB','Indigo', '5E35B1','Purple', '8E24AA','Magenta', 'D81B60','Pink'
        ],
        valid_styles: {
            '*': 'font-size,font-family,color,background-color,text-align,text-decoration,line-height'
        },
        menubar: false,
        height: 320,
        ui_mode: 'split',
        promotion: false,
        branding: false,
        content_css: false,
        content_style: `
            @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;700&family=Montserrat:wght@400;700&family=Merriweather:wght@400;700&family=Playfair+Display:wght@400;700&display=swap');
            body { font-family: Helvetica, Arial, sans-serif; font-size: 14px; }
            p { margin: 0; }
        `,
        setup: function(editor) {
            var lineHeightValues = ['0.3', '0.5', '0.7', '0.9', '1', '1.15', '1.3', '1.5', '1.75', '2', '2.5', '3', '3.5', '4', '4.5', '5'];
            var lastSelectionRange = null;
            editor.on('NodeChange KeyUp MouseUp', function() {
                try { lastSelectionRange = editor.selection.getRng().cloneRange(); } catch (e) {}
            });
            editor.ui.registry.addMenuButton('customlineheight', {
                icon: 'line-height',
                tooltip: 'Line height',
                fetch: function(callback) {
                    var currentValue = null;
                    try {
                        var node0 = editor.selection.getNode();
                        var block0 = editor.dom.getParent(node0, editor.dom.isBlock) || node0;
                        if (block0 && block0.nodeName !== 'BODY') {
                            currentValue = editor.dom.getStyle(block0, 'line-height') || null;
                        }
                    } catch (e) {}
                    var items = lineHeightValues.map(function(v) {
                        return {
                            type: 'togglemenuitem',
                            text: v,
                            active: currentValue === v,
                            onAction: function() {
                                editor.focus();
                                if (lastSelectionRange) {
                                    try { editor.selection.setRng(lastSelectionRange); } catch (e) {}
                                }
                                var blocks = editor.selection.getSelectedBlocks();
                                if (!blocks || !blocks.length) {
                                    var node = editor.selection.getNode();
                                    var single = editor.dom.getParent(node, editor.dom.isBlock) || node;
                                    blocks = [single];
                                }
                                var applied = 0;
                                blocks.forEach(function(block) {
                                    if (block && block.nodeName !== 'BODY') {
                                        editor.dom.setStyle(block, 'line-height', v);
                                        applied++;
                                    }
                                });
                                if (applied > 0) {
                                    editor.nodeChanged();
                                }
                            }
                        };
                    });
                    callback(items);
                }
            });
            editor.ui.registry.addButton('insertimage', {
                icon: 'image',
                tooltip: 'Insert image from library',
                onAction: function() {
                    openMediaPickerForEditor();
                }
            });
        }
    });
    </script>
</x-admin-layout>
