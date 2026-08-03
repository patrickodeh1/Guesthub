@php
    $assignmentImage = optional($category->pivot)->header_image;
    $heroImage = $assignmentImage
        ? url('/img/'.$assignmentImage)
        : ($category->header_image
            ? url('/img/'.$category->header_image)
            : (optional($page)->image_1 ? url('/img/'.$page->image_1) : null));
    $displayTitle = optional($category->pivot)->custom_title ?: (optional($page)->title ?: $category->title);
    $displayDescription = optional($category->pivot)->custom_description ?: $category->description;
    $fallbackPhotos = [
        'wifi' => 'https://images.unsplash.com/photo-1606904825846-647eb07f5be2?auto=format&fit=crop&w=1400&q=80',
        'amenities' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1400&q=80',
        'fitness-center' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=1400&q=80',
        'pool' => 'https://images.unsplash.com/photo-1572331165267-854da2b10ccc?auto=format&fit=crop&w=1400&q=80',
        'restaurants' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1400&q=80',
        'bars' => 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?auto=format&fit=crop&w=1400&q=80',
        'parking' => 'https://images.unsplash.com/photo-1506521781263-d8422e82f27a?auto=format&fit=crop&w=1400&q=80',
        'checkout-instructions' => 'https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?auto=format&fit=crop&w=1400&q=80',
    ];
    $heroImage = $heroImage ?: ($fallbackPhotos[$category->slug] ?? null);
    $tone = ['#eef2ff', '#3b65ce'];
@endphp

<x-guest-layout :booking="$booking" :property="$booking->property" :title="$displayTitle" state="guide">
<section class="guest-detail-shell">
    <a href="{{ route('guest.show', [$booking->booking_id, $booking->token]) }}"
       class="mb-5 inline-flex items-center gap-2 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm transition hover:-translate-y-px hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300">
        <x-icon name="arrow-left" class="h-4 w-4" />
        Back to guide
    </a>

    <header class="guest-detail-hero">
        @if($heroImage)
            <img src="{{ $heroImage }}" alt="{{ $displayTitle }}" loading="eager">
        @endif
    </header>

    <div class="guest-category-scroll">
        @foreach($categories as $cat)
            @php
                $catTone = ['#eef2ff', '#3b65ce'];
                $catTitle = $cat->pivot->custom_title ?: $cat->title;
                $isActive = $cat->id === $category->id;
            @endphp
            @if($booking->booking_id === 'PREVIEW')
            <a href="{{ route('admin.categories.preview', [$cat, $booking->property]) }}"
               class="guest-category-scroll-item {{ $isActive ? 'is-active' : '' }}"
               style="--scroll-tone: {{ $catTone[0] }}; --scroll-accent: {{ $catTone[1] }};">
            @else
            <a href="{{ route('guest.category', [$booking->booking_id, $booking->token, $cat]) }}"
               class="guest-category-scroll-item {{ $isActive ? 'is-active' : '' }}"
               style="--scroll-tone: {{ $catTone[0] }}; --scroll-accent: {{ $catTone[1] }};">
            @endif
                <span class="guest-category-scroll-icon">
                    @if($cat->guest_icon)
                        <img src="{{ url('/img/'.$cat->guest_icon) }}" alt="{{ $catTitle }}" class="h-6 w-6 rounded object-cover">
                    @else
                        <x-icon :name="$cat->slug" class="h-6 w-6" />
                    @endif
                </span>
                <span class="guest-category-scroll-label">{{ $catTitle }}</span>
            </a>
        @endforeach
    </div>

    @php
        $hasArticleContent = $category->action === 'door_lock'
            || optional($page)->content
            || ($category->slug === 'amenities' && $booking->property->amenities->where('active', true)->count());
    @endphp
    <div class="guest-detail-content-grid">
        @if($hasArticleContent)
        <article class="guest-detail-card">
            @if($category->action === 'door_lock' && $locks->isNotEmpty())
                <div class="grid gap-6 {{ $locks->count() > 1 ? 'sm:grid-cols-2' : '' }}">
                    @foreach($locks as $entry)
                        <x-lock-card
                            :booking-id="$booking->booking_id"
                            :token="$booking->token"
                            :lock-id="$entry['lock']->id"
                            :lock-label="$locks->count() > 1 ? $entry['lock']->label : null"
                            :lock-status="$entry['status']"
                        />
                    @endforeach
                </div>
            @elseif($category->action === 'door_lock')
                <p class="text-sm font-bold text-slate-500 text-center py-6">No lock is configured for this property yet.</p>
            @elseif(optional($page)->content)
                <div class="prose-welcome text-base {{ $category->slug === 'wifi' ? 'wifi-cards' : '' }}">{!! $page->renderContent($booking) !!}</div>
            @endif

            @if($category->slug === 'amenities' && $booking->property->amenities->where('active', true)->count())
                <section class="mt-8 border-t border-slate-200 pt-8">
                    <h2 class="text-xl font-black text-slate-950">Available amenities</h2>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        @foreach($booking->property->amenities->where('active', true) as $amenity)
                            <div class="guest-amenity-card">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-md" style="background: {{ $tone[0] }}; color: {{ $tone[1] }}">
                                    <x-icon :name="\Illuminate\Support\Str::slug($amenity->title)" class="h-5 w-5" />
                                </span>
                                <div>
                                    <p class="font-black text-slate-950">{{ $amenity->title }}</p>
                                    @if($amenity->details)
                                        <p class="mt-1 text-sm leading-6 text-slate-600">{{ $amenity->details }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </article>
        @endif

        @if($booking->property->contact_phone)
            <a href="tel:{{ $booking->property->contact_phone }}" class="guest-contact-fab">
                <span class="guest-contact-fab-icon"><x-icon name="contact-guest-services" class="h-5 w-5" /></span>
                <span class="guest-contact-fab-label">Contact Guest Services</span>
            </a>
        @endif
    </div>
</section>
</x-guest-layout>
