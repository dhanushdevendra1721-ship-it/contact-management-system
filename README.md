# 📇 Contact Management System

A simple, lightweight Contact Management System built with PHP and SQLite. This project demonstrates core CRUD operations, input validation, search functionality, and persistent data storage.

## Overview

The application lets users manage contact records through a browser-based interface. It is designed as a learning project and can be run locally using PHP's built-in development server.

## Features

- **Add contacts** with a name, phone number, and optional email address
- **View and search contacts** by relevant contact details
- **Edit existing contacts**
- **Delete contacts**
- **Validate phone numbers** to require exactly 10 digits
- **Validate email addresses** when an email is provided
- **Prevent duplicate phone numbers**
- **Store contact data** using SQLite

## Technology Stack

- **Backend:** PHP
- **Database:** SQLite
- **Frontend:** HTML and CSS
- **Development environment:** PHP built-in web server; Termux can also be used

## Getting Started

### Prerequisites

- PHP with SQLite support enabled
- A web browser
- Git (optional, for cloning the repository)

### 1. Get the project

Clone the repository:

```bash
git clone https://github.com/dhanushdevendra1721-ship-it/contact-management-system.git
cd contact-management-system
```

Alternatively, download the repository as a ZIP from GitHub and extract it.

### 2. Start the application

From the project directory, run:

```bash
php -S 127.0.0.1:8080
```

Then open this address in your browser:

http://127.0.0.1:8080

#### Termux note

If your Termux setup requires a custom PHP configuration file, use:

```bash
php -c ~/php-termux.ini -S 127.0.0.1:8080
```

Keep the server running while using the application.

## How to Use

1. Open the application in your browser.
2. Add a contact using the form.
3. Search for contacts using the available search field.
4. Use the edit action to update a record.
5. Use the delete action to remove a record.

## Validation and Data Integrity

- Phone numbers must contain exactly 10 digits.
- Email is optional, but must be valid when supplied.
- Duplicate phone numbers are rejected.
- Contact records are stored in the application's SQLite database.

## Project Structure

The exact files may vary by version. The main application and documentation are:

```text
contact-management-system/
├── index.php       # Main application
├── contacts.db     # SQLite database (may be created locally)
└── README.md       # Project documentation
```

## Future Enhancements

Possible improvements include user authentication, pagination, CSV export/import, automated tests, and more advanced filtering.

## Author

**Dhanush Devendra**

GitHub: https://github.com/dhanushdevendra1721-ship-it

## License

No license has been specified yet. Add a license file if you intend to distribute or reuse the project publicly.
