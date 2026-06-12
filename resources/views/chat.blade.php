@php
    use Illuminate\Support\Str;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
    <title>Chat Application</title>
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<div class="chat-container{{ $receiver || $selectedGroup ? ' chat-active' : '' }}" id="chatContainer">

    <div class="sidebar" id="sidebarUsers">
        <div class="sidebar-header">
            <button id="closeSidebar" class="sidebar-toggle close-sidebar" type="button" aria-label="Back to chats">
                <i class="bi bi-arrow-left-short"></i>
            </button>
            <h3>Chat</h3>
        </div>

        <div style="padding:10px;">
            <button id="openGroupModal" class="create-group-btn">
                <i class="bi bi-plus-circle"></i> Create Group
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

        <div style="padding:10px; color:#777; font-size:12px; margin-top:8px;">USERS</div>
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
                <button id="openSidebar" class="sidebar-toggle open-sidebar" type="button" aria-label="Open chats">
                    <i class="bi bi-list"></i>
                </button>
                <div style="display:flex; align-items:center; gap:10px; flex:1; min-width:0;">
                    <div class="avatar">
                        @if($selectedGroup)
                            <i class="bi bi-people-fill"></i>
                        @else
                            <i class="bi bi-person-fill"></i>
                        @endif
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
                                @if($selectedGroup) <strong style="font-size:13px; color:#555; margin-bottom:2px; display:block;">{{ $msg->sender->name }}</strong> @endif
                                <div>{{ $msg->message }}</div>
                                <small class="message-time">{{ $msg->created_at->format('h:i A') }}</small>
                            </div>
                        </div>
                    @endif
                @empty
                    <p style="text-align:center; color:#999;">No messages yet</p>
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
                    <input type="text" name="message" id="messageInput" placeholder="Message..." required>
                    <button type="submit" title="Send"><i class="bi bi-send-fill"></i></button>
                </form>
            </div>
        @else
            <div class="chat-header">Chat Application</div>
            <div class="chat-box">
                <div style="text-align:center; margin-top:auto; margin-bottom:auto;">
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
            <div style="max-height: 250px; overflow-y: auto;">
                @foreach($users as $user)
                    <label>
                        <input type="checkbox" name="members[]" value="{{ $user->id }}">
                        {{ $user->name }}
                    </label><br>
                @endforeach
            </div>
            <button type="submit">Create Group</button>
        </form>
    </div>
</div>

<div id="chatNotification" class="chat-notification">
    <strong id="notificationTitle"></strong>
    <p id="notificationMessage"></p>
</div>

<script>
    let previousMessageCount = 0;
    let lastPrivateId = 0;
    let lastGroupId = 0;
    if ("Notification" in window)
    {
        Notification.requestPermission();
    }

    /* ==========================
       Notification
    ========================== */
    function showNotification(title, message)
    {
        const notification =
            document.getElementById('chatNotification');

        if(notification)
        {
            document.getElementById('notificationTitle')
                .innerText = title;

            document.getElementById('notificationMessage')
                .innerText = message;

            notification.style.display = 'block';

            clearTimeout(notification.hideTimer);

            notification.hideTimer = setTimeout(() => {

                notification.style.display = 'none';

            }, 3000);
        }

        // Browser Notification

        if(
            "Notification" in window &&
            Notification.permission === "granted"
        )
        {
            new Notification(title, {

                body: message,

                icon: "{{ asset('favicon.ico') }}"

            });
        }
    }

    /* ==========================
       Global Notifications
    ========================== */
    function checkNotifications() {
        fetch('/check-notification')
        .then(response => response.json())
        .then(data => {
            if (data.private) {
                if (lastPrivateId !== 0 && data.private.id > lastPrivateId) {
                    showNotification(data.private.sender_name, data.private.message);
                }
                lastPrivateId = data.private.id;
            }

            if (data.group) {
                if (lastGroupId !== 0 && data.group.id > lastGroupId) {
                    showNotification(data.group.group_name + ' - ' + data.group.sender_name, data.group.message);
                }
                lastGroupId = data.group.id;
            }
        })
        .catch(error => console.log(error));
    }

    /* ==========================
       Load Messages
    ========================== */
    @if($receiver || $selectedGroup)
    function loadMessages() {
        @if($receiver)
            let fetchUrl = '/messages/{{ $receiver->id }}';
        @else
            let fetchUrl = '/group/messages/{{ $selectedGroup->id }}';
        @endif

        fetch(fetchUrl)
        .then(response => response.json())
        .then(data => {
            if (previousMessageCount > 0 && data.length > previousMessageCount) {
                let latestMessage = data[data.length - 1];
                if (latestMessage.sender_id != {{ session('user_id') }}) {
                    let senderName = 'New Message';
                    @if($receiver)
                        senderName = '{{ $receiver ? $receiver->name : "" }}';
                    @else
                        senderName = latestMessage.sender_name;
                    @endif
                    showNotification(senderName, latestMessage.message);
                }
            }

            previousMessageCount = data.length;
            let html = '';
            data.forEach(message => {
                let senderInfo = '';
                @if($selectedGroup)
                    if (message.sender_id != {{ session('user_id') }}) {
                        senderInfo = `<strong style="font-size:13px; color:#555; margin-bottom:2px; display:block;">${message.sender_name}</strong>`;
                    }
                @endif 

                if (message.sender_id == {{ session('user_id') }}) {
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
        })
        .catch(error => console.error('Fetch Error:', error));
    }

    const messageForm = document.getElementById('messageForm');
    if (messageForm) {
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
    @endif

    /* ==========================
       Group Modal Logic
    ========================== */
    const modal = document.getElementById('groupModal');
    const openBtn = document.getElementById('openGroupModal');
    const closeBtn = document.getElementById('closeGroupModal');
    const chatContainer = document.getElementById('chatContainer');
    const openSidebarBtn = document.getElementById('openSidebar');
    const closeSidebarBtn = document.getElementById('closeSidebar');

    if (openSidebarBtn) {
        openSidebarBtn.addEventListener('click', () => {
            chatContainer?.classList.add('sidebar-open');
            document.body.style.overflow = 'hidden';
        });
    }

    if (closeSidebarBtn) {
        closeSidebarBtn.addEventListener('click', () => {
            chatContainer?.classList.remove('sidebar-open');
            document.body.style.overflow = 'auto';
        });
    }

    if (openBtn) openBtn.addEventListener('click', () => {
        modal.style.display = 'flex';
        modal.classList.add('show');
    });
    
    if (closeBtn) closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
        modal.classList.remove('show');
    });

    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    });

    function updateViewportHeight() {
        document.documentElement.style.setProperty('--vh', `${window.innerHeight * 0.01}px`);
    }

    updateViewportHeight();
    window.addEventListener('resize', updateViewportHeight);

    /* ==========================
       Start Global Notifications
    ========================== */
    checkNotifications();
    setInterval(checkNotifications, 2000);

    /* ==========================
       Prevent body scroll on mobile when modal is open
    ========================== */
    const originalModal = document.getElementById('groupModal');
    const originalOpenBtn = document.getElementById('openGroupModal');
    
    if (originalOpenBtn) {
        originalOpenBtn.addEventListener('click', () => {
            document.body.style.overflow = 'hidden';
        });
    }

    if (originalModal) {
        window.addEventListener('click', (event) => {
            if (event.target === originalModal) {
                document.body.style.overflow = 'auto';
            }
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            document.body.style.overflow = 'auto';
        });
    }
</script>

</body>
</html>