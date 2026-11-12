
# ICT3612 Assignment 3 — PHP Tasks

A brief description of what this project does and who it's for.
This repository contains four small PHP assignment tasks: database-design documentation, a MySQL CRUD/query page, an image-dataset annotation counter, and an employee-training management application. The task pages are independent exercises and do not share a single database configuration.

> **Project location:** the runnable PHP pages are in [`17945968_Mpwanga_EM/`](17945968_Mpwanga_EM/). The repository-root `index.php` is an empty file, so open one of the task pages directly (for example, `task1.php`) rather than expecting the root URL to show a landing page.

## Contents

- [Requirements](#requirements)
- [Run locally](#run-locally)
- [Task guide](#task-guide)
- [Database setup](#database-setup)
- [Dataset ZIP format](#dataset-zip-format)
- [Troubleshooting](#troubleshooting)
- [Security and limitations](#security-and-limitations)

## Requirements

- PHP with a web server (PHP 8.0 or newer is a sensible starting point; the repository does not declare a PHP version requirement).
- PHP extensions:
  - `mysqli` for Tasks 2 and 4.
  - `zip` (the `ZipArchive` class) for Task 3.
- MySQL or MariaDB for Tasks 2 and 4, with a database and schema that match the selected task.
- Write permission for the web-server process in the Task 3 directory so it can create `uploads/` and `dataset/`.

No Composer dependencies or JavaScript package installation are required by the PHP pages themselves. The GitHub Actions file refers to Symfony, Composer, and PHPUnit, but those application files/dependencies are not present in this repository; it is not a reliable way to start or test these assignment pages.

## Run locally

1. Install PHP and the required extensions. On Debian/Ubuntu, packages are commonly named `php`, `php-mysql`, and `php-zip` (package names can vary by distribution).
2. Open a terminal in the PHP project directory:

   ```sh
   cd 17945968_Mpwanga_EM
   ```

3. For Task 1 or Task 3, start PHP's development server:

   ```sh
   php -S 127.0.0.1:8000
   ```

4. Open the page you want:

   - `http://127.0.0.1:8000/task1.php` — database design
   - `http://127.0.0.1:8000/task3.php` — dataset counter
   - `http://127.0.0.1:8000/task2.php` — database CRUD/query page (requires its database setup)
   - `http://127.0.0.1:8000/task4.php` — training management system (requires its database setup)

The menu links between the task pages. You can use Apache or another PHP-capable web server instead; set its document root to `17945968_Mpwanga_EM/`. Do not use PHP's built-in server as a public production server.

## Task guide

### Task 1 — database design (`task1.php`)

A mostly static HTML/PHP page describing relational database designs. It covers a module/lecturer/role relationship and a users/events/registrations design, with keys and relationships, and displays the accompanying ER diagram image. It does not connect to a database.

### Task 2 — module/lecturer CRUD and SQL examples (`task2.php`)

A MySQL-backed page for working with the academic module schema. It provides forms to add a module, update a module/lecturer/role assignment, and delete a lecturer together with associated assignments. It also displays tables and offers five fixed example queries (ordering, `LIKE`, joins, filtering, and `MAX`).

The SQL in this page expects tables named `Modules`, `Lecturers`, `Roles`, and `ModuleLecturerRole`. Create those tables and their keys in the database configured in `task2.php` before opening the page. This repository does not include a clean, standalone SQL setup file for this Task 2 schema.

### Task 3 — dataset annotation counter (`task3.php`)

Accepts a ZIP upload containing a dataset. It reads class names from `classes.txt`, scans `.txt` annotation files under `train/`, `validate/`, and `test/`, then counts each annotation line by its leading class ID and reports per-class and per-folder totals. See [Dataset ZIP format](#dataset-zip-format).

The script creates `uploads/` and `dataset/` alongside `task3.php`. Each new upload removes the previous extracted dataset before extracting the new archive.

### Task 4 — Employee Training Management System (`task4.php`)

A single-file PHP application organized into sections rather than separate source files:

1. **Configuration and helpers** — starts a session, configures the database connection, input/output helpers, flash messages, and role checks.
2. **Models** — `Company`, `User`, `Employee`, `Training`, and `Registration` encapsulate database operations.
3. **Controllers** — authentication, company, employee, training, registration, and administrator controllers handle form actions and access checks.
4. **Routing and views** — the `page` query parameter selects the home, login, registration, company, administrator, or reports view; HTML and styles are rendered in the same file.

Company users can manage their employees and training registrations. Administrators can review/manage companies, employees, and courses and view reports, including company costs and participation summaries. Navigation is included from `menu.inc` and `menu4.inc`.

Examples of page routes include `task4.php?page=home`, `task4.php?page=login`, `task4.php?page=register`, `task4.php?page=dashboard`, `task4.php?page=employees`, `task4.php?page=training`, `task4.php?page=registrations`, `task4.php?page=admin`, and `task4.php?page=reports`. The page only shows routes appropriate to the signed-in role.

## Database setup

Tasks 2 and 4 connect to different databases and expect different schemas. Configure and prepare the database for the task you intend to use; Task 1 and Task 3 do not require MySQL.

### Task 2

`task2.php` currently defines its MySQL host, username, password, and database name near the top of the file. Replace these values with credentials for your own MySQL/MariaDB instance. The required academic schema comprises:

- `Modules` — module code and module name.
- `Lecturers` — lecturer ID and lecturer name.
- `Roles` — role ID and role name.
- `ModuleLecturerRole` — module code, lecturer ID, role ID, and year; references the other three tables.

The page's forms and queries rely on these table and column names. Add some matching records before trying the update, delete, and query forms.

### Task 4

`task4.php` defines `DB_HOST`, `DB_USER`, `DB_PASS`, and `DB_NAME` near the beginning of the file. Set them to your own database details before running this page. The app expects these tables:

- `Companies` — company details.
- `Users` — login name, password hash, role, and optional company association.
- `Employees` — employees belonging to companies.
- `Training` — course name, cost, and duration.
- `Registrations` — employee/course registrations and date.

A copy of the Task 4 source and its schema/sample SQL is appended to `17945968_Mpwanga_EM/task4.txt`. It is a source-code display file with SQL appended, **not** a clean SQL dump: if using it to prepare a database, copy and run only the SQL section beginning at `CREATE TABLE Companies`, after creating/selecting your database. Review the sample data before inserting it. The similarly appended schema in `task2.txt` also describes the training application; it is not the academic schema used by `task2.php`.

The Task 4 authentication code has an inconsistency: account creation stores `password_hash()` values, but login compares the submitted plain-text password directly to the database `Password` field instead of using `password_verify()`. Consequently, company accounts registered through the app will not be able to log in as written. The sample SQL seeds plain-text passwords (`admin123` and `company123`); these match the current comparison but are not safe password storage. Fix the login method to fetch the user by username and verify the submitted password with `password_verify()` before using the sample accounts or creating new accounts. Create an administrator account with an `ADMIN` role if you need administrator pages.

> **Credentials:** database credentials are currently hard-coded in the PHP files, and the source/notes include previously used connection details. Do not reuse those credentials. Replace them with local credentials, rotate any real credentials that have been published, and keep secrets out of version control.

## Dataset ZIP format

The counter expects a ZIP with one dataset directory at its root, for example:

```text
my-dataset.zip
└── dataset-name/
    ├── classes.txt
    ├── train/
    │   ├── image-001.txt
    │   └── ...
    ├── validate/
    │   └── ...
    └── test/
        └── ...
```

Each non-empty line in `classes.txt` defines a class ID, starting at `0` for the first line. Each annotation file should contain one object per line in the form:

```text
<class-id> <x> <y> <width> <height>
```

The counter increments the class corresponding to the first value on each valid-looking line. It ignores empty lines, files that do not end in `.txt`, and IDs that are not listed in `classes.txt`. It does not use or validate the coordinate values. Folder names are expected to be exactly `train`, `validate`, and `test`.

## Troubleshooting

- **Database connection error:** confirm MySQL is running, the configured database exists, credentials are correct, and the `mysqli` extension is enabled.
- **Unknown table/column:** the selected PHP page may be using a different schema than the one loaded. Tasks 2 and 4 use separate schemas.
- **Task 3 reports a missing `classes.txt`:** ensure the ZIP contains a top-level dataset folder with `classes.txt` inside it, rather than placing the file at an unexpected nesting level.
- **Task 3 cannot upload or extract:** check that `uploads/` and `dataset/` are writable by PHP, the ZIP is valid, and `ZipArchive` is enabled. PHP upload-size settings may also limit large archives.
- **Task 4 login fails:** see the authentication inconsistency in [Task 4 database setup](#task-4). New company users are stored with hashed passwords, but login currently compares the plain-text password to the stored hash.
- **The root URL is blank:** open a task page path such as `/task1.php`; the repository's root `index.php` is empty.

## Security and limitations

This is an educational assignment, not a production-ready service. In particular:

- Replace embedded database credentials and rotate any credentials that were previously exposed.
- Task 3 accepts user-provided archives and extracts them server-side; do not expose this page publicly without adding upload-size/type controls, safe archive-path validation, and appropriate isolation.
- Use HTTPS, secure session settings, CSRF protections, authorization checks on every write action, and production-safe error handling before deployment.
- Review all SQL handling and output escaping, especially in Task 2, before using it with untrusted users or data.

## Duplicate files and supporting material

Files whose names end in `(1)` are duplicate copies of their corresponding files without that suffix. Use the unsuffixed PHP pages as the canonical files. The `.txt` files are source listings intended to be displayed in iframes; the Task 2/4 listings also have SQL appended, as noted above. The `.vpd` files and image files are assignment/diagram supporting material.
