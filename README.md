# LNHS Documents Request Portal

A comprehensive web-based document request management system for LNHS (Local National High School) that allows students and alumni to request documents online without physically visiting the school.

## 🎯 System Overview

**Title:** LNHS Documents Request Portal

**Purpose:** Provides online access for students and alumni to request various school documents and certificates, eliminating the need for physical visits to the school.

## ✨ Features

### 🔐 Authentication System
- **Multi-role Login System**
  - Student login
  - Alumni login  
  - Admin login
- **Secure Registration** for students and alumni
- **Password protection** with hashing
- **Session management**

### 📄 Document Request System
- **Online Document Request Form** with:
  - Document type selection (Certificate of Enrollment, Good Moral Certificate, etc.)
  - Purpose specification
  - Preferred release date
  - File upload for requirements (ID, supporting documents)
  - Real-time form validation

### 📊 Request Tracking System
- **Status Tracking:**
  - ✅ Pending → Processing → Approved/Denied → Ready for Pickup
- **Visual status indicators** with color-coded badges
- **Request history** and timeline
- **Email notifications** for status updates

### 👨‍💼 Admin Dashboard
- **Comprehensive Management Interface:**
  - View and manage all requests
  - Update request statuses
  - Add admin notes and comments
  - Generate reports and logs
  - Export data (Excel/CSV format)
- **Statistics Dashboard:**
  - Pending requests count
  - Processing requests
  - Ready for pickup
  - Total requests overview

### 🔔 Notification System
- **Multi-channel Notifications:**
  - Portal alerts
  - Email notifications (configurable)
  - Real-time status updates
- **Notification Types:**
  - Request received
  - Additional documents required
  - Request approved/denied
  - Ready for pickup

## 👥 User Types

### 1. **Students**
- Current enrolled students
- Can request documents online
- Track request status
- Upload supporting documents

### 2. **Alumni** 
- Graduated students
- Can request historical documents
- Provide graduation year
- Access to alumni-specific documents

### 3. **Administrators**
- School staff managing the system
- Process and approve requests
- Generate reports
- Manage users and document types
- Update system settings

## 🛠 Technology Stack

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **Framework:** Bootstrap 5.3
- **Icons:** Font Awesome 6.0
- **Security:** PDO with prepared statements

## 📋 Document Types Available

1. **Certificate of Enrollment** - ₱50.00 (2 days processing)
2. **Good Moral Certificate** - ₱75.00 (3 days processing)
3. **Transcript of Records** - ₱150.00 (5 days processing)
4. **Diploma** - ₱200.00 (7 days processing)
5. **Certificate of Completion** - ₱50.00 (2 days processing)

## 🚀 Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (optional)

### Step 1: Database Setup
1. Create a MySQL database named `lnhs_portal`
2. Update database credentials in `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'lnhs_portal');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

### Step 2: File Uploads
1. Create upload directories:
   ```bash
   mkdir -p uploads/requirements
   chmod 755 uploads/requirements
   ```

### Step 3: Web Server Configuration
1. Point your web server to the project directory
2. Ensure PHP has write permissions to upload directories
3. Configure PHP settings:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   max_execution_time = 300
   ```

### Step 4: Initial Setup
1. Access the system via web browser
2. The database tables will be created automatically
3. Default admin account will be created:
   - **Email:** admin@lnhs.edu.ph
   - **Password:** admin123
   - **Role:** Admin

### Step 5: Email Configuration (Optional)
To enable email notifications, update the `sendEmail()` function in `includes/functions.php` with your SMTP settings or use a service like PHPMailer.

## 🔧 Configuration

### Default Admin Account
- **Email:** admin@lnhs.edu.ph
- **Password:** admin123

**⚠️ Important:** Change the default admin password after first login!

### Customization
- **School Information:** Update school details in the interface
- **Document Types:** Modify available documents in the admin panel
- **Fees:** Adjust document fees in the database
- **Processing Times:** Update processing days for each document type

## 📁 Project Structure

```
lnhs-portal/
├── index.php                 # Main login page
├── register.php              # User registration
├── dashboard.php             # Student/Alumni dashboard
├── request_document.php      # Document request form
├── process_request.php       # Request processing
├── my_requests.php           # User's request history
├── view_request.php          # Request details view
├── profile.php               # User profile management
├── config/
│   └── database.php          # Database configuration
├── includes/
│   └── functions.php         # Utility functions
├── auth/
│   ├── login.php             # Login handler
│   ├── register.php          # Registration handler
│   └── logout.php            # Logout handler
├── admin/
│   ├── dashboard.php         # Admin dashboard
│   ├── requests.php          # All requests management
│   ├── pending_requests.php  # Pending requests
│   ├── users.php             # User management
│   ├── documents.php         # Document types management
│   ├── reports.php           # Reports generation
│   └── settings.php          # System settings
└── uploads/
    └── requirements/         # Uploaded files
```

## 🔒 Security Features

- **SQL Injection Protection:** PDO prepared statements
- **XSS Protection:** Input sanitization
- **CSRF Protection:** Session-based tokens
- **Password Hashing:** bcrypt encryption
- **File Upload Security:** Type and size validation
- **Session Security:** Secure session management

## 📊 Database Schema

### Tables:
1. **users** - User accounts and profiles
2. **document_types** - Available document types and fees
3. **document_requests** - Document request records
4. **notifications** - System notifications

## 🎨 UI/UX Features

- **Responsive Design:** Works on desktop, tablet, and mobile
- **Modern Interface:** Clean, professional design
- **User-Friendly:** Intuitive navigation and forms
- **Accessibility:** WCAG compliant design
- **Loading States:** Visual feedback for actions
- **Error Handling:** Clear error messages

## 📈 Reporting & Analytics

- **Request Statistics:** By status, date, document type
- **User Analytics:** Registration trends, activity logs
- **Export Capabilities:** CSV/Excel export
- **Admin Reports:** Comprehensive system overview

## 🔄 Workflow

1. **Student/Alumni Registration** → Account creation
2. **Document Request** → Form submission with requirements
3. **Admin Review** → Process and approve/deny requests
4. **Status Updates** → Notify users of changes
5. **Document Pickup** → Mark as ready for collection

## 🛡️ Maintenance

### Regular Tasks:
- Monitor system logs
- Backup database regularly
- Update user passwords
- Review and clean old requests
- Monitor disk space for uploads

### Updates:
- Keep PHP and MySQL updated
- Regular security patches
- Feature updates and improvements

## 📞 Support

For technical support or questions:
- Check the system logs for errors
- Review database connectivity
- Verify file permissions
- Contact system administrator

## 📄 License

This project is developed for educational purposes and internal use by LNHS.

---

**Developed with ❤️ for LNHS Documents Request Portal**