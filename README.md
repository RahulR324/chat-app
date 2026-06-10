# Laravel Simple Chat Application

A simple one-to-one real-time chat application built with Laravel, MySQL, AJAX, Sessions, HTML, CSS, and JavaScript.

## Features

* Session-based user access using email
* No registration or authentication system required
* One-to-one private messaging
* WhatsApp-inspired user interface
* Responsive design for desktop, tablet, and mobile devices
* Real-time message updates using AJAX polling
* Automatic chat refresh without page reload
* User sidebar with latest message preview
* Message timestamps
* Active chat highlighting
* MySQL database integration

## Tech Stack

### Backend

* Laravel
* PHP
* MySQL

### Frontend

* HTML
* CSS
* JavaScript
* AJAX


## Application Flow

1. User enters their email address.
2. System verifies the email from the users table.
3. User information is stored in Laravel Session.
4. User is redirected to the chat page.
5. Available users are displayed in the sidebar.
6. Selecting a user opens the conversation.
7. Messages are stored in the messages table.
8. AJAX automatically updates conversations and chat previews.

## Session Management

The application uses Laravel Sessions to maintain user state.

Session Data:

```php
session([
    'user_id' => $user->id,
    'user_name' => $user->name
]);
```

The session is used to:

* Identify the logged-in user
* Send messages
* Load conversations
* Display user information


## Author

Rahul R

Software Engineer Trainee | Full Stack Developer

Built using Laravel and MySQL.
