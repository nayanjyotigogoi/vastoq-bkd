<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * POST /uploads/listing-photos
     * Accepts one or more image files, stores them on the public disk,
     * and returns their publicly accessible URLs.
     */
    public function listingPhotos(Request $request)
    {
        $request->validate([
            'photos'   => 'required|array|max:6', // keep total payload under post_max_size
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:1900', // ~1.9MB each, under upload_max_filesize=2M
        ]);

        $urls = [];

        foreach ($request->file('photos') as $file) {
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path     = $file->storeAs('listings', $filename, 'public');
            $urls[]   = Storage::disk('public')->url($path);
        }

        return response()->json([
            'success' => true,
            'data'    => ['urls' => $urls],
        ], 201);
    }

    /**
     * POST /uploads/profile-photo
     * Single image upload, used for worker/owner profile photos.
     */
    public function profilePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120', // 5MB
        ]);

        $file     = $request->file('photo');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs('profiles', $filename, 'public');
        $url      = Storage::disk('public')->url($path);

        return response()->json([
            'success' => true,
            'data'    => ['url' => $url],
        ], 201);
    }
}
