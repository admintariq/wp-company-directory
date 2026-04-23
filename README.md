# WP Company Directory

A lightweight WordPress plugin that displays a searchable, filterable company/distributor directory powered by **Google Sheets** and **Google Apps Script** — no database required.

---

## Features

- 📋 Data lives in Google Sheets — easy to update without touching code
- ⚡ Fast loading with built-in 5-minute server-side caching
- 🔍 Live search box across all fields
- 🎛️ Three dropdown filters — Type, Region, Coverage Area (auto-update based on selection)
- 📊 Paginated DataTable (10 rows per page)
- 💬 Info popup showing full company details (contact, address, website, fax, mobile)
- 📱 Responsive layout for mobile screens
- 🔌 Drop-in via shortcode — works on any page or post

---

## Requirements

- WordPress 5.0 or higher
- A free Google account (for Google Sheets + Apps Script)
- The page must load jQuery (included by default in WordPress)

---

## Google Sheet Setup

Create a new Google Sheet and name the first tab **Sheet1**.

Set up your columns **in this exact order** (Row 1 is the header row):

| Col | Field         | Example                        |
|-----|---------------|--------------------------------|
| A   | Company       | Acme Distributors              |
| B   | Type          | Distributor                    |
| C   | Phone         | +1 555 000 1234                |
| D   | Email         | info@acme.com                  |
| E   | Region        | North, South                   |
| F   | Coverage Area | City A, City B, City C         |
| G   | Address       | 123 Main St, Springfield       |
| H   | Contact       | John Smith                     |
| I   | Fax           | +1 555 000 5678                |
| J   | Website       | www.acme.com                   |
| K   | Mobile        | +1 555 000 9999                |

> **Note:** Region and Coverage Area support comma-separated values (e.g. `North, South`). These are split automatically for filtering.

---

## Google Apps Script Setup

1. Open your Google Sheet
2. Click **Extensions → Apps Script**
3. Delete any existing code and paste the full contents of **`google-apps-script.gs`**
4. At the top of the script, update the `SPREADSHEET_ID` value with your own Sheet ID:

```javascript
var SPREADSHEET_ID = 'YOUR_SPREADSHEET_ID_HERE';
```

You can find your Spreadsheet ID in the URL:
```
https://docs.google.com/spreadsheets/d/THIS_IS_YOUR_ID/edit
```

5. Click **Deploy → New deployment**
6. Choose type: **Web App**
7. Set **Execute as**: Me
8. Set **Who has access**: Anyone
9. Click **Deploy** and **copy the deployment URL** — you will need it in the next step

> If you update the script later, you must create a **new deployment** (not edit the existing one) for changes to take effect.

---

## WordPress MU Plugin Setup

This is a **Must-Use (MU) plugin** — it is automatically active on every site in your WordPress installation without needing to be activated manually.

### Installation

1. Download `company-directory.php` from this repository
2. Upload it directly to `/wp-content/mu-plugins/`
3. That's it — no activation step needed. WordPress loads it automatically.

> **Note:** MU plugins do not appear in the regular **Plugins** list. You can verify it is loaded under **Plugins → Must-Use** in your WordPress admin.

### Configure the Apps Script URL

Open `company-directory.php` and replace the placeholder URL with your deployment URL from the previous step:

```php
define( 'CDIRECTORY_APPS_SCRIPT_URL', 'YOUR_APPS_SCRIPT_DEPLOYMENT_URL_HERE' );
```

---

## Usage

Add the shortcode to any WordPress page or post:

```
[company_directory]
```

That's it. The directory will load automatically with your Google Sheets data.

---

## How It Works

```
Google Sheet  →  Apps Script (Web App)  →  WordPress Plugin  →  Visitor's Browser
     ↑                  ↑                         ↑
  Your data         REST endpoint            Shortcode renders
                  (JSON output)             DataTable + filters
```

1. The visitor loads the page with `[company_directory]`
2. The plugin's JavaScript fetches data from your Apps Script URL
3. Apps Script reads your Google Sheet, caches the result for 5 minutes, and returns JSON
4. The plugin renders a DataTable with live search, three dropdown filters, and an info popup per row

---

## Caching

Apps Script caches the sheet data for **5 minutes** to reduce API calls and improve speed. If you update your sheet and need the changes to appear immediately, open the Apps Script editor and run the `invalidateCache()` function manually.

---

## File Structure

```
wp-company-directory/
├── company-directory.php   # WordPress plugin (shortcode + frontend)
├── google-apps-script.gs   # Google Apps Script (data endpoint)
└── README.md               # This file
```

---

## Customisation

### Change rows per page
In `company-directory.php`, find:
```javascript
pageLength: 10,
```
Change `10` to any number you prefer.

### Change accent colour
Search for `#704BD8` and `#2C1767` in the PHP file and replace with your own hex colours. These control buttons, filter focus states, and pagination hover effects.

### Add or hide columns
The table displays: **Company, Type, Phone, Email, Info**. To show or hide columns, edit the `<thead>` section and the `cdRenderTable` function in `company-directory.php`.

---

## Troubleshooting

| Problem | Solution |
|---|---|
| Spinner keeps loading | Check your Apps Script URL is correct and the Web App is deployed as "Anyone" access |
| Data not updating | Run `invalidateCache()` in Apps Script, or wait 5 minutes for cache to expire |
| Filters show no options | Make sure Region/Coverage columns have data and the sheet tab is named exactly `Sheet1` |
| Phone numbers show `=` prefix | The script strips leading `=` automatically — ensure numbers aren't stored as formulas |
| CORS error in browser console | Re-deploy the Apps Script as a new deployment and update the URL in the plugin |

---

## License

MIT License — free to use, modify, and distribute.

---

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.
