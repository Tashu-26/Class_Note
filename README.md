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

 3. Step-by-Step Setup Instructions

  
  Step 1: Place the Project
Open your XAMPP folder (usually C:\xampp).
Go to the htdocs folder.
Create a folder named classnote and paste all project files inside it.
 - Path should be: C:\xampp\htdocs\classnote\index.php

  Step 2: Database Setup
Start Apache and MySQL in the XAMPP Control Panel.
pen your browser and go to http://localhost/phpmyadmin.
Create a new database named noteorg.
Click on the noteorg database, go to the Import tab.
Select the database.sql file from your project folder and click Go.



Step 3: Configuration Check
 Open includes/config.php in a text editor.
 Ensure the database credentials match your XAMPP settings (default is root
      with no password):

define('DB_NAME', 'noteorg');
define('DB_USER', 'root');
define('DB_PASS', '');

  Step 4: Permissions
Ensure the uploads folder in your project is writable so you can save
             images.

  Step 5: Run the App
Open your browser and go to:
http://localhost/classnote/
You can now Sign Up and start creating notes!


