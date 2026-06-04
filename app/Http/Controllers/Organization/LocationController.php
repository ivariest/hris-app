<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::query()->latest();

        if ($search = trim((string) $request->string('search'))) {
            $query->where('location_name', 'like', "%{$search}%");
        }

        return view('pages.organization.locations.index', [
            'title' => 'Locations',
            'locations' => $query->paginate(10)->withQueryString(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function create()
    {
        return view('pages.organization.locations.create', [
            'title' => 'Create Location',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'location_name' => ['required', 'string', 'max:100', Rule::unique('locations', 'location_name')],
        ]);

        Location::create($data);

        return redirect()->route('locations.index')->with('success', 'Location created successfully.');
    }

    public function show(Location $location)
    {
        return view('pages.organization.locations.show', [
            'title' => $location->location_name,
            'location' => $location,
        ]);
    }

    public function edit(Location $location)
    {
        return view('pages.organization.locations.edit', [
            'title' => 'Edit Location',
            'location' => $location,
        ]);
    }

    public function update(Request $request, Location $location)
    {
        $data = $request->validate([
            'location_name' => ['required', 'string', 'max:100', Rule::unique('locations', 'location_name')->ignore($location->id)],
        ]);

        $location->update($data);

        return redirect()->route('locations.index')->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        $location->delete();

        return redirect()->route('locations.index')->with('success', 'Location deleted successfully.');
    }
}
