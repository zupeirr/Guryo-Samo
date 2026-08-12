# Guryo Samo — Real Estate Management System

A modern and user-friendly **Real Estate Management System**  using **HTML5, CSS3, JavaScript, PHP, and MySQL** without relying on external frameworks. The system, named **“Guryo Samo”** (“Good Homes” in Somali), is designed to simplify property management, listings, and real estate-related operations through a clean and intuitive platform.


---

## ✨ Features

**Public Website**
- Home page with hero search, live stats, and featured listings
- Properties page with search/filter by location, type, status and max price
- Property details page with full specs, property image, and similar listings
- About Us page (mission, vision, values, team)
- Contact page with a working message form (saved to the database)
- **Register** page — visitors can create a customer account
- **Login** page — used by BOTH customers and admin/staff (one shared login;
  the system automatically sends admins to the dashboard and everyone else
  back to the home page)

**Admin Dashboard** (`/admin`) — only accessible to `admin` / `staff` accounts
- Secure login (bcrypt-hashed password, CSRF-protected form, session auth)
- Dashboard with statistics (total properties, for sale/rent, messages)
- Add Property (with image upload)
- View Properties (table with edit/delete actions)
- Edit Property (replace image optional)
- Delete Property (also removes the uploaded image file)
- View Customer Messages (mark as read / delete)
- **Manage Users** — view every registered account (admin, staff, customer)
  with stats, and remove customer accounts (admin/staff accounts are
  protected from deletion)
- **Reports** — analytics page with properties-by-type and by-status
  breakdowns, price statistics (min/avg/max, total sale value), top
  locations, message and user summaries, and a print button

---

## 🗂 Project Structure

```
real-estate-system/
├── admin/                     # Admin dashboard (admin/staff login required)
│   ├── includes/              # Sidebar + admin header/footer templates
│   ├── dashboard.php
│   ├── properties.php         # View Properties
│   ├── add-property.php
│   ├── edit-property.php
│   ├── delete-property.php
│   ├── messages.php
│   ├── users.php              # Manage Users (view / delete customer accounts)
│   └── reports.php            # Analytics / Reports
├── assets/
│   ├── css/style.css          # Public site styles
│   ├── css/admin.css          # Admin dashboard styles
│   ├── js/main.js             # Public site JS
│   ├── js/admin.js            # Admin JS
│   └── images/no-image.jpg    # Placeholder image
├── config/
│   └── db.php                 # MySQL connection settings
├── database/
│   └── real_estate.sql        # Database schema + sample data
├── includes/
│   ├── header.php / footer.php
│   └── functions.php          # Shared helper functions
├── uploads/                    # Uploaded property images (writable)
├── index.php                   # Home page
├── properties.php
├── property-details.php
├── about.php
├── contact.php
├── login.php
├── register.php
└── logout.php
```

---

## 🚀 Setup Instructions (XAMPP)

1. **Copy the project folder**
   Copy the entire `real-estate-system` folder into your XAMPP `htdocs`
   directory, e.g. `C:\xampp\htdocs\real-estate-system`.

2. **Start Apache and MySQL**
   Open the XAMPP Control Panel and start both **Apache** and **MySQL**.

3. **Create the database**
   - Open `http://localhost/phpmyadmin`
   - Click **Import**, choose the file `database/real_estate.sql`, and click **Go**.
   - This creates the `real_estate_db` database with the `users`, `properties`,
     and `messages` tables, plus a default admin account and sample properties.

4. **Check the database connection**
   Open `config/db.php` and confirm the credentials match your MySQL setup
   (defaults `root` with no password work with a standard XAMPP install).

5. **Set folder permissions (Linux/Mac only)**
   Make sure the `uploads/` folder is writable:
   ```
   chmod -R 755 uploads
   ```

6. **Open the site**
   Visit `http://localhost/real-estate-system/` in your browser.

7. **Log in / Register**
   - Admin login: go to `http://localhost/real-estate-system/login.php`
     - **Username:** `admin`
     - **Password:** `Admin@123`
   - New visitors can create their own account at
     `http://localhost/real-estate-system/register.php`, then log in with
     the same `login.php` page — they'll be sent to the home page instead
     of the dashboard.

   ⚠️ For a real deployment, change the admin password immediately after
   first login (update the `users` table with a new bcrypt hash via
   `password_hash()`).

### Upgrading a database you already imported

If you imported `real_estate.sql` **before** the Register feature was added,
your `users` table won't have the `customer` role yet. Fix it by running
this once in phpMyAdmin's **SQL** tab (select `real_estate_db` first):

```sql
ALTER TABLE users
  MODIFY role ENUM('admin','staff','customer') NOT NULL DEFAULT 'customer';
```

---

## 👤 Account Types

The `users` table holds three kinds of accounts, told apart by the `role` column:

| Role       | Created via                | Can log in? | Can open Admin Dashboard? |
|------------|-----------------------------|:-----------:|:--------------------------:|
| `admin`    | Pre-seeded in the SQL file  | ✅          | ✅                          |
| `staff`    | Same as admin (reserved for future use) | ✅ | ✅                    |
| `customer` | Signs up via `register.php` | ✅          | ❌ (sent to the home page) |

An admin can review every account — and remove customer accounts — from
**Admin Dashboard → Manage Users**. Admin/staff accounts are protected and
cannot be deleted from that page, to avoid accidentally locking everyone
out of the dashboard.

---

## 🔒 Security Notes

- Passwords are stored using PHP's `password_hash()` (bcrypt) and verified
  with `password_verify()`.
- All database queries use **prepared statements** to prevent SQL injection.
- All output is escaped with `htmlspecialchars()` to prevent XSS.
- Forms are protected with CSRF tokens.
- Uploaded images are validated by MIME type and size, renamed randomly,
  and the `uploads/` folder blocks PHP execution via `.htaccess`.
- Admin pages check `requireLogin()` on every request.

---

## 🛠 Technology Stack

| Layer      | Technology              |
|------------|--------------------------|
| Frontend   | HTML5, CSS3, JavaScript (vanilla) |
| Backend    | PHP (procedural, MySQLi) |
| Database   | MySQL                   |
| Server     | Apache (via XAMPP)       |

No frameworks (Laravel, React, Bootstrap, etc.) are used — everything is
Developed from scratch with a clear and organized codebase, making the system easy to understand, demonstrate, and explain during the project presentation.
