<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    protected $fillable = [
        'group_id',
        'user_id'
    ];

    // Each membership belongs to a group
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    // Each membership belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}