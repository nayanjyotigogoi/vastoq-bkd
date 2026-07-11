<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavedSearch;
use Illuminate\Http\Request;

class SavedSearchController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $searches = SavedSearch::where('user_id', $request->user_id)
            ->where('is_active', true)
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $searches]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name'    => 'required|string|max:100',
            'filters' => 'required|array',
        ]);

        // Limit to 10 saved searches per user
        $count = SavedSearch::where('user_id', $request->user_id)->where('is_active', true)->count();
        if ($count >= 10) {
            return response()->json([
                'success' => false,
                'error'   => ['message' => 'You can save up to 10 searches. Delete one to add more.'],
            ], 422);
        }

        $search = SavedSearch::create([
            'user_id' => $request->user_id,
            'name'    => $request->name,
            'filters' => $request->filters,
        ]);

        return response()->json(['success' => true, 'data' => $search], 201);
    }

    public function destroy(Request $request, $id)
    {
        $search = SavedSearch::findOrFail($id);
        $search->update(['is_active' => false]);

        return response()->json(['success' => true, 'message' => 'Search alert removed.']);
    }
}
