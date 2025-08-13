# TechVision Pro - IT Company Blog Website

A complete IT company website with blog functionality and admin panel, built with PHP, MySQL, and modern CSS.

## Features

### Frontend
- **Homepage**: Modern design with hero section, services, about, and latest blog posts
- **Blog Page**: List all blog posts with search and category filtering
- **Blog Detail Page**: Individual blog post view with related articles
- **Responsive Design**: Mobile-first approach with Bootstrap 5

### Admin Panel
- **Secure Login**: Username/password authentication
- **Dashboard**: Overview statistics and recent posts
- **Add New Post**: Create blog posts with title, description, content, category, and image upload
- **Manage Posts**: Edit, delete, and change post status
- **Image Upload**: Support for JPEG, PNG, GIF, and WebP formats

## Requirements

- XAMPP (Apache + MySQL + PHP)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

## Installation

### 1. Setup XAMPP
1. Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Start Apache and MySQL services
3. Place the project files in `C:\xampp\htdocs\Test\`

### 2. Create Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Import the `database.sql` file or run the SQL commands manually
3. The database `it_company_blog` will be created with:
   - Admin users table
   - Blog categories table
   - Blog posts table
   - Default admin user and categories

### 3. Default Admin Credentials
- **Username**: `admin`
- **Password**: `admin123`

## File Structure

```
Test/
├── assets/
│   └── css/
│       └── style.css          # Main stylesheet
├── config/
│   └── database.php           # Database configuration
├── admin/
│   ├── login.php              # Admin login
│   ├── dashboard.php          # Admin dashboard
│   ├── add-post.php          # Add new blog post
│   ├── manage-posts.php      # Manage all posts
│   ├── edit-post.php         # Edit existing post
│   └── logout.php            # Admin logout
├── uploads/                   # Blog post images
├── index.php                  # Homepage
├── blog.php                   # Blog listing page
├── blog-detail.php            # Individual blog post
├── database.sql               # Database structure
└── README.md                  # This file
```

## Usage

### Frontend
1. **Homepage**: `http://localhost/Test/`
2. **Blog**: `http://localhost/Test/blog.php`
3. **Individual Post**: `http://localhost/Test/blog-detail.php?slug=post-slug`

### Admin Panel
1. **Admin Login**: `http://localhost/Test/admin/login.php`
2. **Dashboard**: Accessible after login
3. **Add Post**: Create new blog posts
4. **Manage Posts**: Edit, delete, and manage existing posts

## Creating Blog Posts

1. Login to admin panel
2. Click "Add New Post"
3. Fill in:
   - **Title**: Blog post title
   - **Description**: Short summary (appears in previews)
   - **Content**: Full blog post content
   - **Category**: Select from predefined categories
   - **Image**: Upload featured image
   - **Status**: Draft or Published
4. Click "Create Post"

## Customization

### Adding New Categories
1. Access the database directly via phpMyAdmin
2. Insert new records into `blog_categories` table
3. Or modify the `database.sql` file and re-import

### Styling
- Edit `assets/css/style.css` to customize colors, fonts, and layout
- The CSS uses CSS variables and modern features for easy customization

### Content
- Update company information in the PHP files
- Modify services, about section, and contact details
- Change images and branding elements

## Security Features

- **Password Hashing**: Admin passwords are securely hashed using PHP's `password_hash()`
- **SQL Injection Protection**: All database queries use prepared statements
- **XSS Protection**: Output is properly escaped using `htmlspecialchars()`
- **Session Management**: Secure admin sessions with proper validation

## Troubleshooting

### Database Connection Issues
- Ensure MySQL service is running in XAMPP
- Check database credentials in `config/database.php`
- Verify database `it_company_blog` exists

### Image Upload Issues
- Ensure `uploads/` directory has write permissions
- Check file size limits in PHP configuration
- Verify supported image formats (JPEG, PNG, GIF, WebP)

### Admin Login Issues
- Verify database tables are created correctly
- Check if admin user exists in `admin_users` table
- Default credentials: admin/admin123

### Page Not Loading
- Ensure Apache service is running
- Check file permissions
- Verify PHP syntax with `php -l filename.php`

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Performance Tips

- Optimize uploaded images before uploading
- Use appropriate image formats (WebP for photos, PNG for graphics)
- Consider implementing image compression
- Enable browser caching for static assets

## Future Enhancements

- User registration and comments system
- Advanced search with filters
- Email newsletter integration
- Social media sharing analytics
- SEO optimization tools
- Multi-language support
- Advanced admin features (user management, analytics)

## Support

For technical support or questions:
1. Check the troubleshooting section above
2. Verify XAMPP services are running
3. Check browser console for JavaScript errors
4. Review Apache error logs in XAMPP

## License

This project is created for educational and commercial use. Feel free to modify and use for your business needs.

---

**TechVision Pro** - Transforming businesses through technology since 2024 
