# Hire Me - Admin Dashboard

This is the web-based admin dashboard for the "Hire Me" mobile application. It provides administrative functionalities to manage workers, jobs, and other core aspects of the application.

## Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Project Structure](#-project-structure)
- [Installation](#-installation)
- [Usage](#-usage)
- [API Endpoints](#-api-endpoints)
- [Contributing](#-contributing)
- [License](#-license)

## ✅ Features

The admin dashboard currently supports the following features:

-   **Worker Management:**
    -   Add a new worker with detailed information, including profile picture upload.
    -   View all workers in a searchable and sortable table.
    -   Edit existing worker information through a modal form.
    -   Delete workers with a confirmation prompt.
-   **Live Data Interaction:**
    -   DataTables integration for robust table features.
    -   Asynchronous updates (add, edit, delete) without page reloads.
-   **Error Handling:**
    -   User-friendly error messages for failed operations.

## 🛠 Tech Stack

-   **Backend:** PHP
-   **Frontend:** HTML, CSS, JavaScript, jQuery
-   **Database:** MySQL (or any other SQL database compatible with PHP)
-   **Libraries:**
    -   [DataTables](https://datatables.net/) for advanced table interactions.

## 📂 Project Structure

The project follows a Model-View-Controller (MVC) like pattern to separate concerns.

```
.
├── api/                # API endpoints
│   ├── jobs/
│   ├── messages/
│   ├── ratings/
│   └── users/
├── app/                # Core application logic
│   ├── controllers/    # Handles user input and business logic
│   │   ├── admin/
│   │   └── ratings/
│   ├── models/         # Database interaction (e.g., db_config.php)
│   └── views/          # Presentation layer (HTML templates - though HTML files are in public)
│       ├── admin/
│       ├── auth/
│       ├── jobs/
│       ├── profile/
│       └── shared/
├── config/             # Configuration files
├── public/             # Web server root
│   ├── css/
│   ├── images/
│   ├── js/
│   └── uploads/
├── storage/            # Non-public files
│   ├── cache/
│   ├── logs/
│   └── reports/
└── file_manager.sh     # Script to organize project structure
```

## 🚀 Installation

To set up the project locally, follow these steps:

1.  **Clone the repository:**
    ```bash
    git clone <your-repository-url>
    cd Hire_Us_Admin_WebSite
    ```

2.  **Set up a local server:**
    -   You need a local web server environment like XAMPP, WAMP, MAMP, or a standalone Apache/Nginx + PHP server.
    -   Configure the server's document root to point to the `public/` directory of this project.

3.  **Database Setup:**
    -   Create a new database in your database management system (e.g., phpMyAdmin).
    -   Import the database schema. *(Note: A `.sql` dump file should be created and included in the project for easy setup.)*
    -   Configure your database credentials by editing `app/models/db_config.php`.

## 💻 Usage

-   Access the admin login page by navigating to `http://localhost/admin_login.html` (or your configured local domain).
-   After logging in, you will be redirected to the main dashboard (`Dashboard.html`) where you can manage the application's data.

## 🤝 Contributing

Contributions are welcome! If you'd like to contribute, please follow these steps:

1.  Fork the repository.
2.  Create a new branch (`git checkout -b feature/your-feature-name`).
3.  Make your changes.
4.  Commit your changes (`git commit -m 'Add some feature'`).
5.  Push to the branch (`git push origin feature/your-feature-name`).
6.  Open a Pull Request.

## 📄 License

This project is not currently licensed. It is recommended to add a license file (e.g., MIT, GPL) to define how others can use the code.