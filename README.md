# 🧩 WordPress Update Monitoring with Zabbix

This project provides a lightweight and reliable way to monitor **WordPress update status** with **Zabbix** using a custom REST API endpoint and a Zabbix HTTP Agent item.

The solution detects and differentiates between:

- 🧠 WordPress **core updates** (WordPress version updates)
- 🔌 **Plugin updates**
- 🎨 **Theme updates**
- 🗄️ Required **database updates**
- 🌍 **Translation updates**

No scripts are executed on the Zabbix server or the WordPress host.  
Zabbix queries WordPress exclusively via REST API.

---

## 🏗️ Architecture Overview

```
Zabbix Server
   |
   |  HTTP GET (every 15 minutes)
   |  Basic Auth (Application Password)
   v
WordPress REST API (MU Plugin)
   |
   |  JSON response
   v
Zabbix Items (RAW + Dependent)
   |
   v
Zabbix Triggers (created in UI)
```

---

## 📦 Requirements

### WordPress
- WordPress 6.x
- PHP 8.x recommended
- Ability to install **MU Plugins**
- A WordPress user with **Application Password** support

### Zabbix
- Zabbix **7.4**
- HTTP Agent items enabled
- Network access from the Zabbix server to the WordPress site

---

## 🧩 WordPress Setup

### 1. Install the MU Plugin

Copy the file:

```
./mu-plugins/zabbix-update-monitor.php
```

to 

```
/wp-content/mu-plugins/zabbix-update-monitor.php
```

MU plugins are loaded automatically and cannot be disabled via the WordPress admin UI.

---

### 2. 🔐 Application Password

1. Create a dedicated WordPress user (e.g. `zabbix`)
2. Generate an **Application Password** for this user and remove every blank space in the app password. 
3. No special roles are required beyond being an authenticated user

---

### 3. ⚠️ Important: Disable Debug Output

The REST endpoint **must return pure JSON**.

In `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

If `WP_DEBUG_DISPLAY` is enabled, PHP notices or warnings may be printed before the JSON output and will break JSON parsing in Zabbix.

---

## 🔗 REST API Endpoint

The MU plugin exposes the following endpoint:

```
GET /wp-json/zabbix/v1/updates
```

Example response:

```json
{
  "core_updates": 0,
  "plugin_updates": 1,
  "theme_updates": 0,
  "db_update_required": 0,
  "translation_updates": 0
}
```

### 🔑 Authentication

- HTTP Basic Authentication
- Username: WordPress user
- Password: WordPress Application Password

---

## 📊 Zabbix Setup

### 1. Template

Import the provided **Zabbix template** from there ./template/template-wordpress-update-monitoring.yaml.

The template contains:
- 1 HTTP Agent item returning the raw JSON payload
- 5 dependent items:
  - WordPress core updates
  - Plugin updates
  - Theme updates
  - Database update required
  - Translation updates

The HTTP item polls every **15 minutes**.

---

### 2. 🧷 Host Macro

On the Zabbix host, define the following macro:

```
{$WP_AUTH_URI}
```

Example:

```
https://zabbix:ksC75Thtdfx52PNCVgJ4XFwP@example.com
```

Notes:
- Use an **Application Password**, not a real login password
- URL-encode the password if it contains special characters
- The credentials are only used for this read-only endpoint

---

## 🔒 Security Considerations

- The REST endpoint is **read-only**
- Uses WordPress Application Passwords
- No sensitive data is exposed
- No write operations are possible
- Credentials are stored as Zabbix macros, not hard-coded

---

## ✅ Result

This setup provides:

- 👀 Clear visibility into WordPress update status
- 🧩 Clean separation of update types
- 🚨 Reliable alerting in Zabbix
- 🧼 No cron jobs, no scripts, no agents on WordPress

---

## 📄 License

MIT License

---

## ✍️ Author

Designed for real-world production use with  
**WordPress + Zabbix 7.4 + Cloudflare-compatible setups**.
