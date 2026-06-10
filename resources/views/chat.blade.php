@php
    use Illuminate\Support\Str;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Chat Application</title>
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
</head>
<body>

<div class="chat-container">

    <div class="sidebar" id="sidebarUsers">
        <div class="sidebar-header">
            <h3>Chat Application</h3>
        </div>

        @foreach($users as $user)
            <a href="/chat?user={{ $user->id }}"
               class="user-item {{ request('user') == $user->id ? 'active-user' : '' }}">
                
                <div class="avatar">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                
                <div class="user-details">
                    <div class="user-top">
                        <strong>{{ $user->name }}</strong>
                        <span class="sidebar-time">{{ $user->last_message_time }}</span>
                    </div>
                    <div class="last-message">
                        {{ Str::limit($user->last_message, 30) }}
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="chat-area">
        @if($receiver)
            <div class="chat-header">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div class="avatar">
                        {{ strtoupper(substr($receiver->name, 0, 1)) }}
                    </div>
                    <strong>{{ $receiver->name }}</strong>
                </div>
            </div>

            <div id="messages" class="chat-box">
                @forelse($messages as $msg)
                    @if($msg->sender_id == session('user_id'))
                        <div class="message-row right">
                            <div class="message sent">
                                <div>{{ $msg->message }}</div>
                                <small class="message-time">{{ $msg->created_at->format('h:i A') }}</small>
                            </div>
                        </div>
                    @else
                        <div class="message-row left">
                            <div class="message received">
                                <div>{{ $msg->message }}</div>
                                <small class="message-time">{{ $msg->created_at->format('h:i A') }}</small>
                            </div>
                        </div>
                    @endif
                @empty
                    <p>No messages yet</p>
                @endforelse
            </div>

            <div class="chat-footer">
                <form id="messageForm" method="POST" action="/send-message" class="chat-form">
                    @csrf
                    <input type="hidden" name="receiver_id" value="{{ $receiver->id }}">
                    <input type="text" name="message" id="messageInput" placeholder="Type a message..." required>
                    <button type="submit">Send</button>
                </form>
            </div>
        @else
            <div class="chat-header">Chat Application</div>
            <div class="chat-box">
                <div style="text-align:center; margin-top:100px;">
                    <h2>Welcome {{ session('user_name') }}</h2>
                    <p>Select a user to start chatting</p>
                </div>
            </div>
        @endif
    </div>
</div>

@if($receiver)
<script>
    function loadMessages() {
        fetch('/messages/{{ $receiver->id }}')
        .then(response => response.json())
        .then(data => {
            let html = '';
            data.forEach(message => {
                if(message.sender_id == {{ session('user_id') }}) {
                    html += `
                        <div class="message-row right">
                            <div class="message sent">
                                <div>${message.message}</div>
                                <small class="message-time">${message.formatted_time}</small>
                            </div>
                        </div>`;
                } else {
                    html += `
                        <div class="message-row left">
                            <div class="message received">
                                <div>${message.message}</div>
                                <small class="message-time">${message.formatted_time}</small>
                            </div>
                        </div>`;
                }
            });

            document.getElementById('messages').innerHTML = html;
            let chatBox = document.getElementById('messages');
            chatBox.scrollTop = chatBox.scrollHeight;
        });
    }

    document.getElementById('messageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        fetch('/send-message', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('messageInput').value = '';
            loadMessages();
        });
    });

    // Initial load and interval
    loadMessages();
    setInterval(loadMessages, 2000);

    function loadSidebar() {
        fetch('/chat-sidebar')
        .then(response => response.json())
        .then(users => {
            let html = `
                <div class="sidebar-header">
                    {{ session('user_name') }}
                </div>
            `;

            users.forEach(user => {
                html += `
                    <a href="/chat?user=${user.id}" class="user-item">
                        <div class="avatar">
                            ${user.name.charAt(0).toUpperCase()}
                        </div>
                        <div class="user-details">
                            <div class="user-top">
                                <strong>${user.name}</strong>
                                <span class="sidebar-time">${user.last_message_time ?? ''}</span>
                            </div>
                            <div class="last-message">
                                ${user.last_message ?? ''}
                            </div>
                        </div>
                    </a>
                `;
            });

            document.getElementById('sidebarUsers').innerHTML = html;
        });
    }
</script>
@endif

</body>
</html>