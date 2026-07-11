<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'subject', 'type', 'message',
        'status', 'admin_reply', 'replied_at', 'reply_sent_at',
    ];

    protected $casts = [
        'replied_at'    => 'datetime',
        'reply_sent_at' => 'datetime',
    ];
}
