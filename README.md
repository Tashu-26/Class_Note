# Class_Note
NoteOrg is a smart note management system designed for students.
It helps users organize notes, subjects, and study materials in one place.

With support for tags, file attachments (images/PDFs), favorites, and search, NoteOrg makes studying more efficient and structured.

📌 Key Features


🔹 User Management

Users can register and log in to the system securely.

Passwords are stored in a hashed format to protect user data.

Each user can manage their own profile and access only their data.


🔹 Subject Management

Users can create, edit, and delete subjects.

Notes can be organized under specific subjects for better structure.


🔹 Notes Management

Users can add, edit, delete, and view notes easily.

File upload feature allows attaching PDFs, images, and documents.

Notes are displayed in a clean card-style layout for better readability.


🔹 Advanced Features

Notes can be marked as favorite or pinned for quick access ⭐

Live search helps users find notes instantly 🔍

Tag system allows better organization of notes

Important notes can be highlighted for visibility


🔹 User Interface Features

Fully responsive design (works on mobile and desktop) 📱💻

Supports Dark Mode, Light Mode, and pastel purple theme 🎨

Clean and modern UI for better usability


🔹 User Experience

Displays success and error messages clearly

Confirmation is required before deleting notes to prevent mistakes


🔹 Security Features

Passwords are stored securely using hashing 🔐

Session management keeps users logged in safely

Unauthorized users cannot access protected pages


🛠️ Tech Stack

🔹 Backend

PHP 8.x (Procedural):

Used to handle server-side logic such as authentication, note management, and data processing.

PDO (PHP Data Objects):

Used for secure database operations with prepared statements, helping prevent SQL injection.


🔹 Frontend

HTML5:

Provides the structure of the web pages.

CSS3 (Vanilla CSS with Custom Variables):

Used for styling the application with a clean and modern design. Custom variables help maintain consistent colors and themes.

JavaScript (ES6+):

Adds interactivity such as:

Dark mode toggle

Live search

Favorite toggle (without page reload)

Drag-and-drop file upload


🔹 Database

MySQL 8.0+:

Used to store all application data including users, notes, subjects, tags, and attachments.






📌 Step-by-Step Setup Instructions


🔹 Step 1: Place the Project

Open your XAMPP installation folder (usually C:\xampp).

Go to the htdocs directory.

Create a new folder named classnote.

Paste all your project files inside this folder.


✅ Final path should look like:

C:\xampp\htdocs\classnote\index.php


🔹 Step 2: Database Setup

Start Apache and MySQL from the XAMPP Control Panel.

Open your browser and go to:

http://localhost/phpmyadmin

Create a new database named noteorg.

Select the noteorg database.

Go to the Import tab.

Choose the database.sql file from your project folder.

Click Go to import the database.


🔹 Step 3: Configuration Check

Open the file:

includes/config.php

Make sure your database settings match the default XAMPP configuration:

define('DB_NAME', 'noteorg');

define('DB_USER', 'root');

define('DB_PASS', '');


🔹 Step 4: Folder Permissions

Ensure the uploads folder is writable.

This is required to store uploaded files like images and documents.


🔹 Step 5: Run the Application

Open your browser.

Go to:

http://localhost/classnote/

Register a new account and start using the system.
