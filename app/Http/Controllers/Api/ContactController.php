<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactAdminMail;
use App\Mail\ContactAutoReplyMail;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|digits:10',
            'subject' => 'required|string|max:200',
            'type'    => 'nullable|in:general,grievance,payment,listing,worker',
            'message' => 'required|string|max:2000',
        ]);

        $contact = ContactMessage::create([
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'subject' => $request->subject,
            'type'    => $request->type ?? 'general',
            'message' => $request->message,
        ]);

        // Auto-reply to sender
        try {
            Mail::to($request->email)->send(
                new ContactAutoReplyMail($request->name, $request->subject, $request->input('message'))
            );
        } catch (\Throwable $e) {
            Log::error('[CONTACT] Auto-reply failed', ['error' => $e->getMessage()]);
        }

        // Notify admin
        try {
            Mail::to(config('services.admin_email'))->send(
                new ContactAdminMail($contact)
            );
        } catch (\Throwable $e) {
            Log::error('[CONTACT] Admin notify failed', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Your message has been received. We will reply within 24 hours.',
        ]);
    }

    public function index(Request $request)
    {
        $query = ContactMessage::latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->paginate(20),
        ]);
    }
}
