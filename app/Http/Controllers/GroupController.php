<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupMessage;

class GroupController extends Controller
{
    public function storeGroup(Request $request)
    {
        $request->validate([
            'group_name' => 'required',
            'members'    => 'required|array'
        ]);

        // Create Group
        $group = Group::create([
            'group_name' => $request->group_name
        ]);

        // Ensure creator is added automatically
        $members = $request->members;

        if (!in_array(session('user_id'), $members)) {
            $members[] = session('user_id');
        }

        foreach ($members as $userId) {
            GroupMember::create([
                'group_id' => $group->id,
                'user_id'  => $userId
            ]);
        }

        return redirect('/chat')
            ->with('success', 'Group created successfully!');
    }

    public function openGroupChat($id)
    {
        $group = Group::findOrFail($id);

        $isMember = GroupMember::where('group_id', $id)
            ->where('user_id', session('user_id'))
            ->exists();

        if (!$isMember) {
            return redirect('/chat')
                ->with('error', 'You are not a member of this group');
        }

        $messages = GroupMessage::where('group_id', $id)
            ->orderBy('id', 'asc')
            ->get();

        return view('group-chat', compact('group', 'messages'));
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'group_id' => 'required',
            'message'  => 'required'
        ]);

        GroupMessage::create([
            'group_id'  => $request->group_id,
            'sender_id' => session('user_id'),
            'message'   => $request->message
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    public function loadMessages(Request $request, $id)
    {
        $lastId = $request->get('last_id', 0);

        $messages = GroupMessage::with('sender')
            ->where('group_id', $id)
            ->where('id', '>', $lastId)
            ->orderBy('id')
            ->get();

        return response()->json(
            $messages->map(function ($msg) {
                return [
                    'id'             => $msg->id,
                    'sender_id'      => $msg->sender_id,
                    'message'        => $msg->message,
                    'sender_name'    => $msg->sender->name,
                    'formatted_time' => $msg->created_at->format('h:i A')
                ];
            })
        );
    }
}