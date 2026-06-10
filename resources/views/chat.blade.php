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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<div class="chat-container">

    <div class="sidebar" id="sidebarUsers">
        <div class="sidebar-header">
            <h3>Chat Application</h3>
        </div>

        <div style="padding:10px;">
            <button id="openGroupModal" class="create-group-btn">
                Create Group
            </button>
        </div>

        <div class="section-title">GROUPS</div>
        @foreach($groups as $group)
            <a href="/chat?group={{ $group->id }}"
                class="user-item group-item {{ request('group') == $group->id ? 'active-user' : '' }}">
                <div class="avatar"><i class="bi bi-people-fill"></i></div>
                <div class="user-details">
                    <strong>{{ $group->group_name }}</strong>
                </div>
            </a>
        @endforeach

        <div style="padding:10px; color:#777; font-size:12px;">USERS</div>
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
        @if($receiver || $selectedGroup)
            <div class="chat-header">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div class="avatar">
                        <i class="bi bi-person-fill"></i> {{ strtoupper(substr(($receiver ? $receiver->name : $selectedGroup->group_name), 0, 1)) }}
                    </div>
                    <strong>{{ $receiver ? $receiver->name : $selectedGroup->group_name }}</strong>
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
                                @if($selectedGroup) <strong>{{ $msg->sender->name }}</strong><br> @endif
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
                    @if($receiver)
                        <input type="hidden" name="receiver_id" value="{{ $receiver->id }}">
                    @elseif($selectedGroup)
                        <input type="hidden" name="group_id" value="{{ $selectedGroup->id }}">
                    @endif
                    <input type="text" name="message" id="messageInput" placeholder="Type a message..." required>
                    <button type="submit">Send</button>
                </form>
            </div>
        @else
            <div class="chat-header">Chat Application</div>
            <div class="chat-box">
                <div style="text-align:center; margin-top:100px;">
                    <h2>Welcome {{ session('user_name') }}</h2>
                    <p>Select a user or group to start chatting</p>
                </div>
            </div>
        @endif
    </div>
</div>

<div id="groupModal" class="modal">
    <div class="modal-content">
        <span id="closeGroupModal" class="close">&times;</span>
        <h3>Create Group</h3>
        <form method="POST" action="/group/store">
            @csrf
            <input type="text" name="group_name" placeholder="Group Name" required>
            <h4>Select Members</h4>
            @foreach($users as $user)
                <label>
                    <input type="checkbox" name="members[]" value="{{ $user->id }}">
                    {{ $user->name }}
                </label><br>
            @endforeach
            <br>
            <button type="submit">Create Group</button>
        </form>
    </div>
</div>

@if($receiver || $selectedGroup)
<script>
    function loadMessages() {
        @if($receiver)
            let fetchUrl = '/messages/{{ $receiver->id }}';
        @else
            let fetchUrl = '/group/messages/{{ $selectedGroup->id }}';
        @endif

        fetch(fetchUrl)
        .then(response => response.json())
        .then(data => {
            let html = '';
            data.forEach(message => {
                let senderInfo = '';
                @if($selectedGroup)
                    if(message.sender_id != {{ session('user_id') }}) {
                        senderInfo = `<strong>${message.sender_name}</strong><br>`;
                    }
                @endif

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
                                ${senderInfo}
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

    const messageForm = document.getElementById('messageForm');
    if(messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            let sendUrl = @if($receiver) '/send-message' @else '/group/send-message' @endif;

            fetch(sendUrl, {
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
            })
            .catch(error => console.log(error));
        });
    }

    loadMessages();
    setInterval(loadMessages, 2000);
</script>
@endif

<script>
    const modal = document.getElementById('groupModal');
    const openBtn = document.getElementById('openGroupModal');
    const closeBtn = document.getElementById('closeGroupModal');

    if(openBtn) openBtn.addEventListener('click', () => modal.style.display = 'block');
    if(closeBtn) closeBtn.addEventListener('click', () => modal.style.display = 'none');
    
    window.addEventListener('click', (event) => {
        if(event.target === modal) modal.style.display = 'none';
    });
</script>

</body>
</html>