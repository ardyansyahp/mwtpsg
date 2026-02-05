# 🚀 S2SMWT - Trace Management System

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

## 📌 Project Overview
**Trace (S2SMWT)** is a comprehensive Warehouse and Sub-Master management system built with Laravel. It focuses on tracking parts, plant gates, molds, and raw materials with a high emphasis on data integrity, audit trails (soft deletes), and seamless import/export operations.

---

## ✨ Key Features
- 🏢 **Master Data Management**: Companies (Perusahaan), Plant Gates, Vehicles (Kendaraan).
- ⚙️ **Sub-Master Modules**: 
    - **Parts**: Full lifecycle tracking of manufacturing parts.
    - **Plant Gate Part**: Robust relational mapping between gates and parts.
    - **Mold Management**: Track single and family molds including cavity details.
    - **Bahan Baku**: Raw material tracking and management.
- 🔄 **Smart Import/Export**: Custom CSV parsing with dynamic column mapping and persistence.
- 🗑️ **Recycle Bin System**: Soft delete functionality across all modules with easy restoration.
- ⚡ **Real-time Status Control**: AJAX-based status toggling for improved UX.
- 🔒 **Dynamic Permissions**: Role-based access control for all sensitive actions.

---

## 🛠 Technology Stack
- **Framework**: [Laravel 10+](https://laravel.com)
- **Database**: MySQL / MariaDB
- **Frontend**: Blade Templating, Tailwind CSS, Vanilla JS
- **Tools**: PHP 8.1+, Composer, NPM

---

## 📈 Version Information
- **Current Version**: `v1.2.0-stable`
- **Build Status**: 🟢 Stable
- **Last Edited**: 2026-02-03 14:15 (UTC+7)

---

## 🚀 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/ardyansyahp/s2smwt.git
   cd s2smwt
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

## 📝 Author
Developed and Maintained by **Ardyansyah P.**

---

<p align="center">
  Released under the <b>MIT License</b>.<br>
  Copyright &copy; 2026 Mada Wikri Tunggal.
</p>
