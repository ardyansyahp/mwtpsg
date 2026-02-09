# 🚀 Management MWT - Executive Dashboard & Analytics

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

## 📌 Project Overview
**Management MWT** is an executive dashboard designed exclusively for management-level users (role = 2). It provides high-level analytics, vendor performance metrics, stock status overview, and delivery performance tracking across the entire MWT PSG ecosystem.

---

## ✨ Key Features
- 📊 **Executive Overview**: High-level KPIs and performance metrics
- 🏢 **Vendor Dashboard**: Supplier performance tracking and analytics
- 📦 **Stock Status**: Real-time inventory overview across all warehouses
- 🚚 **Delivery Dashboard**: Logistics performance and delivery metrics
- 🔒 **Management-Only Access**: Restricted to users with management role (role = 2)
- 👤 **Profile Management**: All management users can manage their username and password
- 📈 **Performance Trends**: 6-month historical data visualization

---

## 🆕 Latest Updates (v1.3.0 - Feb 9, 2026)

### Profile Settings for Management Users
- ✅ Profile Settings available for all management users by default
- ✅ Change username and password without superadmin permission
- ✅ Auto-logout after password change for security
- ✅ Modern card-based UI with gradient headers

### Access Control Enhancement
- ✅ Added role validation in CheckAuth middleware
- ✅ Only users with role = 2 (Management) can access this application
- ✅ Non-management users are redirected with error message
- ✅ Improved security and access control

### UI/UX Improvements
- ✅ Profile Settings menu in sidebar
- ✅ Divider for visual separation
- ✅ Error message display for access denied
- ✅ Consistent navigation across management portal

### Routes & Controllers
- ✅ ProfileController with username and password update methods
- ✅ Routes: `profile.edit`, `profile.update.username`, `profile.update.password`
- ✅ Validation for unique username and password confirmation

---

## 🛠 Technology Stack
- **Framework**: [Laravel 10+](https://laravel.com)
- **Database**: MySQL / MariaDB (Shared across all PSG apps)
- **Frontend**: Blade Templating, Tailwind CSS
- **Tools**: PHP 8.1+, Composer, NPM

---

## 📈 Version Information
- **Current Version**: `v1.3.0-stable`
- **Build Status**: 🟢 Stable
- **Last Updated**: 2026-02-09 07:50 (UTC+7)

---

## 🔗 Related Applications
This application is part of the **MWT PSG Ecosystem**:
1. **masterpsg** - Central master data management
2. **supplierpsg** - Supplier and raw material management
3. **shippingpsg** - Shipping, delivery, and logistics
4. **managementmwt** (this app) - Executive dashboard for management

All applications share the same database and session for seamless integration.

---

## 🔐 Access Requirements
**IMPORTANT**: This application is **ONLY** accessible to users with:
- **Role**: Management (role = 2)
- **Session**: Valid login session from masterpsg portal

Users with other roles will be automatically redirected to masterpsg with an access denied message.

---

## 🚀 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/ardyansyahp/mwtpsg.git
   cd mwtpsg/managementmwt
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Setup**
   ```bash
   # Note: Use the shared database with masterpsg
   # Only run migrations, NOT migrate:fresh
   php artisan migrate
   ```

5. **Run Locally**
   ```bash
   php artisan serve --port=8003
   ```

---

## 📝 Changelog

### v1.3.0 (2026-02-09)
- Added Profile Settings feature for management users
- Enhanced CheckAuth middleware with role validation
- Improved access control and security
- Added ProfileController and related routes

### v1.2.0 (2026-02-08)
- Fixed role-based authentication
- Updated LoginController to use role column
- Session management improvements

### v1.1.0 (2026-02-07)
- Initial management dashboard
- Vendor and stock analytics
- Delivery performance tracking

---

## 📝 Author
Developed and Maintained by **Ardyansyah P.**

---

<p align="center">
  Released under the <b>MIT License</b>.<br>
  Copyright &copy; 2026 Mada Wikri Tunggal.
</p>
