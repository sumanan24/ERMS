# Exam Result Management System

A complete PHP MVC-based Exam Result Management System with user authentication and database installation.

## Features

- ✅ MVC (Model-View-Controller) Architecture
- ✅ User Login System
- ✅ Database Installation Script
- ✅ Secure Password Hashing
- ✅ Session Management
- ✅ Modern, Responsive UI
- ✅ Role-based Access (Admin, Teacher, Student)

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- WAMP/XAMPP/LAMP (for local development)

### Setup Instructions

1. **Extract files** to your web server directory (e.g., `C:\wamp64\www\exam`)

2. **Configure Database** (if needed):
   - Edit `config/database.php` to match your MySQL credentials
   - Default settings:
     - Host: localhost
     - Username: root
     - Password: (empty)
     - Database: exam_management (will be created automatically)

3. **Run Installation**:
   - Open your browser and navigate to: `http://localhost/exam/install.php`
   - Click "Install Now" button
   - The system will create:
     - Database: `exam_management`
     - All required tables
     - Default admin account

4. **Login**:
   - Navigate to: `http://localhost/exam/`
   - Default credentials:
     - Username: `admin`
     - Password: `admin123`
   - ⚠️ **Change the default password after first login!**

## Database Structure

### Tables Created:
1. **users** - User accounts and authentication
2. **students** - Student information
3. **subjects** - Subject details
4. **exams** - Exam information
5. **exam_results** - Exam result records

## Project Structure

```
exam/
├── config/
│   ├── database.php      # Database connection
│   └── install.php       # Installation class
├── controllers/
│   └── AuthController.php # Authentication controller
├── models/
│   └── User.php          # User model
├── views/
│   ├── login.php         # Login form
│   └── dashboard.php     # Dashboard view
├── index.php             # Main entry point & router
├── install.php           # Installation page
└── README.md             # This file
```

## MVC Architecture

- **Models** (`models/`): Data access layer (User.php)
- **Views** (`views/`): Presentation layer (login.php, dashboard.php)
- **Controllers** (`controllers/`): Business logic layer (AuthController.php)

## Security Features

- Password hashing using PHP `password_hash()`
- Prepared statements to prevent SQL injection
- Session-based authentication
- Input validation and sanitization

## Default Admin Account

After installation, you can login with:
- **Username:** admin
- **Password:** admin123

**Important:** Change this password immediately after first login!

## Development

### Adding New Features

1. **New Model**: Create in `models/` directory
2. **New Controller**: Create in `controllers/` directory
3. **New View**: Create in `views/` directory
4. **Add Route**: Update `index.php` switch statement

## License

This project is open source and available for educational purposes.

## Support

For issues or questions, please check the code comments or database configuration.

