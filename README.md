# Lostly — Lost & Found for Bangladesh (PHP + MySQL)

Lostly is a lost and found website. People report what they lost or found. Lostly matches lost reports with found ones automatically, and the two people can claim the item and chat privately. It has the new design, with three colour schemes and a dark mode.

Built with **PHP 8**, **MySQL / MariaDB**, **Bootstrap 5** and plain **JavaScript**.

---

## Files

The same layout as the original project: one PHP file per page, plus `css/`, `js/` and `uploads/`.

| File | What it does |
|---|---|
| `index.php` | Home page: hero, live match card, stats, categories, newest reports, how it works, reunited stories, FAQ |
| `browse.php` | Search and filter every report, 9 per page; save a search to get alerts |
| `item_detail.php` | One report: photos, details, claims, tips, private message, possible matches, share |
| `report_item.php` | Report a lost or found item (up to 4 photos); `?id=5` edits an existing report |
| `delete_item.php` | Deletes your own report with its photos |
| `ItemController.php` | Report actions: claim, approve/reject, tip, flag, save, resolve, reopen, saved searches |
| `ItemModel.php` | All report database work: search, photos, statistics and the matching score |
| `dashboard.php` | Your stats, claims waiting for you, matches, your reports and claims |
| `edit_profile.php` | Account settings: contact details, privacy switch, password |
| `login.php` | Log in (5 wrong tries lock the form for a minute) |
| `registration.php` | Create an account |
| `logout.php` | Log out |
| `db.php` | **Database connection settings** and a small safe-query helper |
| `lists.php` | Categories, divisions, all 64 districts and the colour schemes |
| `messages.php` | Inbox and private chat (updates live every few seconds) |
| `alerts.php` | Alerts (matches, claims, tips) and saved searches |
| `saved.php` | Reports you saved |
| `member.php` | A member's public profile with badges |
| `reunited.php` | The wall of items that went back home |
| `poster.php` | Printable A4 poster of your report |
| `admin.php` | Admin panel: charts, reports, members, flags, inbox, activity log, announcement, CSV export |
| `page.php` | About, FAQ, Safety, Terms and Privacy (`page.php?p=faq`) |
| `contact.php` | Contact form (goes to the admin inbox) |
| `includes/` | Shared pieces: `init.php` (start-up and login), `header.php`, `footer.php`, `helpers.php`, `models.php`, `components.php`, `icons.php` |
| `css/` | `bootstrap.min.css`, `fonts.css`, `theme.css` (all colours), `base.css`, `components.css`, `extras.css` |
| `js/` | `main.js` (forms, dark mode, colour schemes, chat, animations) and `vendor/bootstrap.bundle.min.js` |
| `uploads/items/` | Report photos are saved here |
| `DataBase/lostly.sql` | Creates all tables and the demo data |

---

## Step by step: run it on your computer (XAMPP)

### Step 1 — Install XAMPP

Download XAMPP from apachefriends.org and install it. Open the **XAMPP Control Panel** and press **Start** for **Apache** and **MySQL**.

### Step 2 — Copy the project

Unzip and copy the whole **`Lostly`** folder into XAMPP's `htdocs` folder:

- Windows: `C:\xampp\htdocs\Lostly`
- macOS: `/Applications/XAMPP/htdocs/Lostly` (or `~/.bitnami/.../htdocs` on the VM version)

If you already have an old `Lostly` folder there, rename it first (for example `Lostly-old`) so old files don't mix with the new ones.

### Step 3 — Create the database

1. Open `http://localhost/phpmyadmin`.
2. Click **New**, type the name **`lostly`**, choose **utf8mb4_general_ci**, and press **Create**.
3. Click the new `lostly` database, open the **Import** tab, choose **`DataBase/lostly.sql`**, and press **Import** (or **Go**).

The import creates the tables and the demo data. If you had an old `lostly` database, it replaces the Lostly tables in it.

### Step 4 — Check the connection settings

Open **`db.php`**. XAMPP's default is user `root` with an empty password, which is already set. Change these lines only if your MySQL is different:

```php
$db_host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "lostly";
```

### Step 5 — Open the site

Go to **`http://localhost/Lostly/`**.

### Step 6 — Log in with a demo account

The login page has one-click demo buttons:

| Username | Password | Role |
|---|---|---|
| admin | admin123 | Administrator |
| rana, mitu, Didar, sumaiya, tanvir | lostly123 | Members |

### Step 7 — Try the main flow

1. Log in as **rana**. The dashboard shows a claim from Didar and a 100% match.
2. Click **Review**, then **Approve**. The report becomes *Resolved*, Didar gets an alert, and the item appears on the **Reunited** wall.
3. Click **+ Report** and post a *found* item, for example "Grey cat with blue collar" in Pets, Dhaka, Mirpur, with a photo. Lostly matches it with the lost cat and alerts both people.
4. On any report try **Send a private message**, **Save**, **Sightings & tips** and **Print poster**.
5. Log in as **admin** and open **Admin** to see the charts, suspend a member, read the inbox, post an announcement or download the CSV.
6. Use the **palette** button in the top menu to switch colour schemes, and the **moon** button for dark mode.

---

## How the code works

1. **Every page starts with** `require "includes/init.php";`. It starts the session, connects to the database (`db.php`), loads the lists, helpers and models, and finds out who is logged in.
2. **Pages that need an account** call `require_login();`. Admin pages call `require_admin();`.
3. **Every form** sends a secret token (`<?= csrf_field() ?>`) and the page checks it with `check_csrf();`. This blocks forged requests from other websites.
4. **Every database query** is a prepared statement through the `Database` class (`Database::all`, `one`, `value`, `insert`, `run`), so user input can never change the SQL.
5. **Everything shown on screen** goes through `e()`, which escapes HTML, so nobody can inject scripts.
6. **Passwords** are stored with `password_hash()` and checked with `password_verify()`.
7. **Photos** are checked by their real contents (JPG, PNG or WEBP, max 5 MB) and saved with random names. `uploads/.htaccess` stops scripts from running in that folder.
8. **Matching** (`Item::score` in `ItemModel.php`) gives two reports up to 100 points:
   - same category 35
   - same division 15
   - same district 15
   - sensible dates up to 15
   - shared words up to 30

   A score of 55 or more counts as a match.
9. **Design:** `header.php` and `footer.php` wrap every page. All colours are CSS variables in `css/theme.css`. `js/main.js` adds form checks, the confirm dialog, the live chat and the animations.

---

## Customise

- **Turn off the demo buttons** before going live: set `"demo_mode" => false` in `includes/helpers.php` (inside `config()`).
- **Add a category:** add it to `$categories` in `lists.php`. Optionally give it an icon in `CATEGORY_ICONS` and a drawing in `ILLUSTRATIONS` in `includes/icons.php`; otherwise it uses the parcel drawing.
- **Change colours:** edit the variables in `css/theme.css` (`--primary` for buttons, `--accent` for highlights, `--lost`, `--found`).
- **Matching strictness:** change `"match_threshold"` in `config()`.
- **Reports per page and photo limit:** `"per_page"` and `"max_photos"` in `config()`.

---

## Put it online (free hosting)

1. Create an account on a PHP host with MySQL, for example InfinityFree or 000webhost.
2. In the hosting panel, create a MySQL database. Note its host, user, password and name.
3. Open phpMyAdmin from the panel and import `DataBase/lostly.sql`.
4. Put those four values into `db.php`.
5. Upload everything inside the `Lostly` folder to `htdocs` (or `public_html`) with the panel's File Manager or FileZilla.
6. Set `demo_mode` to `false` and change the admin password (log in as admin → Account settings).

---

## Troubleshooting

- **"Could not connect to the database"**: MySQL is not started in XAMPP, or the values in `db.php` are wrong, or the `lostly` database doesn't exist yet (Step 3).
- **Blank page or error after import**: make sure the import finished without red errors and that you imported `DataBase/lostly.sql` into the `lostly` database.
- **Photos don't upload**: check that the `uploads/items` folder exists and is writable (on macOS, right-click → Get Info → Sharing & Permissions → everyone: Read & Write).
- **"This form has expired"**: the page was open for a long time. Refresh and try again.

© Lostly · Group 6
