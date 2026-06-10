<!DOCTYPE html>
<html>
<head>
    <title>Chat Login</title>
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
</head>
<body>

<div class="login-container">

    <div class="login-card">

        <h2>Chat Application</h2>

        <form method="POST" action="/login">

            @csrf

            <input
                type="email"
                name="email"
                placeholder="Enter your email"
                required
            >

            <button type="submit">
                Enter Chat
            </button>

        </form>

        @if(session('error'))
            <p style="color:red; margin-top:15px; text-align:center;">
                {{ session('error') }}
            </p>
        @endif

    </div>

</div>

</body>
</html>