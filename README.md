# LNHS Documents Request Portal

A comprehensive web-based document request system for Laguna National High School (LNHS) that allows students and alumni to request official documents online without having to visit the school physically.

## 🚀 Features

### User Management
- **Student/Alumni Registration & Login**: Secure authentication system
- **Admin Dashboard**: Comprehensive admin panel for system management
- **Role-based Access Control**: Different permissions for students, alumni, and administrators

### Document Request System
- **Online Request Form**: Easy-to-use form for document requests
- **File Upload Support**: Upload valid ID and additional requirements
- **Multiple Document Types**:
  - Certificate of Enrollment
  - Good Moral Certificate
  - Transcript of Records
  - Diploma Copy
  - Certificate of Graduation

### Request Tracking
- **Real-time Status Updates**: Track request progress through multiple stages
- **Status Flow**: Pending → Processing → Approved/Denied → Ready for Pickup → Completed
- **Request History**: Complete audit trail of status changes
- **Email Notifications**: Automated updates on request status changes

### Admin Features
- **Request Management**: View, process, and update request statuses
- **User Management**: Manage student and alumni accounts
- **Document Type Management**: Configure available documents and fees
- **Reports & Analytics**: Generate comprehensive reports
- **System Settings**: Configure system parameters

### Modern UI/UX
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Bootstrap 5**: Modern and professional interface
- **Font Awesome Icons**: Intuitive iconography
- **Dashboard Analytics**: Visual statistics and progress tracking

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript, jQuery
- **Framework**: Bootstrap 5
- **Icons**: Font Awesome 6
- **Architecture**: MVC Pattern with PDO for database operations

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- 5MB+ file upload support
- Email server (for notifications)

## 🔧 Installation

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/lnhs-documents-portal.git
cd lnhs-documents-portal
```

### 2. Database Setup
1. Create a MySQL database named `lnhs_portal`
2. Import the database schema:
```bash
mysql -u username -p lnhs_portal < database/schema.sql
```

### 3. Configuration
1. Update database credentials in `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'lnhs_portal');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

2. Configure email settings for notifications:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

### 4. File Permissions
```bash
chmod 755 uploads/
chmod 755 uploads/id/
chmod 755 uploads/requirements/
```

### 5. Web Server Configuration
Ensure your web server points to the project root directory and has PHP enabled.

## 🚦 Usage

### Default Admin Account
- **Email**: admin@lnhs.edu.ph
- **Password**: password

### For Students/Alumni
1. Visit the portal homepage
2. Click "Create an Account" to register
3. Fill in required information
4. Login with your credentials
5. Navigate to "Request Document"
6. Fill out the request form and upload required files
7. Track your request status in "My Requests"

### For Administrators
1. Login with admin credentials
2. Access the admin dashboard
3. Manage incoming requests in "All Requests"
4. Update request statuses as documents are processed
5. Generate reports and analytics
6. Manage users and system settings

## 📊 Request Status Flow

```
Pending → Processing → Approved/Denied → Ready for Pickup → Completed
```

- **Pending**: Request submitted, awaiting admin review
- **Processing**: Admin is working on the request
- **Approved**: Request approved, document being prepared
- **Denied**: Request denied (with reason)
- **Ready for Pickup**: Document ready for collection
- **Completed**: Document successfully claimed

## 🔐 Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention with PDO prepared statements
- File upload validation and sanitization
- Session management with secure settings
- Admin-only access controls
- Input validation and sanitization

## 📁 Project Structure

```
lnhs-documents-portal/
├── admin/                  # Admin panel files
│   ├── dashboard.php
│   ├── requests.php
│   └── ...
├── assets/                 # Static assets
│   └── css/
│       └── style.css
├── classes/                # PHP classes
│   ├── User.php
│   └── DocumentRequest.php
├── config/                 # Configuration files
│   └── database.php
├── database/               # Database files
│   └── schema.sql
├── uploads/                # File uploads directory
│   ├── id/
│   └── requirements/
├── index.php              # Login page
├── register.php           # Registration page
├── dashboard.php          # Student/Alumni dashboard
├── request-document.php   # Document request form
└── README.md
```

## 🔧 Customization

### Adding New Document Types
1. Access admin panel
2. Go to "Document Types"
3. Add new document with:
   - Document name
   - Description
   - Processing fee
   - Processing days
   - Requirements

### Modifying Email Templates
Edit the notification templates in the `classes/Notification.php` file.

### Styling Changes
Modify `assets/css/style.css` for custom styling.

## 🐛 Troubleshooting

### File Upload Issues
- Check PHP `upload_max_filesize` and `post_max_size` settings
- Ensure upload directories have write permissions
- Verify file type restrictions in `DocumentRequest.php`

### Database Connection Issues
- Verify database credentials in `config/database.php`
- Ensure MySQL service is running
- Check database user permissions

### Email Notification Issues
- Verify SMTP settings in `config/database.php`
- Check if PHP mail extension is enabled
- Ensure firewall allows SMTP connections

## 📈 Future Enhancements

- [ ] SMS notifications via API integration
- [ ] Digital document delivery (PDF generation)
- [ ] Payment gateway integration
- [ ] Mobile app development
- [ ] Advanced reporting with charts
- [ ] Document template management
- [ ] Bulk request processing
- [ ] API for third-party integrations

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

For support and questions:
- **Email**: support@lnhs.edu.ph
- **Phone**: (123) 456-7890
- **Office Hours**: Monday-Friday, 8:00 AM - 5:00 PM

## 👥 Credits

Developed for Laguna National High School (LNHS) to modernize the document request process and improve student services.

---

**LNHS Documents Request Portal** - Streamlining document requests for better student experience.