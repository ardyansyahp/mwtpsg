# 🚀 Supplier PSG - Supplier & Raw Material Management System

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

## 📌 Project Overview
**Supplier PSG** is a comprehensive supplier and raw material management system. It handles supplier monitoring, raw material receiving, and provides real-time dashboards for supply chain visibility.

---

## ✨ Key Features
- 📊 **Control Supplier Dashboard**: Real-time monitoring of supplier performance and delivery status
- 📦 **Bahan Baku Receiving**: Raw material receiving with CSV import and accumulation logic
- 🔄 **Smart Import System**: CSV/TXT import with dynamic column mapping and data persistence
- 📈 **Performance Metrics**: Supplier performance tracking and analytics
- 🗺️ **Interactive Tables**: Frozen columns, fullscreen mode, and advanced filtering
- 🔒 **Permission-Based Access**: Role-based access control for all modules
- 👤 **Profile Management**: Kabag users can manage their own username and password

---

## 🆕 Latest Updates (v1.3.0 - Feb 9, 2026)

### Profile Settings for Kabag Users
- ✅ Added automatic Profile Settings menu for users with "Kabag" position
- ✅ Kabag users can change their username and password without superadmin permission
- ✅ Auto-logout after password change for security
- ✅ Modern card-based UI with gradient headers

### User Model Enhancements
- ✅ Added `isKabag()` method for automatic Kabag detection
- ✅ Case-insensitive detection of "Kabag" in bagian field
- ✅ Relationship with manpower table for position checking

### UI/UX Improvements
- ✅ Profile Settings menu with purple theme (distinct from other menus)
- ✅ Divider before Profile Settings for visual separation
- ✅ Consistent sidebar navigation across all PSG applications

### Routes & Controllers
- ✅ ProfileController with username and password update methods
- ✅ Routes: `profile.edit`, `profile.update.username`, `profile.update.password`
- ✅ Validation for unique username and password confirmation

---

## 🛠 Technology Stack
- **Framework**: [Laravel 10+](https://laravel.com)
- **Database**: MySQL / MariaDB (Shared across all PSG apps)
- **Frontend**: Blade Templating, Tailwind CSS, Vue 3
- **Tools**: PHP 8.1+, Composer, NPM

---

## 📈 Version Information
- **Current Version**: `v1.3.0-stable`
- **Build Status**: 🟢 Stable
- **Last Updated**: 2026-02-09 07:44 (UTC+7)

---

## 🔗 Related Applications
This application is part of the **MWT PSG Ecosystem**:
1. **masterpsg** - Central master data management
2. **supplierpsg** (this app) - Supplier and raw material management
3. **shippingpsg** - Shipping, delivery, and logistics
4. **managementmwt** - Executive dashboard for management

All applications share the same database and session for seamless integration.

---

## 🚀 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/ardyansyahp/supplierpsg.git
   cd supplierpsg
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
   php artisan serve --port=8001
   ```

---

## 📝 Changelog

### v1.3.0 (2026-02-09)
- Added Profile Settings feature for Kabag users
- Enhanced User model with `isKabag()` method
- Improved sidebar navigation consistency
- Added ProfileController and related routes

### v1.2.0 (2026-02-03)
- Control Supplier dashboard improvements
- Frozen columns and fullscreen mode fixes
- Performance optimizations

### v1.1.0 (2026-02-02)
- CSV import with accumulation logic
- Dynamic column mapping
- Receiving module implementation

---

## 📝 Author
Developed and Maintained by **Ardyansyah P.**

---

<p align="center">
  Released under the <b>MIT License</b>.<br>
  Copyright &copy; 2026 Mada Wikri Tunggal.
</p>
