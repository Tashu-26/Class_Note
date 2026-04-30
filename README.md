# Class_Note
NoteOrg is a smart note management system designed for students.
It helps users organize notes, subjects, and study materials in one place.

With support for tags, file attachments (images/PDFs), favorites, and search, NoteOrg makes studying more efficient and structured.

Key Features 

User Management
User can register and log in

Password is kept secure

User can manage profile


Subject Management
 
Create, edit, and delete subjects
Keep notes under each subject
Notes Management
Add, edit, delete, and view notes
Upload files (PDF, images)
Show notes in card style
Advanced Features
Mark notes as favorite or pin
Search notes easily (live search)
Use tags to organize better
Highlight important notes
 User Interface Features
Works on mobile and desktop
Dark mode, pastel purple, and light mode
Clean and modern design
User Experience
Show success and error messages
Ask before deleting anything 



Security Features
Password is saved in a secure way (hashed)
System keeps user login safe (session management)
Users must log in to see important pages



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


