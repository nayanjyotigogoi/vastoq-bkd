<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFurnitureRequest;
use App\Http\Requests\UpdateFurnitureRequest;
use App\Models\Furniture;
use Illuminate\Http\Request;

class FurnitureController extends Controller
{
    /**
     * List furniture.
     */
    public function index(Request $request)
    {
        $query = Furniture::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('is_available')) {
            $query->where(
                'is_available',
                $request->boolean('is_available')
            );
        }

        $furniture = $query
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $furniture
        ]);
    }

    /**
     * Store furniture.
     */
    public function store(StoreFurnitureRequest $request)
    {
        $furniture = Furniture::create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Furniture created successfully.',
            'data' => $furniture
        ], 201);
    }

    /**
     * Show furniture.
     */
    public function show($id)
    {
        $furniture = Furniture::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $furniture
        ]);
    }

    /**
     * Update furniture.
     */
    public function update(
        UpdateFurnitureRequest $request,
        $id
    ) {
        $furniture = Furniture::findOrFail($id);

        $furniture->update(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Furniture updated successfully.',
            'data' => $furniture
        ]);
    }

    /**
     * Delete furniture.
     */
    public function destroy($id)
    {
        $furniture = Furniture::findOrFail($id);

        $furniture->delete();

        return response()->json([
            'success' => true,
            'message' => 'Furniture deleted successfully.'
        ]);
    }
}