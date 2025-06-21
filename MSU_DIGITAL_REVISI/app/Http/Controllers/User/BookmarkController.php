<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    public function index()
    {
        $bookmarks = Bookmark::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.bookmarks.index', compact('bookmarks'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'api_ayat_identifier' => 'required|string',
            'surah_number' => 'required|integer',
            'ayah_number' => 'required|integer'
        ]);

        $bookmark = Bookmark::where('user_id', Auth::id())
            ->where('api_ayat_identifier', $request->api_ayat_identifier)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            return response()->json([
                'success' => true,
                'is_bookmarked' => false,
                'message' => 'Bookmark dihapus'
            ]);
        }

        Bookmark::create([
            'user_id' => Auth::id(),
            'api_ayat_identifier' => $request->api_ayat_identifier,
            'surah_number' => $request->surah_number,
            'ayah_number' => $request->ayah_number
        ]);

        return response()->json([
            'success' => true,
            'is_bookmarked' => true,
            'message' => 'Ayat ditandai'
        ]);
    }

    public function destroy(Bookmark $bookmark)
    {
        if ($bookmark->user_id !== Auth::id()) {
            return redirect()->route('bookmarks.index')
                ->with('error', 'Anda tidak memiliki akses untuk menghapus bookmark ini.');
        }

        $bookmark->delete();
        return redirect()->route('bookmarks.index')
            ->with('success', 'Bookmark berhasil dihapus.');
    }
} 