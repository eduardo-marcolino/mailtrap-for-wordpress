# Mailtrap for WordPress

> **Unofficial, community-maintained plugin.** This repository is not an official Mailtrap integration.

Send WordPress emails using **Mailtrap Transactional Stream**, or capture test emails safely with **Mailtrap Email Sandbox**. The plugin routes emails sent through WordPress's normal `wp_mail()` interface, including compatible forms and ecommerce plugins.

## Features

- **Email Sandbox:** capture WordPress test emails without delivering them to the address in the `To` field.
- **Transactional Stream:** deliver live WordPress account notifications, password resets, order confirmations, and similar non-promotional mail.
- Send a test email directly from WordPress Settings → Mailtrap → Send a test.
- Optional read-only Sandbox message browser, using a separate Sandbox API token.
- Separate SMTP credentials for Sandbox and production. Saving settings never automatically changes to production mode.

**Not included:** marketing campaigns, bulk email routing, inbound email, and mail analytics. Use a dedicated integration for those workflows.

## Requirements

- WordPress 6.4 or newer (minimum supported target; see test matrix before publishing).
- PHP 7.4 or newer (PHP 8.x recommended).
- A [Mailtrap account](https://mailtrap.io/).
- For live sending: a [verified sending domain](https://docs.mailtrap.io/email-api-smtp/setup/smtp-integration) in Mailtrap.

## Install

1. Install this plugin in `wp-content/plugins/mailtrap-for-wp`, or install a ZIP through **Plugins → Add New → Upload Plugin**.
2. Activate **Mailtrap for WordPress**.
3. Open **Settings → Mailtrap**.
4. Select **Email Sandbox** or **Transactional Stream**, enter that mode's credentials, and enable Mailtrap SMTP.
5. Save settings and use **Send a test**. In Sandbox mode, open your selected sandbox in Mailtrap to inspect the captured message; in Transactional mode, verify actual delivery separately.

### Email Sandbox (recommended for development and staging)

In your Mailtrap account, go to **Email Sandbox → your sandbox → Integration → SMTP**, and copy the unique credentials:

| Setting | Value |
|---|---|
| Host | `sandbox.smtp.mailtrap.io` (set automatically) |
| Port | `2525` (default); also `587`, `465`, `25` |
| Username | Your sandbox SMTP username |
| Password | Your sandbox SMTP password |
| Encryption | STARTTLS, except port `465` (implicit TLS) |

Test emails **do not** reach real recipients.

### Transactional Stream (production)

1. Verify your sending domain in Mailtrap.
2. Under **Sending Domains → verified domain → Integrations → Transactional Stream → SMTP**, copy your credentials.
3. Select **Transactional Stream** in the plugin. This mode uses host `live.smtp.mailtrap.io`, default port `587`, and the credentials for your domain/stream (often username `api`, with the corresponding SMTP token/password).
4. Set a From address under a verified domain, or let your existing WordPress plugin set one.
5. Send a test to a real email address that you own, and verify delivery.

**Warning:** When you select Transactional Stream, WordPress emails are sent to their real recipients. Do not use it for staging or development traffic. Promotional campaigns require the separate Mailtrap Bulk Stream, which this version does not automatically configure.

### View captured Sandbox messages (optional)

The Sandbox message browser needs its **own API token** from Mailtrap settings. Add it under **Settings → Mailtrap → Sandbox message browser**. It uses read-only requests to the current Sandbox API:

- `GET https://mailtrap.io/api/sandboxes`
- `GET https://mailtrap.io/api/sandboxes/{sandbox_id}/messages`
- `GET https://mailtrap.io/api/sandboxes/{sandbox_id}/messages/{message_id}`
- `GET https://mailtrap.io/api/sandboxes/{sandbox_id}/messages/{message_id}/body.html` (fallback to `.txt` on 404)

The plugin shows messages in a restricted HTML iframe, blocking scripts and external resources. For full rendering and spam checks, open the email in the Mailtrap web interface. Message listings are paginated (up to 30 per page).

## Upgrade from version 0.7

**Important: upgrade does not activate live email delivery.** Existing `mailtrap_enabled`, `mailtrap_username`, `mailtrap_password`, `mailtrap_port`, and `mailtrap_api_token` options are read as **Sandbox-only** configuration until you save the new settings screen. Old settings are retained in the WordPress database for rollback. After confirming that the upgrade is stable and you no longer need rollback, remove the legacy `mailtrap_password` and `mailtrap_api_token` options through an appropriate WordPress database maintenance process; clearing the new fields does not delete these legacy values. Production credentials are separate and are never copied from the old settings.

After upgrading, open **Settings → Mailtrap**, verify the credentials, save once, and run the Sandbox test. If you previously relied on `smtp.mailtrap.io`, the plugin now routes testing through `sandbox.smtp.mailtrap.io`.

## Credential handling

- Manage access to WordPress administration accounts carefully: credentials entered in the admin settings are stored in the WordPress options table. They are not displayed back in the form.
- Leave a password/token field blank when updating settings to preserve the value; use the **Clear** checkbox to remove it.
- On managed deployments, define secrets in `wp-config.php` instead of storing them in the WordPress database:

  ```php
  define( 'MAILTRAP_WP_SANDBOX_PASSWORD', getenv( 'MAILTRAP_WP_SANDBOX_PASSWORD' ) );
  define( 'MAILTRAP_WP_LIVE_PASSWORD', getenv( 'MAILTRAP_WP_LIVE_PASSWORD' ) );
  define( 'MAILTRAP_WP_SANDBOX_API_TOKEN', getenv( 'MAILTRAP_WP_SANDBOX_API_TOKEN' ) );
  ```

  Prefer assigning these from actual server environment variables (and **never commit tokens**). When a constant is defined, it overrides the saved password/token for that field.
- Remove or rotate exposed credentials promptly.

## Compatibility notes

The plugin uses `phpmailer_init` to configure WordPress's standard SMTP transport. Another SMTP plugin can also modify the same mailer; disable conflicting SMTP plugins or choose only one SMTP integration. Some mailing plugins bypass `wp_mail()` and will not be routed through Mailtrap by this plugin.

## Development / validation

```sh
find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l
php tests/smoke.php
```

Before publishing to WordPress.org, the maintainer should additionally test on a real WordPress site against supported PHP and WordPress versions, test end-to-end against Mailtrap Sandbox and a verified sending domain, and update screenshots and translations. The repository's existing WordPress.org deployment workflow still requires the maintainer's SVN credentials.

## Official docs

- [Mailtrap developer API docs](https://docs.mailtrap.io/developers)
- [Sandbox SMTP setup](https://docs.mailtrap.io/email-sandbox/setup/sandbox-smtp-integration)
- [Sandbox API setup](https://docs.mailtrap.io/email-sandbox/setup/sandbox-api-integration)
- [Sending via SMTP](https://docs.mailtrap.io/email-api-smtp/setup/smtp-integration)

License: GPLv2 or later. Original author: Eduardo Marcolino. This PR does not change ownership or make this plugin official.
