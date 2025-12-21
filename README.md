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

## 9. Guide on Migration

### 1. Create a New Migration

Run Artisan command:

```bash
php artisan make:migration update_users_table_add_status_column --table=users
```

* Use `--table=your_table` to target an existing table.
* Use a descriptive name for clarity.

---

### 2. Edit the Migration File

Inside `database/migrations/..._update_users_table_add_status_column.php`:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add new column
            $table->string('status')->default('active');

            // Example: modify an existing column (needs doctrine/dbal)
            // $table->string('email', 150)->change();

            // Example: drop a column
            // $table->dropColumn('old_column');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');

            // To reverse changes if you modified a column:
            // $table->string('email', 255)->change();
        });
    }
};
```

---

### 3. Install Doctrine DBAL if You’ll Modify Columns

Laravel requires **DBAL** for altering existing columns (changing length, type, nullable, etc.):

```bash
composer require doctrine/dbal
```

---

### 4. Run the Migration

```bash
php artisan migrate
```

Rollback if needed:

```bash
php artisan migrate:rollback
```

---

### 5. Common Examples

* **Add multiple columns**:

  ```php
  $table->string('phone')->nullable();
  $table->date('birthdate')->nullable();
  ```

* **Change column type**:

  ```php
  $table->integer('age')->nullable()->change();
  ```

* **Rename column**:

  ```php
  $table->renameColumn('username', 'user_name');
  ```

* **Drop multiple columns**:

  ```php
  $table->dropColumn(['middle_name', 'nickname']);
  ```

