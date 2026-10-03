# MY-PROJECT-1.2
# Lostly — Lost & Found for Bangladesh (Group 6)

PHP + MySQL (XAMPP), built on a small custom **MVC framework** with a
**Bootstrap 5** front end. No Composer, no build step: copy the folder and run.

## Setup (XAMPP)

1. Copy the `Lostly` folder into `htdocs`.
2. Start Apache and MySQL.
3. Database — pick ONE:
   - **Fresh install:** in phpMyAdmin open *Import* and choose `DataBase/lostly.sql`.
     (It creates the `lostly` database and replaces existing Lostly tables.)
   - **Keep your existing data:** select your `lostly` database, then import `DataBase/upgrade.sql`.
4. Open `http://localhost/Lostly/`.

Database settings are in `app/config.php`.

| Admin username | Password |
| --- | --- |
| `admin` | `admin123` (change it from Edit profile after the first login) |

Accounts created before passwords were hashed still log in; the password is
converted to a secure hash on first login.

## The framework (MVC)

Every request goes through one file, `index.php` (the front controller):

```
browser ──> index.php ──> Router ──> Controller ──> Model ──> database
                                         │
                                         └──> View (inside the shared layout) ──> browser
```

Addresses look like `http://localhost/Lostly/index.php/items/7`.
`app/routes.php` lists every address and the controller method behind it:

```php
$router->get("items/{id}", "ItemController@show");
$router->post("items/{id}/claims", "ClaimController@store");
```

```
index.php                 front controller
app/
  config.php              database, categories, divisions, settings
  bootstrap.php           autoloader, session, security checks
  routes.php              address  ->  Controller@method
  core/                   the framework
    Router.php            matches the address to a route
    Request.php           reads the address and form input
    Controller.php        base controller (view, redirects, login guards)
    View.php              renders a view inside layouts/main.php
    Database.php          prepared-statement wrapper around mysqli
    Auth.php  Csrf.php    login state, CSRF tokens
    helpers.php           e(), url(), asset(), flash() ...
  controllers/            one class per area (Auth, Item, Claim, Message, Admin ...)
  models/                 all SQL lives here (User, Item, Claim, Message, Notification, Flag)
  views/                  HTML only (layouts/, partials/, items/, admin/ ...)
css/style.css             Lostly theme on top of Bootstrap
js/main.js                page behaviour on top of Bootstrap's JS
vendor/bootstrap/         Bootstrap JS bundle + offline CSS fallback
uploads/items/            item photos
DataBase/                 lostly.sql (fresh) and upgrade.sql (existing data)
```

To add a page: add a line in `routes.php`, a method in a controller, and a view file.

## Bootstrap 5

Bootstrap provides the grid (`row` / `col-*`), navbar with collapse and dropdown,
form controls, buttons, badges, pagination, nav pills, progress bars, collapse,
toasts (flash messages) and the modal used for every "are you sure?" dialog.
`css/style.css` re-skins those components through Bootstrap's own CSS variables
to give the "notice board" look, in both light and dark (`data-bs-theme`).

Bootstrap's CSS loads from the jsDelivr CDN. With no internet connection the page
automatically falls back to the copy in `vendor/bootstrap/` (a Bootswatch build of
Bootstrap 5.3.2), so the site still works offline. Bootstrap's JavaScript is always
served locally.

## Animation

All animation is plain CSS keyframes plus one small script (`js/main.js`, "scroll animations"):

- **Scroll reveal** — panels, tiles and cards rise in the first time they scroll into view, staggered one after another
- **Notice cards** drop onto the board, then the tape sticks and the LOST / FOUND stamp thumps down
- **Numbers count up** and **chart bars grow** when they come into view
- **Landing page** — headline labels slap on in sequence, posters sway, and a ticker scrolls the newest notices (pauses on hover)
- **Small touches** — tape flaps and photos zoom on card hover, unread badges pulse, chat bubbles pop in, a failed form shakes, the confirm dialog springs open, colours fade when the theme changes

It is decoration only: with JavaScript off nothing is hidden, and with the system
"reduce motion" setting on, every animation is switched off.

## Features

**Open to everyone (no account needed)**

| Feature | Where |
| --- | --- |
| Home page: search, category tiles with live counts, divisions, newest reports, FAQ | `HomeController` |
| Browse and search with filters, sorting and pagination | `ItemController@index` |
| Report pages with photo gallery, reference number (LF-00042), share buttons | `ItemController@show` |
| Reunited wall with thank-you notes | `PageController@reunited` |
| Public member profiles with activity badges | `MemberController` |
| About, Safety tips, Questions & answers, Terms, Privacy | `PageController`, `views/pages/` |
| Contact form (rate limited, spam trap) | `PageController@contactSend` |

**For members**

| Feature | Where |
| --- | --- |
| Report lost / found items with up to 4 photos and an optional reward | `ItemController`, `Upload` |
| District suggestions for all 64 districts, following the chosen division | `config.php`, `partials/district_list.php` |
| Smart matches between lost and found reports | `Item::matches()`, `Item::score()` |
| Saved searches: get an alert when a matching report is posted | `SearchAlert`, `NotificationController@subscribe` |
| Saved reports (watchlist), with an alert when one is resolved | `Saved`, `ItemController@save` |
| Claims with proof; owner approves or rejects | `ClaimController` |
| Sightings & tips: public comments on a report | `CommentController` |
| Private messaging per item, live updates, unread badges | `MessageController` |
| Printable A4 poster of your own report | `ItemController@poster`, `views/items/poster.php` |
| Resolve with a thank-you note; reopen later | `ItemController@resolve` |
| Privacy switch: hide phone and email, be reached by message only | `ProfileController` |
| After logging in you return to the page you were trying to open | `Controller::requireLogin()` |

**For admins**

| Feature | Where |
| --- | --- |
| Overview charts, remove reports, suspend members, manage admins, flagged posts | `AdminController` |
| Inbox for Contact-form messages | `ContactMessage` |
| Activity log of every admin action | `AdminLog` |
| Site-wide announcement banner | `Setting` |
| Export all reports to CSV (safe to open in Excel) | `AdminController@export` |

## Security

- CSRF token on every form; every POST is checked in one place (`Csrf::verify()`)
- Delete and logout are POST-only
- All SQL uses prepared statements (`Database`); all output is escaped with `e()`
- Route placeholders such as `{id}` only accept numbers
- Uploads are checked by real file content, renamed randomly, and scripts cannot run in `uploads/`
- `app/` and `DataBase/` are blocked from direct browser access (`.htaccess`)
- Changing a password needs the current password; login slows down after 5 wrong tries
- Suspended accounts are logged out immediately and their reports hidden
- Phone numbers and emails are never shown to visitors without an account

## How smart matching scores (0–100)

| Signal | Points |
| --- | --- |
| Same category | 35 |
| Same division | 15 |
| Same district (within the division) | 15 |
| Found 0–3 days after lost / 4–14 days / 15–45 days | 15 / 10 / 4 |
| Found more than 2 days *before* it was lost | −15 |
| Item names share a word | 20 |
| Other shared words | up to 10 |

Reports scoring 55 or more (`match_threshold` in `app/config.php`) are shown as possible matches.

## Not included

- **Email**: there is no mail server in XAMPP, so there is no email verification, password reset by email or email alerts. Alerts appear inside the site.
- **Terms and Privacy pages** describe what the software does; have them reviewed before running Lostly for the public.

## Notes

- `upgrade.sql` uses `ADD COLUMN IF NOT EXISTS`, which needs MariaDB (the database XAMPP ships).
- Fonts load from Google Fonts; offline, the site falls back to system fonts.
