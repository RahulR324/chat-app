# Laravel Chat Application

A modern chat application built with Laravel, MySQL, AJAX, Sessions, HTML, CSS, and JavaScript. The application supports both one-to-one private messaging and group conversations with a responsive, WhatsApp-inspired interface.

## Features

### User Access

* Session-based user access using email
* No registration or authentication system required
* Laravel session management
* User information stored in session after login

### Private Chat

* One-to-one messaging
* Real-time message updates using AJAX polling
* Automatic chat refresh without page reload
* Latest message preview in sidebar
* Message timestamps
* Active chat highlighting

### Group Chat

* Create chat groups
* Add multiple members to a group
* Group creator automatically added as a member
* Group conversations within the main chat interface
* Group listing in sidebar
* Display sender names inside group conversations
* Real-time group message updates

### User Interface

* WhatsApp-inspired design
* Responsive layout for desktop, tablet, and mobile devices
* User sidebar
* Group sidebar section
* Modern chat bubbles
* Automatic scrolling to latest messages

### Database Integration

* MySQL database
* User management
* Private messages storage
* Group management
* Group member management
* Group message management

---

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

---

## Database Tables

### users

Stores application users.

### messages

Stores private chat messages.

### groups

Stores group information.

### group_members

Stores group membership data.

### group_messages

Stores messages sent within groups.

---

## Application Flow

### Private Chat

1. User enters email address.
2. System verifies the email.
3. User information is stored in Laravel Session.
4. User is redirected to the chat page.
5. Available users are displayed in the sidebar.
6. Selecting a user opens the conversation.
7. Messages are stored in the messages table.
8. AJAX updates conversations automatically.

### Group Chat

1. User clicks Create Group.
2. Group name is entered.
3. Members are selected.
4. Group is created.
5. Creator is automatically added to the group.
6. Group appears in the sidebar.
7. Members can send and receive messages.
8. Messages are stored in the group_messages table.
9. AJAX refreshes group conversations automatically.

---

## Session Management

The application uses Laravel Sessions to maintain user state.

### Session Data

```php
session([
    'user_id' => $user->id,
    'user_name' => $user->name,
    'user_email' => $user->email
]);
```

### Session Usage

* Identify logged-in users
* Send private messages
* Send group messages
* Load conversations
* Display user information
* Verify group membership

---

## Author

**Rahul R**

Software Engineer Trainee | Full Stack Developer

Built using Laravel, PHP, MySQL, AJAX, JavaScript, HTML, and CSS.
