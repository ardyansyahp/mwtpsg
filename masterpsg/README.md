# 🚀 Master PSG - Central Master Data Management System

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

## 📌 Project Overview
**Master PSG** is the central hub for managing master data across the MWT PSG ecosystem. It handles companies, machines, manpower, plant gates, vehicles, parts, molds, and raw materials with comprehensive permission-based access control.

---

## ✨ Key Features
- 🏢 **Master Data Management**: Companies (Perusahaan), Machines (Mesin), Manpower, Plant Gates, Vehicles (Kendaraan)
- ⚙️ **Sub-Master Modules**: 
    - **Parts**: Full lifecycle tracking of manufacturing parts
    - **Plant Gate Part**: Relational mapping between gates and parts
    - **Mold Management**: Track single and family molds including cavity details
    - **Bahan Baku**: Raw material tracking and management
- 🔄 **Smart Import/Export**: Custom CSV parsing with dynamic column mapping and persistence
- 🗑️ **Recycle Bin System**: Soft delete functionality across all modules with easy restoration
- ⚡ **Real-time Status Control**: AJAX-based status toggling for improved UX
- 🔒 **Dynamic Permissions**: Role-based access control for all sensitive actions
- 👤 **Profile Management**: Kabag users can manage their own username and password
- 🔍 **Global Search**: Quick search across all master data modules

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
- ✅ Error message display on homepage for access denied notifications
- ✅ Success/error messages with icons

### Routes & Controllers
- ✅ ProfileController with username and password update methods
- ✅ Routes: `profile.edit`, `profile.update.username`, `profile.update.password`
- ✅ Validation for unique username and password confirmation

---

## 🛠 Technology Stack
- **Framework**: [Laravel 10+](https://laravel.com)
- **Database**: MySQL / MariaDB (Shared across all PSG apps)
- **Frontend**: Blade Templating, Tailwind CSS, Alpine.js
- **Tools**: PHP 8.1+, Composer, NPM

---

## 📈 Version Information
- **Current Version**: `v1.3.0-stable`
- **Build Status**: 🟢 Stable
- **Last Updated**: 2026-02-09 07:44 (UTC+7)

---

## 🔗 Related Applications
This application is part of the **MWT PSG Ecosystem**:
1. **masterpsg** (this app) - Central master data management
2. **supplierpsg** - Supplier and raw material management
3. **shippingpsg** - Shipping, delivery, and logistics
4. **managementmwt** - Executive dashboard for management

All applications share the same database and session for seamless integration.

---

## 🚀 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/ardyansyahp/masterpsg.git
   cd masterpsg
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

4. **Database Migration**
   ```bash
   php artisan migrate --seed
   ```

5. **Run Locally**
   ```bash
   php artisan serve
   ```

---

## 📝 Changelog

### v1.3.0 (2026-02-09)
- Added Profile Settings feature for Kabag users
- Enhanced User model with `isKabag()` method
- Improved error handling and user feedback
- Added ProfileController and related routes

### v1.2.0 (2026-02-03)
- Smart Import/Export functionality
- Recycle Bin system implementation
- Dynamic permissions system

### v1.1.0 (2026-02-02)
- Initial master data modules
- Sub-master modules implementation

---

## 📝 Author
Developed and Maintained by **Ardyansyah P.**

---

<p align="center">
  Released under the <b>MIT License</b>.<br>
  Copyright &copy; 2026 Mada Wikri Tunggal.
</p>
