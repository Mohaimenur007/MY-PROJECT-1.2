<div align="center">

<img src="favicon.svg" alt="Lostly logo" width="84">

# Lostly

**The lost & found network for Bangladesh.**

Report what you lost or found, get matched automatically, and return items safely through claims and private messaging.

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-MariaDB-4479A1?logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?logo=javascript&logoColor=black)
![Responsive](https://img.shields.io/badge/Design-Responsive%20%C2%B7%20Dark%20mode-6B4A2E)

[Features](#features) · [Screenshots](#screenshots) · [Getting started](#getting-started) · [How it works](#how-it-works) · [Project structure](#project-structure)

<br>

<img src="docs/screenshots/home.png" alt="Lostly home page" width="100%">

</div>

---

## Table of contents

- [About the project](#about-the-project)
- [Features](#features)
- [Screenshots](#screenshots)
- [Built with](#built-with)
- [Getting started](#getting-started)
- [Configuration](#configuration)
- [How it works](#how-it-works)
- [Project structure](#project-structure)
- [Database](#database)
- [Security](#security)
- [Deployment](#deployment)
- [Roadmap](#roadmap)
- [Contributing](#contributing)
- [Team](#team)
- [License](#license)
- [Acknowledgements](#acknowledgements)

---

## About the project

When something goes missing in Bangladesh, the owner and the finder usually have no way to reach each other. Paper posters cover one street, and social media posts disappear within a day.

**Lostly** puts every lost and found report in one searchable place, organised by all **8 divisions and 64 districts**. Whenever a report is posted, Lostly compares it with reports of the opposite kind and alerts both people when two look like the same item. They can then verify ownership with a claim, talk privately without sharing phone numbers, and mark the report resolved when the item is back home.

---

## Features

### For members
- **Lost & found reports** with up to 4 photos, category, location, date and an optional cash reward.
- **Smart search** by keyword, type, division, category and status, with pagination. Typing a reference such as `LF-00012` jumps straight to that report.
- **Personal dashboard** with statistics, pending claims, possible matches, your reports and claims you sent.
- **Saved reports** (a watchlist) and **saved searches** that alert you when a matching report is posted.
- **Public profiles** with activity badges such as *Returned 2 items* and *Helpful tipster*.
- **Printable A4 poster** for any of your reports.

### Smart matching
- Every new report is scored from **0 to 100** against active reports of the opposite type.
- The score uses category, division, district, dates and shared words in the title and description.
- Both people are notified, and matches appear on the report page and the dashboard.

### Claims & communication
- **Proof-based claims:** the reporter approves or rejects each one. Approving resolves the report and automatically rejects the other claims.
- **Private messaging** per report, with live updates and unread counters.
- **Sightings & tips:** public comments on a report, with an alert to its owner.
- **Alerts centre** for matches, claims, decisions, tips and saved searches.
- **Reunited wall** of resolved reports with thank-you notes.

### Administration
- **Overview dashboard:** totals, reports per day for the last 7 days, lost-vs-found split, resolution rate, and breakdowns by category and division.
- **Moderation:** remove reports, review flagged posts, suspend or restore members, grant admin rights.
- **Contact inbox**, **activity log**, a **site-wide announcement banner** and **CSV export**.

### Design
- Built with Bootstrap 5, with custom report illustrations and line icons.
- **Three colour schemes** (Stone & Mocha, Mocha & Teal, Espresso & Gold) plus **dark mode**, remembered per visitor.
- Fully **responsive**, from phones to wide screens, with subtle scroll animations that respect *reduced motion*.
- Fonts (Poppins and Inter) are served locally: no external requests.

---

## Screenshots

| Home | Browse |
|:---:|:---:|
| <img src="docs/screenshots/home.png" alt="Home page" width="100%"> | <img src="docs/screenshots/browse.png" alt="Browse reports" width="100%"> |
| **Report page** | **Dashboard** |
| <img src="docs/screenshots/report.png" alt="Report page" width="100%"> | <img src="docs/screenshots/dashboard.png" alt="Dashboard" width="100%"> |
| **Private messaging** | **New report** |
| <img src="docs/screenshots/chat.png" alt="Private chat" width="100%"> | <img src="docs/screenshots/new-report.png" alt="Report form" width="100%"> |
| **Admin panel** | **Dark mode** |
| <img src="docs/screenshots/admin.png" alt="Admin panel" width="100%"> | <img src="docs/screenshots/home-dark.png" alt="Dark mode" width="100%"> |

<details>
<summary><b>Colour schemes and mobile</b></summary>
<br>

<img src="docs/screenshots/colour-schemes.png" alt="Stone & Mocha, Mocha & Teal and Espresso & Gold colour schemes" width="100%">

<p align="center"><img src="docs/screenshots/mobile.png" alt="Mobile layout" width="420"></p>

</details>

---

## Built with

| Layer | Technology |
|---|---|
| Back end | PHP 8 (no framework), sessions, prepared statements via `mysqli` |
| Database | MySQL 5.7+ / MariaDB 10.3+ |
| Front end | HTML5, CSS3 (custom design tokens), Bootstrap 5.3, vanilla JavaScript |
| Fonts & icons | Poppins, Inter, SVG line icons and illustrations |
| Local server | XAMPP / WAMP / MAMP or any Apache + PHP + MySQL host |

---

## Getting started

### Prerequisites

- **PHP 8.0+** with the `mysqli` extension (included in XAMPP)
- **MySQL** or **MariaDB**
- **Apache** (or any web server that runs PHP)

The easiest option is [XAMPP](https://www.apachefriends.org), which includes all three.

### Installation

1. **Clone the repository** into your web root (for XAMPP, the `htdocs` folder):

   ```bash
   cd C:/xampp/htdocs            # macOS: /Applications/XAMPP/htdocs
   git clone https://github.com/YOUR_USERNAME/lostly.git Lostly
   ```

2. **Start Apache and MySQL** from the XAMPP Control Panel.

3. **Create the database:**
   - Open `http://localhost/phpmyadmin`.
   - Create a database named **`lostly`** with collation `utf8mb4_general_ci`.
   - Select it, open **Import**, and import **`DataBase/lostly.sql`**.

4. **Check the connection settings** in [`db.php`](db.php). The XAMPP defaults are already filled in:

   ```php
   $db_host = "localhost";
   $db_user = "root";
   $db_password = "";
   $db_name = "lostly";
   ```

5. **Open the app:** <http://localhost/Lostly/>

### Demo accounts

The import includes sample reports, claims and messages, so every feature can be tried straight away.

| Role | Username | Password |
|---|---|---|
| Administrator | `admin` | `admin123` |
| Members | `rana`, `mitu`, `Didar`, `sumaiya`, `tanvir` | `lostly123` |

> [!IMPORTANT]
> Before publishing the site, set `demo_mode` to `false` (this hides the demo login buttons) and change the admin password.

---

## Configuration

App settings live in `config()` inside [`includes/helpers.php`](includes/helpers.php):

| Setting | Default | Description |
|---|---|---|
| `per_page` | `9` | Reports per page on Browse |
| `match_threshold` | `55` | Minimum score (0–100) for two reports to count as a match |
| `max_photos` | `4` | Photos allowed per report |
| `max_upload_bytes` | `5 MB` | Maximum size of each photo |
| `max_search_alerts` | `10` | Saved searches per member |
| `demo_mode` | `true` | Shows the one-click demo accounts on the login page |

Other settings:

- **Categories, divisions and districts:** [`lists.php`](lists.php)
- **Colours for all schemes, light and dark:** [`css/theme.css`](css/theme.css)

---

## How it works

### Request flow

```
Browser ──► page.php ──► includes/init.php ──► db.php · lists.php · helpers · models
                │                                   (session, current user, CSRF)
                ├─ reads data through ItemModel.php / includes/models.php
                └─ renders includes/header.php ─ page HTML ─ includes/footer.php
```

- Every page is a plain PHP file that starts with `require "includes/init.php";`.
- Form actions on a report (claim, approve, tip, flag, save, resolve and so on) are handled by `ItemController.php`.
- Every action is a `POST` request with a CSRF token.

### Smart matching

`Item::score()` in [`ItemModel.php`](ItemModel.php) compares a lost report with a found report:

| Signal | Points |
|---|---:|
| Same category | 35 |
| Same division | 15 |
| Same district (within that division) | 15 |
| Found 0–3 days after the loss (4–14 days: 10, 15–45 days: 4) | up to 15 |
| Shared meaningful words in the titles | 20 |
| Shared words across titles and descriptions | up to 10 |
| Found more than 2 days *before* it was lost | −15 |

Scores at or above `match_threshold` (55) are shown as possible matches, and both people get an alert.

---

## Project structure

```
Lostly/
├── index.php              # Home page
├── browse.php             # Search, filters, pagination, saved-search button
├── item_detail.php        # Report page: gallery, claims, tips, matches, sharing
├── report_item.php        # Create / edit a report (photo uploads)
├── delete_item.php        # Delete your own report
├── ItemController.php     # Report actions (claim, decide, tip, flag, save, resolve…)
├── ItemModel.php          # Report queries, photos, statistics, matching score
├── dashboard.php          # Member dashboard
├── edit_profile.php       # Account settings and password
├── login.php · registration.php · logout.php
├── messages.php           # Inbox, chat and live-update endpoint
├── alerts.php             # Alerts and saved searches
├── saved.php · member.php · reunited.php · poster.php
├── admin.php              # Admin panel and CSV export
├── page.php               # About, FAQ, Safety, Terms, Privacy
├── contact.php            # Contact form → admin inbox
├── db.php                 # Database connection + Database helper class
├── lists.php              # Categories, divisions, districts, colour schemes
├── includes/
│   ├── init.php           # Bootstrapping: session, security headers, current user
│   ├── header.php         # <head>, navigation, toasts
│   ├── footer.php         # Footer, confirm dialog, scripts
│   ├── helpers.php        # config(), e(), url(), CSRF, formatting
│   ├── models.php         # User, Claim, Message, Notification, Flag, Upload…
│   ├── components.php     # Report card, stat tiles, banners, badges
│   └── icons.php          # SVG icons and item illustrations
├── css/                   # bootstrap · fonts · theme · base · components · extras
├── js/
│   ├── main.js            # Validation, dark mode, colour schemes, chat, animations
│   └── vendor/            # Bootstrap bundle
├── uploads/items/         # Uploaded photos (scripts blocked by .htaccess)
├── DataBase/lostly.sql    # Schema + demo data
└── docs/screenshots/      # Images used in this README
```

---

## Database

| Table | Purpose |
|---|---|
| `users` | Accounts, contact details, role, suspension and privacy setting |
| `items` | Lost and found reports (cover photo, status, views, reward, resolution note) |
| `item_images` | Additional photos for a report |
| `claims` | Ownership claims with *Pending / Approved / Rejected* status |
| `messages` | Private messages between two members about one report |
| `comments` | Public sightings and tips |
| `notifications` | Alerts for matches, claims, tips, saved searches and admin actions |
| `saved_items` | Members' watchlists |
| `search_alerts` | Saved searches |
| `flags` | Posts reported to the admins |
| `contact_messages` | Contact-form inbox |
| `admin_log` | Audit trail of admin actions |
| `settings` | Site settings such as the announcement banner |

Foreign keys use `ON DELETE CASCADE`. Deleting a report also removes its photos from disk.

---

## Security

- **SQL injection:** every query is a prepared statement with bound parameters.
- **XSS:** all user content is escaped with `htmlspecialchars` before output. Chat updates are inserted as plain text.
- **CSRF:** every state-changing form carries a per-session token, checked with `hash_equals`.
- **Passwords:** hashed with `password_hash()` (bcrypt) and verified with `password_verify()`.
- **Sessions:** `HttpOnly` and `SameSite=Lax` cookies, strict mode, and the session ID is regenerated on login. Suspended members are signed out immediately.
- **Brute force:** after 5 failed logins, the form is locked for 60 seconds.
- **Uploads:**
  - Each file is validated by its real image type (JPG, PNG or WEBP) and capped at 5 MB.
  - It is saved under a random name.
  - Script execution is blocked in `uploads/`.
- **Access control:**
  - Only owners can edit, delete, resolve or print their reports.
  - Conversations are limited to the two people involved.
  - Admin pages require the admin role.
- **Headers:** `X-Content-Type-Options`, `X-Frame-Options` and `Referrer-Policy`. On Apache, `includes/` and `DataBase/` cannot be opened from the browser.

---

## Deployment

Lostly runs on any shared host with PHP 8 and MySQL (for example InfinityFree, 000webhost or cPanel hosting):

1. Create a MySQL database in the hosting panel and import `DataBase/lostly.sql` with phpMyAdmin.
2. Enter the host's database details in `db.php`.
3. Upload the project files to `htdocs` / `public_html`.
4. Make sure `uploads/items/` is writable.
5. Set `demo_mode` to `false` and change the admin password.

---

## Roadmap

- [ ] Email notifications for matches and claims
- [ ] Bangla (বাংলা) interface
- [ ] Map view of reports by district
- [ ] Image-based similarity in the matching score
- [ ] Password reset by email
- [ ] REST API for a mobile app

---

## Contributing

Contributions, issues and feature requests are welcome.

1. Fork the project.
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m "Add amazing feature"`
4. Push to the branch: `git push origin feature/amazing-feature`
5. Open a pull request.

---

## Team

Lostly was built by **Group 6**.

| Name | Role | GitHub |
|---|---|---|
| YOUR_NAME | Full-stack development | [@YOUR_USERNAME](https://github.com/YOUR_USERNAME) |
| TEAM_MEMBER | — | [@USERNAME](https://github.com/USERNAME) |
| TEAM_MEMBER | — | [@USERNAME](https://github.com/USERNAME) |

---

## License

No license has been chosen yet. Add a `LICENSE` file to state how others may use this code. [MIT](https://choosealicense.com/licenses/mit/) is a common choice for open-source student projects.

---

## Acknowledgements

- [Bootstrap](https://getbootstrap.com) (MIT License)
- [Poppins](https://fonts.google.com/specimen/Poppins) and [Inter](https://rsms.me/inter/) fonts (SIL Open Font License)
- Line icons based on [Lucide](https://lucide.dev) (ISC License)
- Badges by [Shields.io](https://shields.io)

<div align="center">
<br>
<sub>Made with care in Bangladesh · © 2026 Lostly · Group 6</sub>
</div>
