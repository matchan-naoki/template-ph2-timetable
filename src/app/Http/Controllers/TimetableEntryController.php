<?php

namespace App\Http\Controllers;

use App\Models\TimetableEntry;
use Illuminate\Http\Request;

class TimetableEntryController extends Controller
{
    // 授業一覧
    public function index()
    {
        $entries = TimetableEntry::orderBy('day')
            ->orderBy('period')
            ->get();

        return view('entries.index', [
            'entries' => $entries
        ]);
    }

    // 登録画面
    public function create()
    {
        return view('entries.create');
    }

    // 新規登録
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

    // 編集画面
    public function edit($id)
    {
        $entry = TimetableEntry::findOrFail($id);

        return view('entries.edit', [
            'entry' => $entry
        ]);
    }

    // 編集内容を保存
    public function update(Request $request, $id)
    {
        $entry = TimetableEntry::findOrFail($id);

        $data = $request->validate([
            'day' => 'required|integer|between:1,5',
            'period' => 'required|integer|between:1,5',
            'name' => 'required|string|max:255',
            'room' => 'required|string|max:255',
        ]);

        $entry->update($data);

        return redirect('/entries');
    }

    // 授業を削除
    public function destroy($id)
    {
        $entry = TimetableEntry::findOrFail($id);

        $entry->delete();

        return redirect('/entries');
    }

    public function destroyAll()
{
    TimetableEntry::query()->delete();

    return redirect('/entries');
}
}
