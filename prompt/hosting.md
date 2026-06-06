# Hosting Environment Specification

## System Architecture & Software Versions

- **Operating System:** Linux (`4.18.0-553.37.1.lve.el8.x86_64`)
- **Architecture:** x86_64
- **Web Server:** Apache 2.4.67
- **Database Engine:** MariaDB 10.11.18-log
- **Control Panel:** cPanel 134.0 (build 35)
- **Server Name / Shared IP:** host139 / `103.200.23.139`
- **Primary Domain:** `caothangcntt.site`

## Resource Allocations & Limits

- **Disk Space:** 8 GB
- **Physical Memory (RAM):** 2 GB
- **Concurrent Entry Processes:** Max 20
- **Total Processes:** Max 100
- **I/O Throughput Limit:** 20 MB/s
- **IOPS Limit:** 2,048
- **Databases / Subdomains / Bandwidth:** Unlimited

---

## Deployment & Hosting Pipeline (Detailed)

### Step 1: Database Provisioning & Migration

1. **Access Control Panel:** Log in to cPanel.
2. **Create Database:** Navigate to **MySQL Database Wizard** or **MySQL Databases**, create a new database, and note the database name (`user_dbname`).
3. **User Management:** Create a new MySQL user with a secure password.
4. **Privilege Assignment:** Link the user to the database and grant **ALL PRIVILEGES**.
5. **Schema Migration:** \* **Option A (UI):** Open **phpMyAdmin**, select the created database, and use the **Import** tab to upload the `.sql` schema file.
   - **Option B (CLI):** Execute migrations via terminal using the custom `ctsdk` migration command.

### Step 2: Application Source Code Deployment

1. **Package Source:** Compress the local application source code (excluding the `public` folder if separation is required, or full project depending on framework architecture) into a `.zip` archive.
2. **Upload Core Files:** Open cPanel **File Manager**, navigate to the root directory `/home/[user]/`, upload the `.zip` archive, and extract it here.
3. **Expose Public Assets:** Move the complete contents of the application's `public` folder into the `/home/[user]/public_html/` directory.
4. **Symlink Generation** Execute the following PHP command via terminal to link the target storage directory with the public-facing directory:

```bash
php ctsdk.php storage-link storage-path public-path
```

### Step 3: Environment Configuration & Entrypoint Alignment

1. **Environment Variables:** Inside `/home/[user]/`, configure the target environment variables by modifying `.env.staging` or `.env.production` with production credentials (DB name, DB user, DB password).
2. **Bootstrap Adjustment:** Edit the main entrypoint file `/home/[user]/public_html/index.php`. Modify the `AppLoader` configuration block to load the correct `.env` environment file paths matching the directory structure.
3. **Verification:** Launch a browser and verify application availability via `http://caothangcntt.site`.
