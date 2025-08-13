# 🚀 Hostinger Deployment Guide

## 📋 Prerequisites
- Hostinger hosting account with PHP support
- Domain: test.smrkonova.com
- Access to Hostinger control panel

## 🔧 Step 1: Database Setup

### 1.1 Create Database in Hostinger
1. Login to Hostinger control panel
2. Go to **Databases** → **MySQL Databases**
3. Create a new database:
   - Database name: `your_database_name` (e.g., `smrkonov_test`)
   - Username: `your_database_username` (e.g., `smrkonov_admin`)
   - Password: `your_database_password` (create a strong password)
4. Note down these credentials

### 1.2 Import Database Structure
1. Go to **Databases** → **phpMyAdmin**
2. Select your newly created database
3. Click **Import** tab
4. Choose the `database-production.sql` file
5. Click **Go** to import

## 📁 Step 2: File Upload

### 2.1 Upload Files via File Manager
1. Go to **Files** → **File Manager**
2. Navigate to `public_html` folder
3. Upload all files from the deployment package:
   - `index.php`
   - `blog.php`
   - `blog-detail.php`
   - `admin/` folder
   - `config/` folder
   - `assets/` folder
   - `uploads/` folder

### 2.2 Set File Permissions
Set these permissions:
- Folders: `755`
- Files: `644`
- `uploads/` folder: `755`

## ⚙️ Step 3: Configuration

### 3.1 Update Database Configuration
1. Edit `config/database.php`
2. Replace placeholder values with your actual database credentials:
   ```php
   private $db_name = 'your_actual_database_name';
   private $username = 'your_actual_database_username';
   private $password = 'your_actual_database_password';
   ```

### 3.2 Test Configuration
1. Visit `https://test.smrkonova.com`
2. Check if the homepage loads correctly
3. Test admin panel: `https://test.smrkonova.com/admin/`

## 🔐 Step 4: Admin Access

### 4.1 Default Login Credentials
- **Username**: `admin`
- **Password**: `admin123`

### 4.2 Change Default Password
1. Login to admin panel
2. Go to profile settings (if available)
3. Change the default password for security

## 🌐 Step 5: Domain Configuration

### 5.1 DNS Settings
Ensure your domain points to Hostinger:
- A record: Point to Hostinger's IP
- CNAME: Point to your hosting account

### 5.2 SSL Certificate
1. Go to **SSL** in Hostinger control panel
2. Enable SSL for your domain
3. Your site will be accessible via `https://test.smrkonova.com`

## ✅ Step 6: Testing

### 6.1 Frontend Testing
- [ ] Homepage loads correctly
- [ ] Blog page displays posts
- [ ] Blog detail pages work
- [ ] Images display properly

### 6.2 Admin Panel Testing
- [ ] Admin login works
- [ ] Dashboard displays correctly
- [ ] Can add new blog posts
- [ ] Can manage existing posts
- [ ] Categories management works

## 🚨 Troubleshooting

### Common Issues:

#### 1. Database Connection Error
- Check database credentials in `config/database.php`
- Verify database exists and is accessible
- Check if MySQL service is running

#### 2. 500 Internal Server Error
- Check file permissions
- Review error logs in Hostinger control panel
- Verify PHP version compatibility (PHP 7.4+)

#### 3. Images Not Displaying
- Check `uploads/` folder permissions
- Verify image paths in database
- Ensure images were uploaded correctly

#### 4. Admin Panel Not Working
- Verify session configuration
- Check if all admin files were uploaded
- Review PHP error logs

## 📞 Support

If you encounter issues:
1. Check Hostinger error logs
2. Verify file permissions
3. Test database connection
4. Contact Hostinger support if needed

## 🔒 Security Notes

- Change default admin password immediately
- Keep database credentials secure
- Regularly update your hosting environment
- Monitor for unauthorized access

---

**Deployment completed!** 🎉

Your website should now be live at: `https://test.smrkonova.com`
