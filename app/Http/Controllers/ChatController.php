<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;

class ChatController extends Controller
{
    public function loginPage()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return redirect('/')->with('error', 'Email not found');
        }

        session([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email
        ]);

        return redirect('/chat');
    }

    public function chat(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect('/');
        }

        $users = User::where('id', '!=', session('user_id'))->get();

        foreach ($users as $user) {
            $lastMessage = Message::where(function ($query) use ($user) {
                $query->where('sender_id', session('user_id'))
                      ->where('receiver_id', $user->id);
            })
            ->orWhere(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->where('receiver_id', session('user_id'));
            })
            ->latest('id')
            ->first();

            $user->last_message = $lastMessage ? $lastMessage->message : 'No messages yet';
            $user->last_message_time = $lastMessage ? $lastMessage->created_at->format('h:i A') : '';
        }

        $receiver = null;
        $messages = collect();

        if ($request->user) {
            $receiver = User::find($request->user);

            if ($receiver) {
                $messages = Message::where(function ($query) use ($receiver) {
                    $query->where('sender_id', session('user_id'))
                          ->where('receiver_id', $receiver->id);
                })
                ->orWhere(function ($query) use ($receiver) {
                    $query->where('sender_id', $receiver->id)
                          ->where('receiver_id', session('user_id'));
                })
                ->orderBy('id', 'asc')
                ->get();
            }
        }

        return view('chat', compact('users', 'receiver', 'messages'));
    }

    public function sendMessage(Request $request)
    {
        Message::create([
            'sender_id'   => session('user_id'),
            'receiver_id' => $request->receiver_id,
            'message'     => $request->message
        ]);

        return response()->json(['success' => true]);
    }

    public function loadMessages($userId)
    {
        $messages = Message::where(function ($query) use ($userId) {
            $query->where('sender_id', session('user_id'))
                  ->where('receiver_id', $userId);
        })
        ->orWhere(function ($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', session('user_id'));
        })
        ->orderBy('id', 'asc')
        ->get();

        $messages = $messages->map(function ($message) {
            $message->formatted_time = $message->created_at->format('h:i A');
            return $message;
        });

        return response()->json($messages);
    }
}