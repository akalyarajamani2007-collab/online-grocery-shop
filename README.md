# Smart Online Grocery Shop Management System

## Project Structure

- `/admin` - admin panel pages
- `/user` - user panel pages
- `/assets/css` - custom styles
- `/assets/js` - client-side JavaScript
- `/assets/images` - product image uploads
- `/config` - database connection file
- `/database` - SQL schema file
- `/includes` - shared PHP functions and helpers
- `/templates` - reusable header/footer templates

## Database Setup

1. Open MySQL Workbench or phpMyAdmin.
2. Run the SQL file: `/database/schema.sql`.
3. Run `/database/seed.sql` to add sample admin, user, categories, and items.
4. The schema creates `grocery_db` and all required tables.

The password above is `Admin@123` hashed with `password_hash`.

## Configuration

1. Open `/config/db.php`.
2. Update `$host`, `$user`, `$pass` and `$db` for your environment.
3. Save the file.

## Running Locally

1. Place this folder in your local web server root (`c:\xampp\htdocs\grocery`).
2. Start Apache and MySQL in XAMPP.
3. Open browser to `http://localhost/grocery/index.php`.
4. Use `/admin/login.php` for admin access.
5. Add products in `/admin/items.php` after login.

## Features Included

- User registration and login
- Admin login and dashboard
- Product browsing with filters
- Category management
- Order reports for users and admins
- Secure password hashing and prepared statements
- Bootstrap 5 responsive UI

## Next Steps

- Add cart persistence and checkout pages
- Implement product CRUD pages in admin
- Create delivery and payment management interfaces
- Add AJAX cart updates and toast notifications
- Build invoice export and report generation
