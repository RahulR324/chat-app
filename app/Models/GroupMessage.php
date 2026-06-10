<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMessage extends Model
{
    protected $fillable = [
        'group_id',
        'sender_id',
        'message'
    ];

    // Message belongs to a group
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    // Message belongs to a sender (user)
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}