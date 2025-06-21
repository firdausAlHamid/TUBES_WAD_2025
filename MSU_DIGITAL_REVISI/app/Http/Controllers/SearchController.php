<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SearchHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    /**
     * Display a listing of the user's search history.
     */
    public function index()
    {
        $history = SearchHistory::where('user_id', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();
        
        return response()->json($history);
    }

    /**
     * Store a newly created search term in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'term' => 'required|string|min:2|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $term = $request->input('term');

        // Save search term to history
        SearchHistory::updateOrCreate(
            ['user_id' => Auth::id(), 'term' => $term],
            ['updated_at' => now()] // Touch timestamp to bring recent searches to the top
        );

        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified search history item from storage.
     */
    public function destroy(SearchHistory $searchHistory)
    {
        // Authorize that the user owns this search history item
        if ($searchHistory->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $searchHistory->delete();

        return response()->json(['success' => true]);
    }
}
