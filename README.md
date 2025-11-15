# Secure Full-Stack Authentication System

This project is a complete, secure user login and registration system built from scratch. It features a modern, single-page-feel front-end that uses AJAX to communicate with a secure, back-end PHP engine.

The system also includes a "members-only" dashboard where logged-in users can access a tool (a GPA calculator) and save their personal data, which is then loaded back on their next visit.



---

## Core Features

This project was built with a "security-first" mindset and demonstrates several key web development principles.

### 🛡️ Back-End & Security
* **Password Hashing:** All user passwords are secured using PHP's `password_hash()` and `password_verify()` functions. Plain-text passwords are **never** stored in the database.
* **SQL Injection Prevention:** All database queries use **Prepared Statements** (`?` placeholders) to prevent SQLi attacks, ensuring user input is never executed as a command.
* **Secure Session Management:** The system uses PHP sessions to maintain a user's logged-in state. A secure "bouncer" script on the members-only page (`welcome.php`) checks for a valid session and redirects unauthorized users.
* **Full-Stack Data Persistence:** Logged-in users can `UPDATE` their personal data (GPA) to the database, which is then `SELECT`ed and "loaded" back the next time they log in.

### 🚀 Front-End & User Experience
* **AJAX-Driven (Single-Page Feel):** The login and registration forms use JavaScript's `fetch()` API to talk to the PHP server without a page reload.
* **JSON API:** The PHP back-end (`auth.php`) acts as an API, sending clean, computer-readable **JSON** "memos" (e.g., `{"status": "success", "message": "Login successful!"}`) back to the JavaScript front-end.
* **Dynamic UI:** The front-end instantly shows success or error messages on the same page by reading the JSON response.
* **Modern Design:** The UI is a clean, modern "card" design built with CSS, including dynamic elements like a "click-and-hold" to show passwords.

---

## Technologies Used

* **Front-End:** HTML5, CSS3, JavaScript (ES6+)
* **Back-End:** PHP
* **Database:** MySQL
* **Local Server:** XAMPP (Apache, MySQL)

---

## How to Run This Project Locally

This is a full-stack project and requires a PHP/MySQL server environment like XAMPP to run.

1.  **Download & Install XAMPP:** Make sure the Apache and MySQL services are running.
2.  **Import the Database:**
    * Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
    * Create a new database named `login_project`.
    * Click on that database and go to the **"SQL"** tab.
    * Paste in the following SQL command to create the `users` table:
        ```sql
        CREATE TABLE `users` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `username` varchar(50) NOT NULL,
          `password` varchar(255) NOT NULL,
          `current_cgpa` decimal(4,2) DEFAULT NULL,
          `credits_completed` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ```
    * Click "Go".
3.  **Add Project Files:**
    * Place this entire project folder inside your `htdocs` directory (e.g., `C:/xampp/htdocs/auth_v2`).
4.  **Run the Application:**
    * Open your browser and navigate to `http://localhost/auth_v2/`.
    * You can now register a new user and test the complete login/logout/save-data flow.
