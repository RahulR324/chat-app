<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'group_name'
    ];

    // A group has many messages
    public function messages()
    {
        return $this->hasMany(GroupMessage::class);
    }

    // A group has many members
    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }
}