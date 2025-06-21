<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index()
    {
        $bookmarks = auth()->user()->bookmarks()
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('user.bookmarks.index', compact('bookmarks'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'api_ayat_identifier' => 'required|string'
        ]);

        $parts = explode(':', $request->api_ayat_identifier);
        if (count($parts) !== 2) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid ayat identifier format'
            ]);
        }

        $surahNumber = $parts[0];
        $ayahNumber = $parts[1];

        $bookmark = auth()->user()->bookmarks()
            ->where('api_ayat_identifier', $request->api_ayat_identifier)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            $message = 'Bookmark berhasil dihapus';
            $bookmarked = false;
        } else {
            $bookmark = auth()->user()->bookmarks()->create([
                'api_ayat_identifier' => $request->api_ayat_identifier,
                'surah_number' => $surahNumber,
                'ayah_number' => $ayahNumber
            ]);
            $message = 'Bookmark berhasil ditambahkan';
            $bookmarked = true;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'bookmarked' => $bookmarked
        ]);
    }

    public function destroy(Bookmark $bookmark)
    {
        $this->authorize('delete', $bookmark);
        
        $bookmark->delete();

        return back()->with('success', 'Bookmark berhasil dihapus');
    }
} 