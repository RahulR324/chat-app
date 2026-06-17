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
                                @if($selectedGroup) 
                                    <strong style="font-size:13px; color:#555; margin-bottom:2px; display:block;">{{ $msg->sender->name }}</strong> 
                                @endif
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
    let lastPrivateId = 0;
    let lastGroupId = 0;
    let lastMessageId = 0;

    if ("Notification" in window && Notification.permission === "default") {
        Notification.requestPermission();
    }

    /* Helper: Show UI Notification */
    function showNotification(title, message) {
        const notification = document.getElementById('chatNotification');
        if (notification) {
            document.getElementById('notificationTitle').innerText = title;
            document.getElementById('notificationMessage').innerText = message;
            notification.style.display = 'block';
            clearTimeout(notification.hideTimer);
            notification.hideTimer = setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }

        if ("Notification" in window && Notification.permission === "granted") {
            new Notification(title, {
                body: message,
                icon: "{{ asset('favicon.ico') }}"
            });
        }
    }
    const currentReceiverId = {{ $receiver ? $receiver->id : 'null' }};

    /* Poll for Global Notifications */
    function checkNotifications() {
        fetch('/check-notification')
        .then(response => response.json())
        .then(data => {
            if (data.private && lastPrivateId !== 0 && data.private.id > lastPrivateId) {
                if (
                    currentReceiverId === null ||
                    data.private.sender_id != currentReceiverId
                ){
                    showNotification(
                        data.private.sender_name,
                        data.private.message
                    );
                }
            }
            if (data.private) lastPrivateId = data.private.id;

            if (data.group && lastGroupId !== 0 && data.group.id > lastGroupId) {
                showNotification(data.group.group_name + ' - ' + data.group.sender_name, data.group.message);
            }
            if (data.group) lastGroupId = data.group.id;
        })
        .catch(error => console.log(error));
    }

    function escapeHtml(text)
    {
        const div =
            document.createElement('div');

            div.textContent = text;

        return div.innerHTML;
    }

    /* Append Message to UI */
    function appendMessage(message) {
        let senderInfo = '';
        @if($selectedGroup)
            if (message.sender_id != {{ session('user_id') }} && message.sender_name) {
                senderInfo = `<strong style="font-size:13px;color:#555;display:block;margin-bottom:2px;">${message.sender_name}</strong>`;
            }
        @endif

        let html = (message.sender_id == {{ session('user_id') }}) 
            ? `<div class="message-row right"><div class="message sent"><div>${escapeHtml(message.message)}</div><small class="message-time">${message.formatted_time}</small></div></div>`
            : `<div class="message-row left"><div class="message received">${senderInfo}<div>${escapeHtml(message.message)}</div><small class="message-time">${message.formatted_time}</small></div></div>`;

        document.getElementById('messages').insertAdjacentHTML('beforeend', html);
    }

    /* Load Messages */
    function loadMessages() {
        let fetchUrl = @if($receiver) '/messages/{{ $receiver->id }}' @else '/group/messages/{{ $selectedGroup->id }}' @endif;

        fetch(fetchUrl + '?last_id=' + lastMessageId)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                data.forEach(message => {
                    appendMessage(message);
                    lastMessageId = message.id;
                });
                let chatBox = document.getElementById('messages');
                let shouldScroll = 
                    chatBox.scrollHeight -
                    chatBox.scrollTop -
                    chatBox.clientHeight < 100;

                if(shouldScroll){
                    const nearBottom =
                    chatBox.scrollHeight -
                    chatBox.scrollTop -
                    chatBox.clientHeight < 150;

                    if (nearBottom)
                    {
                        chatBox.scrollTop =
                        chatBox.scrollHeight;
                    }
                }
            }
        })
        .catch(error => console.error('Fetch Error:', error));
    }

    /* Form Submission */
    const messageForm = document.getElementById('messageForm');
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            fetch(@if($receiver) '/send-message' @else '/group/send-message' @endif, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value },
                body: formData
            })
            .then(() => {
                document.getElementById('messageInput').value = '';
            })
            .catch(error => console.log(error));
        });
    }

    /* Initialize */
    @if($messages->count())
        lastMessageId = {{ $messages->last()->id }};
    @endif

    loadMessages();
    setInterval(loadMessages, 3000);
    checkNotifications();
    setInterval(checkNotifications, 5000);

    /* UI Interaction Logic */
    const modal = document.getElementById('groupModal');
    const openBtn = document.getElementById('openGroupModal');
    const closeBtn = document.getElementById('closeGroupModal');
    const chatContainer = document.getElementById('chatContainer');
    const openSidebarBtn = document.getElementById('openSidebar');
    const closeSidebarBtn = document.getElementById('closeSidebar');

    openSidebarBtn?.addEventListener('click', () => { chatContainer?.classList.add('sidebar-open'); document.body.style.overflow = 'hidden'; });
    closeSidebarBtn?.addEventListener('click', () => { chatContainer?.classList.remove('sidebar-open'); document.body.style.overflow = 'auto'; });
    openBtn?.addEventListener('click', () => { modal.style.display = 'flex'; modal.classList.add('show'); document.body.style.overflow = 'hidden'; });
    closeBtn?.addEventListener('click', () => { modal.style.display = 'none'; modal.classList.remove('show'); document.body.style.overflow = 'auto'; });
    
    window.addEventListener('click', (e) => { if (e.target === modal) { modal.style.display = 'none'; modal.classList.remove('show'); document.body.style.overflow = 'auto'; } });

    function updateViewportHeight()
    {
        let vh = window.visualViewport
            ? window.visualViewport.height * 0.01
            : window.innerHeight * 0.01;

            document.documentElement.style.setProperty(
            '--vh',
            `${vh}px`
        );
    }
    updateViewportHeight();
    window.addEventListener('resize', updateViewportHeight);
    if (window.visualViewport)
    {
        window.visualViewport.addEventListener(
            'resize',
            updateViewportHeight
        );
    }
</script>

</body>
</html>