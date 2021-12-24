<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Invites extends Model
{
    use Notifiable;

    protected $table = "invites";

    protected $fillable = [
        'user_id',
        'user_name',
        'track_code',
        'referred_code',
        'name',
        'email',
        'mobile',
        'email_send_at',
        'mobile_send_at',
        'see_at'
    ];

    protected $casts = [
        'mobile_send_at' => 'datetime',
        'email_send_at' => 'datetime',
        'see_at' => 'datetime'
    ];
}
