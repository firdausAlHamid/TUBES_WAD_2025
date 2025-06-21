<?php

namespace App\Http\Controllers;

use App\Models\CustomJuz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CustomJuzController extends Controller
{
    public function index()
    {
        $customJuzs = auth()->user()->customJuzs()->orderBy('created_at', 'desc')->get();
        return view('user.quran.juz.index', compact('customJuzs'));
    }

    public function create()
    {
        return view('user.quran.juz.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'juz_number' => 'required|integer|between:1,30',
            'edition' => 'required|string'
        ]);

        $customJuz = auth()->user()->customJuzs()->create($request->all());

        return redirect()->route('juz.show', $customJuz->id)
            ->with('success', 'Juz berhasil ditambahkan');
    }

    public function show(CustomJuz $customJuz)
    {
        $this->authorize('view', $customJuz);
        
        $response = Http::get("http://api.alquran.cloud/v1/juz/{$customJuz->juz_number}/{$customJuz->edition}");
        
        if ($response->successful()) {
            $juzData = $response->json()['data'];
            return view('user.quran.juz.show', compact('customJuz', 'juzData'));
        }

        return back()->with('error', 'Gagal memuat data juz dari API');
    }

    public function edit(CustomJuz $customJuz)
    {
        $this->authorize('update', $customJuz);
        return view('user.quran.juz.edit', compact('customJuz'));
    }

    public function update(Request $request, CustomJuz $customJuz)
    {
        $this->authorize('update', $customJuz);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $customJuz->update($request->only(['name', 'description']));

        return redirect()->route('juz.show', $customJuz->id)
            ->with('success', 'Juz berhasil diperbarui');
    }

    public function destroy(CustomJuz $customJuz)
    {
        $this->authorize('delete', $customJuz);
        
        $customJuz->delete();

        return redirect()->route('juz.index')
            ->with('success', 'Juz berhasil dihapus');
    }
} 