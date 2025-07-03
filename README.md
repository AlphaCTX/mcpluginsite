# Minecraft Plugin Site

This project is a simple PHP/MySQL web application that allows an administrator to upload Minecraft plugins and provides a public download page.

## Features

- **Admin login** with a fixed username and password defined in `config.php`.
- **Plugin management** with rich text descriptions. Plugins have a logo, short description and long description. Existing plugins can receive multiple versions with Minecraft 1.16–1.21 support and changelogs.
- **Update posts** section with rich text editing to publish news that appears on the home page.
- **Public plugin listing** with search capability and download tracking.
- **Download statistics** chart on the admin dashboard.
- **Dynamic admin** interface that refreshes automatically after changes.
- **Error reporting** in the admin area for easier debugging.

## Files

- `index.php` – public landing page showing featured plugins, the latest update and plugin search.
- `plugin.php` – individual plugin page with description and download tabs.
- `functions.php` – helpers for site configuration.
- `admin.php` – administration dashboard for managing plugins and updates.
- `upload.php` – AJAX endpoint for plugin uploads.
  Supports files up to 20MB.
- `download.php` – serves jar files and records downloads.
- `stats.php` – returns JSON statistics for the chart.
- `db.php` – database connection helper.
- `.htaccess` – simple configuration to ensure PHP files are executed.
- `schema.sql` – SQL script to create the required tables.
- `config.sample.php` – example configuration file.

## Database

Run the queries in `schema.sql` to create the database schema. It contains the tables `plugins`, `plugin_versions`, `downloads`, `updates` and `settings`.

## Configuration

Copy `config.sample.php` to `config.php` and adjust the database credentials and admin login details.
Use the **Site config** section in the admin dashboard to change the site title and upload a logo, favicon or banner image. Featured plugins can be chosen from the existing list.

## Notes

This project is intentionally minimal and meant as a starting point. Feel free to extend it with additional features such as version management, site configuration options or a dedicated plugin page as described in the comments within the code.

### Site layout
- **Top bar** with links to Home, Plugins and Updates
 - **Banner** image with a slideshow of three featured plugins
- **Latest update** section showing the newest news post
- **Recently updated** list of the five newest plugin versions
- **Footer** with site title and year
- **Admin dashboard** with a light panel for creating plugins and managing their versions
- Plugin and update pages use the same light panel styling
- Plugin lists show each logo next to the name
- Statistics page can filter by plugin, version and Minecraft version
- Admin login screen shows the site logo
