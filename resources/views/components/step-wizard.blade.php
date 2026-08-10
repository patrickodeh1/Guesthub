@props(['steps', 'type' => 'checkin', 'nextSection' => 'guest-guide-section', 'bookingId' => 'PREVIEW', 'token' => 'preview'])
@php $total = count($steps); @endphp
@if($total > 0)
<div id="step-wizard-{{ $type }}" class="guest-portal-card guest-portal-card--wizard">
    <div class="guest-status-bar">
        <div>
            <p class="guest-status-kicker">{{ $type === 'checkout' ? 'Check-out' : ($type === 'parking' ? 'Parking' : 'Check-in') }}</p>
            <h1 class="guest-status-title" id="wizard-title-{{ $type }}">{{ $steps[0]['title'] }}</h1>
        </div>
        <span class="guest-status-pill is-checked">
            <span id="wizard-counter-{{ $type }}" class="text-xs font-bold">1 / {{ $total }}</span>
        </span>
    </div>

    <div class="wizard-body">
        <div class="wizard-scroll">
            @foreach($steps as $i => $step)
            @php $allImages = array_values(array_filter(array_merge([$step['image'] ?? null], $step['images'] ?? []))); @endphp
            <div class="wizard-step" id="wizard-{{ $type }}-step-{{ $i }}" @if($i > 0) style="display:none" @endif>
                @if(count($allImages))
                    <img id="wizard-main-img-{{ $type }}-{{ $i }}" src="{{ $allImages[0] }}" alt="{{ $step['title'] }}" class="mb-3 w-full rounded-xl shadow-sm">
                    @if(count($allImages) > 1)
                        <div class="mb-4 flex gap-2 overflow-x-auto pb-1">
                            @foreach(array_slice($allImages, 1) as $img)
                                <img src="{{ $img }}" alt="{{ $step['title'] }}" loading="lazy" decoding="async" class="wizard-gallery-thumb h-20 w-20 shrink-0 cursor-pointer rounded-lg object-cover shadow-sm" data-type="{{ $type }}" data-step="{{ $i }}">
                            @endforeach
                        </div>
                    @endif
                @endif
                <div class="prose-welcome text-base text-slate-700">{!! $step['content'] !!}</div>
                @if(($step['action'] ?? 'content') === 'door_lock' && ($step['lock_id'] ?? null))
                    <x-lock-card class="mt-5" :booking-id="$bookingId" :token="$token" :lock-id="$step['lock_id']" :lock-status="$step['lock_status'] ?? null" />
                @elseif(($step['action'] ?? 'content') === 'door_lock')
                    <p class="mt-5 text-sm font-bold text-slate-500 text-center">No lock is configured for this property yet.</p>
                @endif
            </div>
            @endforeach
        </div>

        <div class="wizard-dots-row">
            @foreach($steps as $i => $step)
                <span class="wizard-dot h-2 w-2 rounded-full {{ $i === 0 ? 'bg-slate-900' : 'bg-slate-200' }}" id="wizard-dot-{{ $type }}-{{ $i }}"></span>
            @endforeach
        </div>

        <div class="wizard-nav">
            <button type="button" id="wizard-prev-{{ $type }}" class="guest-outline-btn flex-1" style="display:none">Previous</button>
            <button type="button" id="wizard-next-{{ $type }}" class="guest-primary-btn flex-1" @if($total === 1) style="display:none" @endif>Next</button>
            <button type="button" id="wizard-done-{{ $type }}" class="guest-primary-btn is-go flex-1" @if($total > 1) style="display:none" @endif>
                <x-icon name="check" class="h-4 w-4" />
                {{ $type === "checkout" ? "All Done" : ($type === "checkin" ? "I'm Checked In!" : "Continue to Guide") }}
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    var type = "{{ $type }}";
    var total = {{ $total }};
    var current = 0;
    var titles = @json(array_column($steps, 'title'));

    document.querySelectorAll('.wizard-gallery-thumb[data-type="' + type + '"]').forEach(function (thumb) {
        thumb.addEventListener("click", function () {
            var step = thumb.dataset.step;
            var main = document.getElementById("wizard-main-img-" + type + "-" + step);
            if (!main) return;
            var swap = main.src;
            main.src = thumb.src;
            thumb.src = swap;
        });
    });

    function goTo(n) {
        document.getElementById("wizard-" + type + "-step-" + current).style.display = "none";
        document.getElementById("wizard-dot-" + type + "-" + current).className = "wizard-dot h-2 w-2 rounded-full bg-slate-200";
        current = n;
        document.getElementById("wizard-" + type + "-step-" + current).style.display = "";
        document.getElementById("wizard-dot-" + type + "-" + current).className = "wizard-dot h-2 w-2 rounded-full bg-slate-900";
        document.getElementById("wizard-title-" + type).textContent = titles[current];
        document.getElementById("wizard-counter-" + type).textContent = (current + 1) + " / " + total;
        document.getElementById("wizard-prev-" + type).style.display = current === 0 ? "none" : "";
        document.getElementById("wizard-next-" + type).style.display = current === total - 1 ? "none" : "";
        document.getElementById("wizard-done-" + type).style.display = current === total - 1 ? "" : "none";
        var scrollEl = document.querySelector("#step-wizard-" + type + " .wizard-scroll");
        if (scrollEl) scrollEl.scrollTo({top: 0, behavior: "smooth"});
    }

    document.getElementById("wizard-next-" + type).addEventListener("click", function() { if (current < total - 1) goTo(current + 1); });
    document.getElementById("wizard-prev-" + type).addEventListener("click", function() { if (current > 0) goTo(current - 1); });
    document.getElementById("wizard-done-" + type).addEventListener("click", function() {
        if (type === "checkin" || type === "checkout") {
            var confirmUrl = type === "checkin"
                ? "{{ route('guest.confirm-checkin', [$bookingId, $token]) }}"
                : "{{ route('guest.confirm-checkout', [$bookingId, $token]) }}";
            var doneBtn = document.getElementById("wizard-done-" + type);
            doneBtn.disabled = true;
            fetch(confirmUrl, {
                method: "POST",
                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}", "Content-Type": "application/json" }
            }).then(function(response) {
                if (!response.ok) {
                    throw new Error("Request failed with status " + response.status);
                }
                document.getElementById("step-wizard-" + type).style.display = "none";
                var wrapper = document.getElementById("step-wizard-" + type + "-wrapper");
                if (wrapper) wrapper.style.display = "none";
                var next = document.getElementById("{{ $nextSection }}-wrapper") || document.getElementById("{{ $nextSection }}");
                if (next) next.style.display = "";
                window.scrollTo({top: 0, behavior: "smooth"});
            }).catch(function() {
                doneBtn.disabled = false;
                var errEl = document.getElementById("wizard-error-" + type);
                if (!errEl) {
                    errEl = document.createElement("p");
                    errEl.id = "wizard-error-" + type;
                    errEl.className = "mt-3 text-sm font-semibold text-red-600 text-center";
                    doneBtn.insertAdjacentElement("afterend", errEl);
                }
                errEl.textContent = "Something went wrong. Please check your connection and try again, or refresh the page.";
            });
        } else {
            document.getElementById("step-wizard-" + type).style.display = "none";
            var wrapper = document.getElementById("step-wizard-" + type + "-wrapper");
            if (wrapper) wrapper.style.display = "none";
            var next = document.getElementById("{{ $nextSection }}-wrapper") || document.getElementById("{{ $nextSection }}");
            if (next) next.style.display = "";
            window.scrollTo({top: 0, behavior: "smooth"});
        }
    });
})();
</script>
@endif
