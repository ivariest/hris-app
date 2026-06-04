<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\PositionLevel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PositionLevelController extends Controller
{
    public function index(Request $request)
    {
        $query = PositionLevel::query()->latest();

        if ($search = trim((string) $request->string('search'))) {
            $query->where('level_name', 'like', "%{$search}%");
        }

        return view('pages.organization.position-levels.index', [
            'title' => 'Position Levels',
            'positionLevels' => $query->paginate(10)->withQueryString(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function create()
    {
        return view('pages.organization.position-levels.create', [
            'title' => 'Create Position Level',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'level_name' => ['required', 'string', 'max:50', Rule::unique('position_levels', 'level_name')],
        ]);

        PositionLevel::create($data);

        return redirect()->route('position-levels.index')->with('success', 'Position level created successfully.');
    }

    public function show(PositionLevel $positionLevel)
    {
        return view('pages.organization.position-levels.show', [
            'title' => $positionLevel->level_name,
            'positionLevel' => $positionLevel,
        ]);
    }

    public function edit(PositionLevel $positionLevel)
    {
        return view('pages.organization.position-levels.edit', [
            'title' => 'Edit Position Level',
            'positionLevel' => $positionLevel,
        ]);
    }

    public function update(Request $request, PositionLevel $positionLevel)
    {
        $data = $request->validate([
            'level_name' => ['required', 'string', 'max:50', Rule::unique('position_levels', 'level_name')->ignore($positionLevel->id)],
        ]);

        $positionLevel->update($data);

        return redirect()->route('position-levels.index')->with('success', 'Position level updated successfully.');
    }

    public function destroy(PositionLevel $positionLevel)
    {
        $positionLevel->delete();

        return redirect()->route('position-levels.index')->with('success', 'Position level deleted successfully.');
    }
}
