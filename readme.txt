=== Mailtrap for WordPress ===
Contributors: eduardo.marcolino
Tags: mailtrap, smtp, email sandbox, email testing, transactional email
Requires at least: 6.4
Requires PHP: 7.4
Stable tag: 0.8.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Send WordPress emails with Mailtrap transactional SMTP or capture them safely in Mailtrap Email Sandbox.

== Description ==

**Unofficial third-party plugin; not an official Mailtrap integration.**

Mailtrap for WordPress connects WordPress's standard wp_mail() function to Mailtrap SMTP.

* Email Sandbox: safely capture test emails instead of delivering to customers.
* Transactional Stream: send live password resets, order emails, and site notifications through a verified Mailtrap sending domain.
* Test your mailer settings from the WordPress dashboard.
* Optionally list captured Sandbox messages with a separate API token.

Production mode sends live email; testing mode does not. Promotional/bulk streams are not automatically managed by this plugin.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/mailtrap-for-wp/`, then activate it.
2. Go to Settings > Mailtrap.
3. Select Email Sandbox or Transactional Stream and copy SMTP credentials from the appropriate Mailtrap integration screen.
4. Save and send a test email. For Transactional Stream, verify your sending domain first.

See README.md in GitHub for API tokens, configuration, and security notes.

== Frequently Asked Questions ==

= Will Sandbox emails be delivered to actual recipients? =

No. Email Sandbox captures messages without delivering them to the specified recipient.

= Can the plugin send live transactional mail? =

Yes. Select Transactional Stream and configure its separate SMTP credentials for a verified sending domain.

= Does upgrading from 0.7 activate live delivery? =

No. Existing settings are interpreted as Sandbox configuration and remain in test mode.

== Changelog ==

= 0.8.0 =
* Support current Mailtrap Email Sandbox and Transactional Stream SMTP endpoints.
* Migrate old SMTP settings in Sandbox-only mode without switching to live delivery.
* Migrate the Sandbox message browser to current Mailtrap API routes.
* Add safer credential fields, input validation, output escaping, and restricted message previews.
* Update naming, docs, and PHP/WordPress compatibility requirements.

= 0.7 =
* Added email reader using Mailtrap API.
* Updated plugin assets.
