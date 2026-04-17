# AFCA Pro Hit Tracker

A lightweight WordPress plugin that tracks unique daily hits per post or page via the REST API, with built-in spam and bot protection.

- **Author:** André Amorim — [andreamorim.site](https://andreamorim.site)
- **Version:** 1.1
- **Requires WordPress:** 6.0+
- **Requires PHP:** 8.1+

---

## Features

- **REST API–based tracking** — hits are recorded asynchronously via a secure `POST /wp-json/pht/v1/track` endpoint; no page-reload required.
- **Unique daily hits** — each IP address can only register one hit per post per calendar day, preventing repeat inflation.
- **Bot & spam protection** — common crawlers, scrapers and headless clients are detected by User-Agent and silently ignored.
- **Rate limiting** — each IP is limited to 60 requests per hour per endpoint/post combination; excess requests return `429`.
- **Origin validation** — the tracking endpoint rejects requests whose `Origin` or `Referer` header does not match the site's own domain.
- **Honeypot field** — a hidden `hp` parameter in the request body silently discards bot-submitted forms.
- **Session-level deduplication** — the frontend JS stores a `sessionStorage` flag after a successful track, so navigating back to the same post in the same tab never fires a second request.
- **Nonce-secured endpoint** — the tracker fetches a fresh `wp_rest` nonce from `GET /wp-json/pht/v1/nonce` before every tracking call; invalid nonces are rejected with `403`.
- **Cloudflare-aware IP detection** — real visitor IPs are read from `HTTP_CF_CONNECTING_IP` when present, so rate limits work correctly behind Cloudflare.
- **Dashboard widget** — an "Top Posts by Hits" widget appears on the WordPress admin dashboard, listing the five most-viewed pieces of content.
- **Admin list columns** — a sortable "Hits" column is added to the post/page list tables for all tracked post types.
- **Configurable post types** — track any combination of public post types from the settings page; defaults to `post` and `page`.
- **Data reset** — the settings page includes a "Reset All Hits" action that wipes all `post_hits` meta rows and rate-limit transients.
- **Self-hosted auto-updates** — the plugin checks for new versions daily against the author's own update hub and integrates with the standard WordPress update flow.

---

## Installation

1. Upload the `afca-pro-hit-tracker` folder to `/wp-content/plugins/`.
2. Activate the plugin through **Plugins → Installed Plugins**.
3. Go to **Settings → Pro Hit Tracker** to configure which post types to track.

> **Note:** The `vendor/` directory (Composer autoloader) must be present. If you are installing from source, run `composer install` first.

---

## How It Works

```
Browser loads singular post/page
  └─ tracker.js is enqueued (only on singular views for tracked post types)
       └─ Fetches GET /wp-json/pht/v1/nonce  →  receives { nonce }
            └─ POSTs to /wp-json/pht/v1/track with X-WP-Nonce header
                 ├─ SpamGuard checks: bot UA, rate limit, origin/referer
                 ├─ HitRecorder checks: already counted today? (transient)
                 └─ Records hit:
                      ├─ post_hits            (post meta — all-time total)
                      └─ post_hits_daily_YYYY-MM-DD  (post meta — daily bucket)
```

Hit data is stored entirely in standard WordPress post meta — no custom database tables are created.

---

## REST API Endpoints

### `GET /wp-json/pht/v1/nonce`

Returns a short-lived `wp_rest` nonce for use in the tracking request.

**Response:**
```json
{ "nonce": "abc123def4" }
```

> LiteSpeed Cache no-cache headers are set automatically on this endpoint.

---

### `POST /wp-json/pht/v1/track`

Records a hit for a given post.

**Headers:**

| Header | Value |
|--------|-------|
| `Content-Type` | `application/json` |
| `X-WP-Nonce` | Nonce obtained from the `/nonce` endpoint |

**Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `post_id` | integer | Yes | The ID of the post/page to track |
| `hp` | string | No | Honeypot field — must be empty; non-empty values are silently discarded |

**Responses:**

| Status | Meaning |
|--------|---------|
| `200 { "status": "counted" }` | Hit recorded successfully |
| `200 { "status": "already counted" }` | This IP already counted today |
| `200 { "status": "bot ignored" }` | Request detected as a bot |
| `200 { "status": "ok" }` | Honeypot triggered |
| `403 { "error": "invalid nonce" }` | Nonce missing or invalid |
| `403 { "error": "forbidden" }` | Origin/referer mismatch |
| `404 { "error": "invalid post" }` | Post not found, not published, or not a tracked type |
| `429 { "error": "rate limit exceeded" }` | Too many requests from this IP |

---

## Post Meta Keys

| Meta Key | Description |
|----------|-------------|
| `post_hits` | Cumulative all-time hit count (integer) |
| `post_hits_daily_YYYY-MM-DD` | Hit count for the given calendar day |

You can query these directly with standard WordPress functions:

```php
$total_hits = (int) get_post_meta( $post_id, 'post_hits', true );
$hits_today = (int) get_post_meta( $post_id, 'post_hits_daily_' . date( 'Y-m-d' ), true );
```

---

## Settings

Navigate to **Settings → Pro Hit Tracker** in the WordPress admin.

| Setting | Default | Description |
|---------|---------|-------------|
| **Tracked Post Types** | `post, page` | Comma-separated list of post type slugs to track. All registered public post types on the current site are listed as a reference. |

### Reset All Hits

The **Danger Zone** section provides a "Reset All Hits" button. Clicking it permanently deletes:

- All `post_hits` and `post_hits_daily_*` post meta rows.
- All `hit_*` deduplication transients.
- All `pht_rate_*` rate-limit transients.

This action cannot be undone.

---

## Admin Features

### Dashboard Widget

A **Top Posts by Hits** widget appears on the WordPress dashboard home screen, listing the top 5 most-viewed published posts across all tracked post types, with direct edit links. Administrators also see a quick link to the settings page.

### Sortable Hits Column

A **Hits** column is added to the post list table for every tracked post type. The column is sortable — click the header to order posts by total hit count ascending or descending.

---

## Auto-Updates

The plugin registers a daily WP-Cron event (`afca_pro_hit_tracker_updates`) that polls the author's update hub for new releases. When a newer version is available, it surfaces in the standard **Plugins → Updates** screen and can be updated with one click.

---

## Project Structure

```
afca-pro-hit-tracker/
├── afca-pro-hit-tracker.php   # Plugin bootstrap & hook registration
├── composer.json              # PSR-4 autoload + dev dependencies
├── phpcs.xml                  # PHP CodeSniffer ruleset (WordPress standard)
├── assets/
│   └── js/
│       └── tracker.js         # Frontend tracking script
└── src/
    ├── Admin/
    │   ├── AdminColumns.php   # Hits column in post list tables
    │   ├── DashboardWidget.php# Top-posts dashboard widget
    │   ├── SettingsPage.php   # Settings & reset UI
    │   └── Updates.php        # Self-hosted auto-update logic
    ├── Api/
    │   ├── NonceEndpoint.php  # GET /pht/v1/nonce
    │   └── TrackEndpoint.php  # POST /pht/v1/track
    ├── Frontend/
    │   └── Enqueue.php        # Enqueues tracker.js on singular views
    ├── Support/
    │   ├── Helpers.php        # IP resolution, bot detection
    │   └── PostTypes.php      # Reads tracked post types from options
    └── Tracker/
        ├── Cleaner.php        # Wipes all hit data (used by reset)
        ├── HitRecorder.php    # Writes post meta & dedup transient
        └── SpamGuard.php      # Bot, rate-limit & origin checks
```

---

## Development

**Install dependencies:**
```bash
composer install
```

**Run code linting:**
```bash
vendor/bin/phpcs --standard=phpcs.xml src/
```

The project follows the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/).

---

## License

Distributed by [André Amorim](https://andreamorim.site). See the plugin header for full details.