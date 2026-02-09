# 🏭 MWT PSG Ecosystem - Integrated Manufacturing Management System

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>

<p align="center">
  <strong>A comprehensive, multi-application ecosystem for manufacturing operations management</strong>
</p>

---

## 📌 Overview

**MWT PSG** is an integrated suite of 4 Laravel applications designed to manage the complete manufacturing lifecycle - from master data and supplier management to shipping logistics and executive analytics. All applications share a unified database and session management for seamless cross-application workflows.

---

## 🎯 Applications

### 1. 🗂️ **Master PSG** - Central Master Data Hub
**Directory**: `masterpsg/`  
**Port**: 8000  
**Access**: All authenticated users

The central repository for all master data across the ecosystem.

**Key Features**:
- Master Data: Companies, Machines, Manpower, Plant Gates, Vehicles
- Sub-Master: Parts, Molds, Raw Materials, Plant Gate-Part Mapping
- Smart CSV Import/Export with column mapping
- Recycle Bin system with soft deletes
- Global search across all modules
- Profile Settings for Kabag users

---

### 2. 📦 **Supplier PSG** - Supply Chain Management
**Directory**: `supplierpsg/`  
**Port**: 8001  
**Access**: Users with supplier-related permissions

Comprehensive supplier and raw material management system.

**Key Features**:
- Control Supplier Dashboard with real-time monitoring
- Raw Material Receiving with CSV import
- Supplier performance tracking
- Interactive tables with frozen columns and fullscreen mode
- Data accumulation logic for multiple entries
- Profile Settings for Kabag users

---

### 3. 🚚 **Shipping PSG** - Logistics & Delivery
**Directory**: `shippingpsg/`  
**Port**: 8002  
**Access**: Users with shipping-related permissions

End-to-end logistics and delivery management platform.

**Key Features**:
- Finished Goods stock management (In/Out scanning)
- SPK (Work Order) management
- Delivery dispatch and assignment
- Real-time GPS tracking for drivers
- Control Truck monitoring
- Delivery performance analytics
- Driver-focused simplified interface
- Profile Settings for Kabag users

---

### 4. 📊 **Management MWT** - Executive Dashboard
**Directory**: `managementmwt/`  
**Port**: 8003  
**Access**: Management users only (role = 2)

Executive-level dashboard for high-level analytics and decision making.

**Key Features**:
- Executive Overview with KPIs
- Vendor performance dashboard
- Stock status across all warehouses
- Delivery performance metrics
- 6-month performance trends
- Management-only access control
- Profile Settings for all management users

---

## 🔗 System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Shared MySQL Database                     │
│                   (mwt_psg_production)                       │
└─────────────────────────────────────────────────────────────┘
                              ▲
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
┌───────▼────────┐   ┌───────▼────────┐   ┌───────▼────────┐
│  Master PSG    │   │ Supplier PSG   │   │ Shipping PSG   │
│   (Port 8000)  │   │  (Port 8001)   │   │  (Port 8002)   │
│                │   │                │   │                │
│ • Master Data  │   │ • Suppliers    │   │ • Logistics    │
│ • Sub-Master   │   │ • Receiving    │   │ • Delivery     │
│ • Permissions  │   │ • Analytics    │   │ • GPS Track    │
└────────────────┘   └────────────────┘   └────────────────┘
                              │
                    ┌─────────▼──────────┐
                    │  Management MWT    │
                    │   (Port 8003)      │
                    │                    │
                    │ • Executive View   │
                    │ • Analytics        │
                    │ • Performance      │
                    └────────────────────┘
```

---

## ✨ Latest Updates (v1.3.0 - Feb 9, 2026)

### 🎉 Profile Settings for Kabag Users
All applications now support automatic Profile Settings for users with "Kabag" (Head of Department) position:
- ✅ Automatic detection via `isKabag()` method
- ✅ Change username and password without superadmin permission
- ✅ Auto-logout after password change for security
- ✅ Modern card-based UI with gradient headers

### 🔒 Enhanced Access Control
- ✅ Management MWT now restricted to role = 2 only
- ✅ Improved error handling and user feedback
- ✅ Consistent sidebar navigation across all apps

### 🐛 Bug Fixes
- ✅ Fixed homepage KPI permissions in Shipping PSG
- ✅ Corrected permission slugs to match database

---

## 🛠 Technology Stack

- **Framework**: Laravel 10+
- **Database**: MySQL / MariaDB (Shared)
- **Frontend**: Blade Templates, Tailwind CSS, Alpine.js, Vue 3
- **Maps**: Leaflet.js for GPS tracking
- **Session**: Database-driven shared sessions
- **Tools**: PHP 8.1+, Composer, NPM

---

## 🚀 Installation

### Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js & NPM
- MySQL / MariaDB
- Web server (Apache/Nginx) or Laravel Valet

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/ardyansyahp/mwtpsg.git
   cd mwtpsg
   ```

2. **Setup Each Application**

   For each application (masterpsg, supplierpsg, shippingpsg, managementmwt):

   ```bash
   cd [app-directory]
   composer install
   npm install && npm run build
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configure Environment**

   Update `.env` for each app with shared database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=mwt_psg_production
   DB_USERNAME=your_username
   DB_PASSWORD=your_password

   SESSION_DRIVER=database
   SESSION_DOMAIN=.mwtpsg.test
   ```

4. **Database Migration** (Only from masterpsg)
   ```bash
   cd masterpsg
   php artisan migrate:fresh --seed
   ```

5. **Run Applications**

   Open 4 terminal windows and run:
   ```bash
   # Terminal 1 - Master PSG
   cd masterpsg && php artisan serve --port=8000

   # Terminal 2 - Supplier PSG
   cd supplierpsg && php artisan serve --port=8001

   # Terminal 3 - Shipping PSG
   cd shippingpsg && php artisan serve --port=8002

   # Terminal 4 - Management MWT
   cd managementmwt && php artisan serve --port=8003
   ```

6. **Access Applications**
   - Master PSG: http://localhost:8000
   - Supplier PSG: http://localhost:8001
   - Shipping PSG: http://localhost:8002
   - Management MWT: http://localhost:8003

---

## 👥 User Roles & Access

| Role | Value | Access |
|------|-------|--------|
| **User** | 0 | Permission-based access to Master, Supplier, and Shipping PSG |
| **Superadmin** | 1 | Full access to Master PSG, user management, permissions |
| **Management** | 2 | Access to Management MWT dashboard + permission-based access to other apps |

### Special Access: Kabag Users
Users with "Kabag" in their position (bagian field) automatically get:
- Profile Settings menu in all applications
- Ability to change their own username and password
- No superadmin permission required

---

## 📝 Version Information

- **Current Version**: v1.3.0
- **Release Date**: February 9, 2026
- **Build Status**: 🟢 Stable
- **License**: MIT

---

## 📚 Documentation

Each application has its own detailed README:
- [Master PSG README](masterpsg/README.md)
- [Supplier PSG README](supplierpsg/README.md)
- [Shipping PSG README](shippingpsg/README.md)
- [Management MWT README](managementmwt/README.md)

---

## 🔄 Changelog

### v1.3.0 (2026-02-09)
- Added Profile Settings for Kabag users across all apps
- Enhanced User model with `isKabag()` method
- Improved access control for Management MWT
- Fixed homepage KPI permissions in Shipping PSG
- Updated all READMEs with comprehensive documentation

### v1.2.0 (2026-02-08)
- Fixed role-based authentication
- Updated to use role column instead of is_superadmin
- Session management improvements

### v1.1.0 (2026-02-05)
- Sidebar cross-app navigation
- Shared session implementation
- Permission-based menu visibility

---

## 👨‍💻 Development Team

**Lead Developer**: Ardyansyah P.  
**Organization**: Mada Wikri Tunggal  
**Repository**: https://github.com/ardyansyahp/mwtpsg

---

## 📄 License

This project is released under the **MIT License**.

Copyright © 2026 Mada Wikri Tunggal. All rights reserved.

---

<p align="center">
  <strong>Built with ❤️ using Laravel</strong>
</p>
