# Project Setup Guide

This document explains how to set up the project after cloning the repository, including Filament Admin setup.

---

## 1. Clone the Repository
```bash
git clone <repo-url>
cd <project-folder>
````

---

## 2. Install Dependencies

```bash
composer install
npm install
npm run build
```

---

## 3. Environment Setup

* Copy `.env.example` to `.env`

```bash
cp .env.example .env
```

* Update database credentials in `.env`
* Generate app key:

```bash
php artisan key:generate
```

---

## 4. Run Migrations

```bash
php artisan migrate
```

---

## 5. Filament Admin Panel

This project uses [Filament](https://filamentphp.com/docs/3.x/panels/installation) for the admin panel.

* Default Filament path has been changed from `/admin` to `/`.
* You can access the admin panel directly at:

```
http://your-app.test/
```

---

## 6. Create an Admin User

Filament uses your Laravel authentication. To create the first admin user, run:

```bash
php artisan make:filament-user
```

Follow the prompts to enter:

* Name
* Email
* Password

You can now log in with this account at `/`.

---

## 7. Serve the Application

Run the local development server:

```bash
php artisan serve
```

The app will be available at:

```
http://127.0.0.1:8000
```

---

## 8. Learning Filament

To learn how to use and extend Filament:

* [Filament Documentation](https://filamentphp.com/docs/3.x/panels/installation)
* [Widgets & Resources](https://filamentphp.com/docs/3.x/panels/resources)
* [Dashboard Customization](https://filamentphp.com/docs/3.x/panels/dashboard)
