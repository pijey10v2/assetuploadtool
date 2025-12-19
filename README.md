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
✅ Responsive Bootstrap 5.3.8 UI with loading spinner and Select2 dropdowns  
✅ SQL Server integration for dynamic dropdowns (e.g., Project Layers)  

# ⚙️ Requirements
| Requirement   | Version               |
| ------------- | --------------------- |
| PHP           | ≥ 8.2 (PHP 8.3.26)    |
| Laravel       | 11.0                  |
| Composer      | ≥ 2.x                 |
| Database      | SQL Server            |
| Queue Driver  | `database` or `redis` |

# 📦 Installation Guide
1️⃣ Clone the Repository
```
git clone https://github.com/pijey10v2/assetuploadtool.git
cd assetuploadtool
```

2️⃣ Install PHP Dependencies
```
composer install

```
3️⃣ Set Up Environment File

Copy .env.example and configure your database, cache, and queue drivers.  
Rename the file to .env and update the values as needed.

Then open .env and configure these values:

```
APP_NAME="Asset Upload Tool"  
APP_ENV=local  
APP_DEBUG=true  
APP_URL=http://localhost:8080/
```

# Database (SQL Server)
```
DB_CONNECTION=sqlsrv  
DB_HOST=your_host          
DB_DATABASE=your_db  
DB_USERNAME=your_username  
DB_PASSWORD=your_password  
```

# Cache / Queue
```
CACHE_DRIVER=file  
QUEUE_CONNECTION=your_db  
```

# External API
```
JOGET_API_URL=https://ams.reveronconsulting.com/JavaBridge/asset/index.php
```

# External API (Get All Tables from Joget DB)
```
API_GET_ALL_TABLES_URL=${JOGET_API_URL}?mode=get_all_tables
```

# 🧰 Database Setup

1️⃣ Run Migrations
```
php artisan migrate
```

2️⃣ (Optional) Add Queue Table

If you use the database queue driver, run this migration:
```
php artisan queue:table  
php artisan migrate
```

# 🧾 Application Setup

Create Upload Directories  

Ensure these directories exist and are writable:  

storage/app/uploads  
storage/app/bimfiles  

# ⚙️ Queue Worker (for Progress Tracking)

The app uses Laravel Jobs (e.g., ProcessExcelInsertJob) to insert data asynchronously and track progress.  

To run the queue worker manually:  
```
php artisan queue:work
```

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
```
Cache::put("upload_progress_{$jobId}", [  
    'status' => 'processing',  
    'processed' => $processed,  
    'total' => $totalRows,  
    'inserted' => $inserted,  
    'progress' => round(($processed / $totalRows) * 100)  
]);  
```
You can view cached progress in Tinker:  
```
php artisan tinker  
Cache::get('upload_progress_upload_68f84412bb21c3.09257791');
```

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
| `Allowed memory size exhausted`                 | Increase memory in `php.ini` → `memory_limit = 10G`                    |
| Progress bar not updating                       | Confirm `php artisan queue:work` is running and `CACHE_DRIVER=file`      |
| BIM file not found                              | Verify `.bim` files exist under `storage/app/bimfiles`                   |
| AJAX error 419                                  | Ensure CSRF token is present in your AJAX request headers                |

Additional:  

Make sure that the extensions for sqlsrv is enabled at php.ini file
```
extension=php_sqlsrv_83_nts_x64.dll  
extension=php_pdo_sqlsrv_83_nts_x64.dll  
```
Make sure that the extensions for sqlite is enabled at php.ini file
```
extension=pdo_sqlite  
extension=sqlite3  
```
# ✅ 1️⃣ Add a Binding in IIS

Open IIS Manager (inetmgr from Run dialog).

In the left Connections pane, expand your server and click your Laravel site (e.g., “AssetUploadTool”).

In the Actions pane (right side), click “Bindings…”

In the Site Bindings window, click Add…

Fill it out like this:

Type: http

IP address: All Unassigned (or your server’s IP)

Port: 80

Hostname: dg.asset.tool

Click OK, then Close.

✅ What this does:
This tells IIS to respond to http://dg.asset.tool requests and route them to your Laravel app folder.

# ✅ 2️⃣ Edit Your Local Hosts File

You need to tell Windows where to find dg.asset.tool since it’s not a real domain.

Open Notepad as Administrator.

Open the file:

C:\Windows\System32\drivers\etc\hosts

Add this line at the bottom:

127.0.0.1    dg.asset.tool

Save the file.

✅ Now you can open:
👉 http://dg.asset.tool
…and it will load your Laravel app hosted in IIS.

# ✅ 3️⃣ Configure Laravel’s APP_URL

Open your Laravel project’s .env file and update:

APP_URL=http://dg.asset.tool

Then clear the configuration cache:
```
php artisan config:clear
```
or in PowerShell:
```
php artisan config:clear
```
Laravel uses APP_URL for things like redirects, asset URLs, and email links.

# 🛠️ PHP Configuration

Make sure your php.ini file is properly configured to handle large file uploads and longer request times.
You can update the following settings in your php.ini file:
```
max_file_uploads = 100  
upload_max_filesize = 5000M  
post_max_size = 5000M  
max_execution_time = 0
max_input_time = 60  
max_input_vars = 900000
memory_limit = 10G
```
💡 Note:
After updating your php.ini, restart your web server (e.g., IIS, Apache, Nginx, or PHP-FPM) to apply the changes.  

# Additional SQL Query Update:

Make sure to add UNIQUE KEY on asset table (e.g. app_fd_inv_furniture) for columns: c_import_batch, c_model_element:

Add UNIQUE KEY:

```
ALTER TABLE jwdb.app_fd_inv_furniture
ADD UNIQUE KEY uniq_model_batch (c_model_element(191), c_import_batch(191));
```

Check if the UNIQUE KEY is added:

```
SHOW INDEX
FROM jwdb.app_fd_inv_furniture
WHERE Key_name = 'uniq_model_batch';
```

# Increase Session Lifetime:

Set to 8 hours:
```
SESSION_LIFETIME=480
```

# 👨‍💻 Author

Paolo Jon B. Caraig  
💼 Software Developer  
🧩 Laravel | PHP | JS | SQL Server

# 🏁 License

This project is proprietary and intended for internal use within Reveron Consulting/Digile.  
All rights reserved © 2025
