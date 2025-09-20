🌐 Social Network Project

A mini social networking web app built with PHP (Core, no frameworks), MySQL, jQuery, Bootstrap, and AJAX.
Developed as part of Webkul Assignment (2026 Batch Placement Drive).

✨ Features

🔑 User Authentication (Signup / Login / Logout)

👤 Profile Management (Update profile picture, name, age, email)

📝 Create Posts (with text & image upload)

👍👎 Like / Dislike System (realtime counts per post)

💬 Comment System (threaded comments under each post)

🎨 Modern UI with Bootstrap 5 + Custom CSS

⚡ AJAX-based Actions (no full page reloads for like, dislike, comment, delete)

🛠️ Tech Stack

Frontend: HTML5, CSS3, Bootstrap 5, jQuery, AJAX

Backend: PHP 8 (Core, PDO)

Database: MySQL (via phpMyAdmin / XAMPP)

Server: Apache (XAMPP Localhost)

📂 Project Structure
Social-Network/
│
├── db/                 # SQL schema & migrations
├── public/             # Public-facing pages
│   ├── index.php       # Home / Login
│   ├── register.php    # Signup
│   ├── profile.php     # User Profile & Posts
│   ├── ajax.php        # AJAX requests handler
│   └── assets/         # CSS / JS / Images
│
├── src/                # Core PHP classes
│   ├── Post.php        # Post model (CRUD, likes, comments)
│   ├── User.php        # User model (auth, profile updates)
│   ├── UploadHandler.php
│   └── config.php      # DB connection & session
│
└── README.md           # Project documentation

⚙️ Setup Instructions
1. Install XAMPP

Download & install XAMPP
.
Start Apache and MySQL from XAMPP Control Panel.

2. Clone Repo
git clone https://github.com/GARVIT-PALIWAL/Social-Network.git
cd Social-Network

3. Setup Database

Open phpMyAdmin

Create a database:

CREATE DATABASE social_network;


Import the schema file db/schema.sql

4. Configure DB Connection

Edit src/config.php and update MySQL credentials if needed:

$host = 'localhost';
$db   = 'social_network';
$user = 'root';
$pass = '';

5. Run the Project

Move project folder into htdocs:

C:\xampp\htdocs\Social-Network


Visit in browser:

http://localhost/Social-Network/public

📸 Screenshots

(Add your own screenshots here – Profile page, Like/Dislike buttons, Comments, etc.)

🚀 Future Improvements

Add friend system (follow/unfollow)

Add real-time chat (WebSockets)

Improve AJAX live updates (no page reloads at all)

Deploy on a free host (e.g. Heroku / Render / 000webhost)
