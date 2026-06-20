<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFurnitureEnquiryRequest;
use App\Models\FurnitureEnquiry;
use Illuminate\Http\Request;

class FurnitureEnquiryController extends Controller
{
    /**
     * Store a new furniture enquiry.
     */
    public function store(StoreFurnitureEnquiryRequest $request)
    {
        $enquiry = FurnitureEnquiry::create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Furniture enquiry submitted successfully.',
            'data' => $enquiry
        ], 201);
    }

    /**
     * List all enquiries.
     * (Admin use later)
     */
    public function index()
    {
        $enquiries = FurnitureEnquiry::with('furniture')
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $enquiries
        ]);
    }

    /**
     * Show a single enquiry.
     */
    public function show($id)
    {
        $enquiry = FurnitureEnquiry::with([
                'furniture',
                'user'
            ])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $enquiry
        ]);
    }

    /**
     * Update enquiry status.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => [
                'required',
                'in:pending,accepted,declined,completed'
            ],
            'admin_notes' => [
                'nullable',
                'string'
            ]
        ]);

        $enquiry = FurnitureEnquiry::findOrFail($id);

        $enquiry->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Enquiry updated successfully.',
            'data' => $enquiry
        ]);
    }

    /**
     * Delete enquiry.
     */
    public function destroy($id)
    {
        $enquiry = FurnitureEnquiry::findOrFail($id);

        $enquiry->delete();

        return response()->json([
            'success' => true,
            'message' => 'Enquiry deleted successfully.'
        ]);
    }
}