# 💪 FitZone - Gym Management System

A complete Gym Management System built with **HTML, CSS, JavaScript (Frontend)** and **Core PHP + MySQL (Backend)**.

---

## Features

### 🔐 Authentication System
- Admin / Trainer / Member login with role-based access
- Forgot Password (token-based reset)
- Change Password

### 👤 Member Management
- Add, Edit, Delete members
- Member profiles with photo upload
- Emergency contact, weight/height tracking
- Active/Inactive/Expired status

### 💳 Membership Plans
- Create custom plans (Monthly, Quarterly, Yearly)
- Auto-calculate expiry dates
- Track active subscribers per plan

### 💰 Payment Management
- Record payments (Cash, UPI, Card, Bank Transfer)
- Auto-generate invoice numbers
- Print receipts
- Date-range filtering
- Export to CSV

### 📅 Attendance System
- Member check-in / check-out
- Daily attendance log
- Duration tracking

### 🏋️ Trainer Management
- Add trainers with specialization & experience
- Assign trainers to members
- Trainer dashboard with assigned members & classes

### 📊 Admin Dashboard
- Total members, active members, trainers count
- Today's attendance
- Monthly revenue
- Revenue bar chart (Chart.js)
- Expiring memberships alert (3-day & 7-day warnings)

### 📆 Class Scheduling
- Create classes (Yoga, Zumba, CrossFit, etc.)
- Set day, time, trainer, capacity
- Members can enroll/unenroll

### 🔔 Notification System
- Auto-generate expiry alerts
- Send custom notifications to individual or all members
- Payment reminders, class schedule updates

### 🧾 Reports
- Revenue report with date range
- Members report
- Attendance report
- Export to CSV

---

## Setup Instructions

### Requirements
- **PHP 7.4+** (or PHP 8.x)
- **MySQL 5.7+** (or MariaDB)
- **Apache** with mod_rewrite (XAMPP / WAMP / LAMP)

### Installation

1. **Copy** the `gym-management-system` folder to your web server root:
   - XAMPP: `C:\xampp\htdocs\gym-management-system`
   - WAMP: `C:\wamp64\www\gym-management-system`

2. **Create the database:**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the file `database.sql`
   - This creates the `gym_management` database with all tables and sample data

3. **Configure database connection** (if needed):
   - Edit `includes/config.php`
   - Update `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`

4. **Create upload directory:**
   ```
   mkdir -p uploads/members
   chmod 755 uploads/members
   ```

5. **Access the system:**
   - Open: `http://localhost/gym-management-system`

### Default Login Credentials

| Role    | Username       | Password   |
|---------|---------------|------------|
| Admin   | admin         | admin123   |
| Trainer | trainer_rahul | admin123   |
| Trainer | trainer_priya | admin123   |

---

## Project Structure

```
gym-management-system/
├── index.php                 # Login page
├── logout.php
├── database.sql              # Full database schema + sample data
├── README.md
│
├── includes/
│   ├── config.php            # DB config + helper functions
│   ├── auth.php              # Authentication functions
│   └── sidebar.php           # Reusable sidebar navigation
│
├── assets/
│   ├── css/style.css         # Complete dark-theme stylesheet
│   └── js/main.js            # Utility JS (modals, search, export)
│
├── pages/
│   ├── change-password.php   # Shared password change
│   │
│   ├── admin/
│   │   ├── dashboard.php     # Admin dashboard with stats & charts
│   │   ├── members.php       # Member CRUD
│   │   ├── member-profile.php
│   │   ├── trainers.php      # Trainer CRUD
│   │   ├── plans.php         # Membership plans CRUD
│   │   ├── payments.php      # Payment management
│   │   ├── attendance.php    # Attendance tracking
│   │   ├── classes.php       # Class scheduling
│   │   ├── reports.php       # Revenue/Member/Attendance reports
│   │   └── notifications.php # Notification management
│   │
│   ├── trainer/
│   │   ├── dashboard.php
│   │   ├── my-members.php
│   │   └── classes.php
│   │
│   └── member/
│       ├── dashboard.php
│       ├── profile.php
│       ├── classes.php
│       └── payments.php
│
├── api/
│   └── attendance.php        # AJAX endpoint
│
├── uploads/
│   └── members/              # Member photos
│
└── exports/                  # Generated reports
```

---

## Tech Stack

- **Frontend:** HTML5, CSS3 (Custom dark theme), Vanilla JavaScript
- **Backend:** Core PHP (no framework)
- **Database:** MySQL
- **Charts:** Chart.js
- **Fonts:** Bebas Neue + Outfit (Google Fonts)

---

Built with ❤️ for your friend's gym!
