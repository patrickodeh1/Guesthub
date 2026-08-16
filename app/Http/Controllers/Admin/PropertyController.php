<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Property;
use App\Models\Category;
use App\Models\InstructionStep;
use Illuminate\Http\Request;
use App\Services\MediaService;
use Illuminate\Support\Str;

class PropertyController extends Controller
{

    public function guideIndex(Request $request)
    {
        $properties = Property::query()
            ->when($request->search, fn ($query, $search) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
            ))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.guest-guide.index', [
            'properties' => $properties,
        ]);
    }

    public function guide(Property $property)
    {
        $property->load(['categories', 'pages']);
        $assignedIds = $property->categories->pluck('id')->toArray();
        $categories = Category::orderBy('sort_order')->get();

        return view('admin.guest-guide.show', [
            'property' => $property,
            'categories' => $categories,
            'assignedIds' => $assignedIds,
        ]);
    }
    public function index(Request $request)
    {
        $properties = Property::query()
            ->when($request->search, fn ($query, $search) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
            ))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.properties.index', compact('properties'));
    }

    public function create()
    {
        return view('admin.properties.form', ['property' => new Property()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['timezone'] = $this->detectTimezone($data['latitude'] ?? null, $data['longitude'] ?? null, $data['timezone'] ?? null);
        $property = Property::create($data);

        foreach (\App\Models\Category::all() as $category) {
            $property->categories()->attach($category->id, ['active' => true]);
        }


        ActivityLog::record('property_created', "{$property->name} was added.", 'properties', $property);

        return redirect()->route('admin.properties.index')->with('success', 'Property created.');
    }

    public function edit(Property $property, Request $request)
    {
        return view('admin.properties.form', [
            'property' => $property,
            'returnTo' => $request->headers->get('referer'),
        ]);
    }

    public function update(Request $request, Property $property)
    {
        $data = $this->validated($request, $property);
        $data['timezone'] = $this->detectTimezone($data['latitude'] ?? null, $data['longitude'] ?? null, $data['timezone'] ?? null);
        $property->update($data);
        ActivityLog::record('property_updated', "{$property->name} was updated.", 'edit', $property);

        $destination = $request->filled('return_to') ? $request->input('return_to') : route('admin.properties.index');

        return redirect()->to($destination)->with('success', 'Property updated.');
    }

    public function updateCheckoutTime(Request $request, Property $property)
    {
        $data = $request->validate([
            'checkout_time' => ['required', 'date_format:H:i'],
        ]);

        $property->update(['checkout_time' => $data['checkout_time']]);

        ActivityLog::record('property_updated', "{$property->name} check-out time was updated.", 'edit', $property);

        return response()->json(['ok' => true, 'checkout_time' => $property->checkout_time]);
    }

    public function updateLockboxCode(Request $request, Property $property)
    {
        $data = $request->validate([
            'lockbox_code' => ['nullable', 'string', 'max:255'],
        ]);

        $property->update(['lockbox_code' => $data['lockbox_code'] ?? null]);

        ActivityLog::record('property_updated', "{$property->name} lockbox code was updated.", 'edit', $property);

        return back()->with('success', 'Lockbox code updated.');
    }

    public function destroy(Property $property)
    {
        $property->delete();
        ActivityLog::record('property_deleted', "{$property->name} was deleted.", 'delete');

        return back()->with('success', 'Property deleted.');
    }

    public function duplicate(Request $request, Property $property)
    {
        $data = $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $baseName = preg_replace('/\s*-\s*Unit\s+.+$/i', '', $property->name);
        $created = [];

        for ($i = 0; $i < $data['count']; $i++) {
            $unitLabel = $this->nextUnitLabel($baseName);

            $copy = $property->replicate(['slug']);
            $copy->name = "{$baseName} - Unit {$unitLabel}";
            $copy->unit_number = $unitLabel;
            $copy->slug = $this->uniqueSlug($copy->name);
            $copy->save();

            foreach ($property->amenities as $amenity) {
                $newAmenity = $amenity->replicate();
                $newAmenity->property_id = $copy->id;
                $newAmenity->save();
            }

            foreach ($property->instructionSteps as $step) {
                $newStep = $step->replicate();
                $newStep->property_id = $copy->id;
                $newStep->save();
            }

            foreach ($property->pages as $page) {
                $newPage = $page->replicate();
                $newPage->property_id = $copy->id;
                $newPage->save();
            }

            foreach ($property->categories as $category) {
                $copy->categories()->attach($category->id, $category->pivot->only([
                    'custom_title', 'custom_description', 'header_image', 'active',
                ]));
            }

            $created[] = $copy;
        }

        ActivityLog::record('property_duplicated', "{$property->name} was duplicated into ".count($created)." unit(s).", 'properties', $property);

        return redirect()->route('admin.properties.index')->with('success', count($created).' unit(s) created from '.$property->name.'.');
    }

    private function nextUnitLabel(string $baseName): int
    {
        $count = Property::where('name', 'like', $baseName.' - Unit %')->count();
        return $count + 2; // original counts as "Unit 1" implicitly
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;
        while (Property::where('slug', $slug)->exists()) {
            $slug = $original.'-'.(++$i);
        }
        return $slug;
    }

    private function validated(Request $request, ?Property $property = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:properties,slug,'.($property?->id ?? 'NULL')],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'zip' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'map_embed_url' => ['nullable', 'url'],
            'map_directions_url' => ['nullable', 'url'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'welcome_intro' => ['nullable', 'string'],
            'checkin_instructions' => ['nullable', 'string'],
            'parking_instructions' => ['nullable', 'string'],
            'checkout_instructions' => ['nullable', 'string'],
            'header_image' => ['nullable', 'image', 'max:10240'],
            'existing_header_image' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'checkout_time' => ['nullable', 'date_format:H:i'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['active'] = $request->boolean('active');

        if ($request->hasFile('header_image')) {
            $data['header_image'] = $request->file('header_image')->store('properties', 'public');
            MediaService::register($data['header_image'], $request->file('header_image')->getClientOriginalName(), $request->file('header_image')->getSize(), 'Property Headers');
        } elseif ($request->filled('existing_header_image')) {
            $data['header_image'] = $request->input('existing_header_image');
        } elseif ($property) {
            unset($data['header_image']);
        }
        unset($data['existing_header_image']);

        return $data;
    }

    private function detectTimezone(?float $lat, ?float $lng, ?string $existing): string
    {
        if (!$lat || !$lng) return $existing ?? 'America/New_York';
        try {
            $url = "https://timeapi.io/api/timezone/coordinate?latitude={$lat}&longitude={$lng}";
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            if (isset($data['timeZone'])) return $data['timeZone'];
        } catch (\Throwable $e) {
            // fallback
        }
        return $existing ?? 'America/New_York';
    }
}
