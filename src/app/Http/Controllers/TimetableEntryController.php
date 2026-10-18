<?php

namespace App\Http\Controllers;

use App\Models\TimetableEntry;

class TimetableEntryController extends Controller
{
    public function index()
    {
        $entries = TimetableEntry::orderBy('day')
            ->orderBy('period')
            ->get();

        return view('entries.index', [
            'entries' => $entries
        ]);
    }

    public function create()
    {
        return view('entries.create');
    }
    public function store()
{
    $data = request()->validate([
        'day' => 'required|integer|between:1,5',
        'period' => 'required|integer|between:1,5',
        'name' => 'required|string|max:255',
        'room' => 'required|string|max:255',
    ]);

    TimetableEntry::create($data);

    return redirect('/entries');
}
}
