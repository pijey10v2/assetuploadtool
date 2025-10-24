# 🧱 Asset Upload Tool — Laravel Application

A Laravel-based application designed to upload, process, and map BIM (.bim / SQLite) and Excel files.
The tool provides data mapping, real-time progress updates, and automated data insertion through background jobs.

# 🚀 Features

✅ Upload and process .bim, .sqlite3, and Excel (.xlsx, .xls, .csv) files  
✅ Reads BIM data directly from SQLite-based .bim files  
✅ Extracts and maps Excel header columns dynamically  
✅ Displays database vs raw data mapping table with editable dropdowns  
✅ Supports progress tracking during background processing (via Laravel Jobs & Cache)  
✅ Uses AJAX for upload and mapping without reloading the page  
✅ Responsive Bootstrap 5.3.8 UI with progress bars and Select2 dropdowns  
✅ SQL Server integration for dynamic dropdowns (e.g., Project Layers)  

# ⚙️ Requirements
| Requirement   | Version               |
| ------------- | --------------------- |
| PHP           | ≥ 8.2                 |
| Laravel       | 10.x                  |
| Node.js & npm | ≥ 18.x                |
| Composer      | ≥ 2.x                 |
| Database      | SQL Server            |
| Queue Driver  | `database` or `redis` |

# 📦 Installation Guide
1️⃣ Clone the Repository

git clone https://github.com/pijey10v2/assetuploadtool.git
cd assetuploadtool

2️⃣ Install PHP Dependencies

composer install

3️⃣ Set Up Environment File

Copy .env.example and configure your database, cache, and queue drivers.  
Rename the file to .env and update the values as needed.

Then open .env and configure these values:

APP_NAME="Asset Upload Tool"  
APP_ENV=local  
APP_DEBUG=true  
APP_URL=http://localhost:8080/

# Database (SQL Server)
DB_CONNECTION=sqlsrv
DB_HOST=DT-PH-1016\SQLEXPRESS        
DB_DATABASE=RI_Constructs_Assets_V4
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Cache / Queue
CACHE_DRIVER=file
QUEUE_CONNECTION=RI_Constructs_Assets_V4

# External API
JOGET_API_URL=https://ams.reveronconsulting.com/JavaBridge/asset/index.php

# External API (Get All Tables from Joget DB)
API_GET_ALL_TABLES_URL=${JOGET_API_URL}?mode=get_all_tables

# 🧰 Database Setup

1️⃣ Run Migrations

php artisan migrate

2️⃣ (Optional) Add Queue Table

If you use the database queue driver, run this migration:

php artisan queue:table  
php artisan migrate

# 🧾 Application Setup

Create Upload Directories  

Ensure these directories exist and are writable:  

storage/app/uploads  
storage/app/bimfiles  

# ⚙️ Queue Worker (for Progress Tracking)

The app uses Laravel Jobs (e.g., ProcessExcelInsertJob) to insert data asynchronously and track progress.  

To run the queue worker manually:  

php artisan queue:work

✅ Tip:  
You can also run it automatically when the server starts — add this to your Windows Task Scheduler or Supervisor configuration.  

# 🧮 File Naming Convention

Uploaded files keep their original names, but automatically append a readable timestamp for uniqueness:  

MyRawData-2025-10-22 14-45-32.xlsx  
RoadElements-2025-10-22 14-45-32.bim  

# 🧑‍💻 API Endpoints Used

| Mode                | Description                      |
| ------------------- | -------------------------------- |
| `get_all_tables   ` | Fetches database tables         |
| `get_table_columns` | Fetches database table columns   |
| `get_excel_columns` | Extracts raw Excel file columns  |
| `insert_asset_data` | Inserts or updates asset records |

These APIs are integrated with:  

https://ams.reveronconsulting.com/JavaBridge/asset/index.php  

# 🖥️ Frontend Features

AJAX Upload using jQuery  
Loading Spinner while processing  
Dynamic data mapping table with searchable Select2 dropdowns  
“Execute Data Update” button triggers background job with live progress polling  
Fixed table height with scrollable area  
Responsive Bootstrap layout for dashboard and upload page  

# 📊 Progress Tracking

The backend uses Laravel’s Cache facade to store progress data for each job:  

Cache::put("upload_progress_{$jobId}", [  
    'status' => 'processing',  
    'processed' => $processed,  
    'total' => $totalRows,  
    'inserted' => $inserted,  
    'progress' => round(($processed / $totalRows) * 100)  
]);  

You can view cached progress in Tinker:  

php artisan tinker  
Cache::get('upload_progress_upload_68f84412bb21c3.09257791');

Or if using the file cache driver:  

storage/framework/cache/data/

# 🧱 Folder Structure

resources/  
│  
├── views/  
│   ├── layouts/  
│   │   └── app.blade.php  
│   └── uploadtool/  
│       ├── index.blade.php  
│       ├── _form.blade.php  
│       ├── _mapping-table.blade.php   
├── public/js/  
│       ├── uploadtool.js  
app/  
├── Http/  
│   ├── Controllers/  
│   │   └── UploadToolController.php  
│   └── Middleware/  
│       └── RedirectIfAuthenticated.php  
├── Jobs/  
│   └── ProcessExcelInsertJob.php  

# 🔍 Troubleshooting

| Issue                                           | Solution                                                                 |
| ----------------------------------------------- | ------------------------------------------------------------------------ |
| `cURL error 77: error setting certificate file` | Ensure `php.ini` has a valid `curl.cainfo` path pointing to `cacert.pem` |
| `Allowed memory size exhausted`                 | Increase memory in `php.ini` → `memory_limit = 1024M`                    |
| Progress bar not updating                       | Confirm `php artisan queue:work` is running and `CACHE_DRIVER=file`      |
| BIM file not found                              | Verify `.bim` files exist under `storage/app/bimfiles`                   |
| AJAX error 419                                  | Ensure CSRF token is present in your AJAX request headers                |

Additional:  

Make sure that the extensions for sqlsrv is enabled at php.ini file

extension=php_sqlsrv_83_nts_x64.dll  
extension=php_pdo_sqlsrv_83_nts_x64.dll  

Make sure that the extensions for sqlite is enabled at php.ini file

extension=pdo_sqlite  
extension=sqlite3  

# 👨‍💻 Author

Paolo Jon B. Caraig  
💼 Software Developer  
🧩 Laravel | PHP | JS | SQL Server

# 🏁 License

This project is proprietary and intended for internal use within Reveron Consulting.  
All rights reserved © 2025

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
