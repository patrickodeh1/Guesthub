<x-admin-layout :title="$property->name">
    <div class="card overflow-hidden mb-8">
        <img src="{{ $property->heroImageUrl() }}" class="w-full max-h-96 object-cover">
        <div class="p-6">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-950">{{ $property->name }}</h1>
                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $property->active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                    {{ $property->active ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
    </div>

    @php
        $unassigned = $categories->whereNotIn('id', $assignedIds);
    @endphp

    <div class="mb-3 mt-2 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-slate-950">Categories</h2>
        <span class="text-sm text-slate-500">{{ count($assignedIds) }} of {{ $categories->count() }} sections active</span>
    </div>

    @if(count($assignedIds) === 0)
        <div class="card card-pad mb-6 text-center text-slate-500">No sections assigned yet. Add sections from the list below.</div>
    @endif

    <div id="sortable-categories" class="mb-8 grid gap-4">
        @foreach($categories->whereIn('id', $assignedIds) as $category)
            @php
                $page = $property->pages->firstWhere('category_id', $category->id);
                $pivot = $property->categories->firstWhere('id', $category->id)?->pivot;
            @endphp
            <div class="card overflow-hidden" data-id="{{ $category->id }}">
                <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <span class="drag-handle grid h-8 w-6 shrink-0 cursor-grab place-items-center text-slate-400 hover:text-slate-600" title="Drag to reorder">
                            <x-icon name="menu" class="h-5 w-5" />
                        </span>
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-blue-50 text-blue-600 overflow-hidden">
                            @if($category->guest_icon)
                                <img src="{{ url('/img/'.$category->guest_icon) }}" class="h-full w-full object-cover">
                            @else
                                <x-icon :name="$category->slug" class="h-5 w-5" />
                            @endif
                        </span>
                        <div>
                            <p class="font-semibold text-slate-950">{{ $pivot?->custom_title ?: $category->title }}</p>
                            <p class="text-sm text-slate-500">{{ $category->description }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap shrink-0 gap-2">
                        <a href="{{ route('admin.categories.preview', [$category, $property]) }}" target="_blank" class="btn-secondary text-xs">Preview</a>
                        <a href="{{ route('admin.content.edit', [$property, $category]) }}" class="btn-secondary text-xs">Edit</a>
                        <form method="post" action="{{ route('admin.categories.assign') }}">
                            @csrf
                            <input type="hidden" name="property_id" value="{{ $property->id }}">
                            @foreach($categories->whereIn('id', $assignedIds)->where('id', '!=', $category->id) as $c)
                                <input type="hidden" name="category_ids[]" value="{{ $c->id }}">
                            @endforeach
                            <button class="btn-danger text-xs" onclick="return confirm('Remove this section from {{ $property->name }}?')">Remove</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($unassigned->count())
    <div class="mb-3">
        <button type="button" onclick="toggleSection('unassigned-pool')" class="text-sm font-semibold text-slate-500 hover:text-slate-900">
            + Show {{ $unassigned->count() }} available section(s) to add
        </button>
    </div>
    <div id="unassigned-pool" class="hidden">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($unassigned as $category)
                <div class="card card-pad flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-slate-100 overflow-hidden">
                            @if($category->guest_icon)
                                <img src="{{ url('/img/'.$category->guest_icon) }}" class="h-full w-full object-cover">
                            @else
                                <x-icon :name="$category->slug" class="h-5 w-5 text-slate-400" />
                            @endif
                        </span>
                        <p class="text-sm font-semibold text-slate-700">{{ $category->title }}</p>
                    </div>
                    <form method="post" action="{{ route('admin.categories.assign') }}">
                        @csrf
                        <input type="hidden" name="property_id" value="{{ $property->id }}">
                        @foreach($assignedIds as $assignedId)
                            <input type="hidden" name="category_ids[]" value="{{ $assignedId }}">
                        @endforeach
                        <input type="hidden" name="category_ids[]" value="{{ $category->id }}">
                        <button type="submit" class="btn-primary text-xs">Add</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
    <script>
    (function () {
        var el = document.getElementById('sortable-categories');
        if (!el) return;
        new Sortable(el, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function () {
                var ids = Array.from(el.children).map(function (card) { return card.dataset.id; });
                fetch('{{ route("admin.categories.reorder") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ category_ids: ids })
                });
            }
        });
    })();

    function toggleSection(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.classList.toggle('hidden');
    }
    </script>
</x-admin-layout>
