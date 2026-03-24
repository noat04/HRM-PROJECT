<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::paginate(10);

        return Inertia::render('Shifts/Index', [
            'shifts' => $shifts,
        ]);
    }

    public function create()
    {
        return Inertia::render('Shifts/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'grace_period' => 'nullable|integer',
            'work_days' => 'required|array',
            'work_days.*' => 'string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'description' => 'nullable|string|max:255',
            'start_time' => 'required|string|max:255',
            'end_time' => 'required|string|max:255',
            'status' => 'required|string|max:255',
        ]);

        Shift::create($request->all());

        return redirect()->route('shifts.index');
    }

    public function show(Shift $shift)
    {
        return Inertia::render('Shifts/Show', [
            'shift' => $shift,
        ]);
    }

    public function edit(Shift $shift)
    {
        return Inertia::render('Shifts/Edit', [
            'shift' => $shift,
        ]);
    }

    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|string|max:255',
            'end_time' => 'required|string|max:255',
            'status' => 'required|string|max:255',
        ]);

        $shift->update($request->all());

        return redirect()->route('shifts.index');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();

        return redirect()->route('shifts.index');
    }
}
