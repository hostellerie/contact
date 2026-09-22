# Contact 2.1.0 for Geeklog

Contact is a lightweight contact-form plugin for Geeklog, modernized while preserving transition compatibility.

## Compatibility

- Geeklog 2.1.1 through 2.2.2
- PHP 5.6 through PHP 8.x
- No external service is required
- Optional integration with Geeklog reCAPTCHA and Spam-X
- Mono-site and shared-files multisite friendly: runtime state remains site-specific

## Main features

- Public contact form at `/contact/`
- Configurable recipient by Geeklog user UID
- Optional subject field and subject prefix
- Optional copy to the sender
- Optional privacy acknowledgement and privacy-policy link
- Responsive, theme-neutral form
- Visible validation errors with entered values preserved
- UTF-8-safe field truncation when `mbstring` is available
- Configurable maximum message length

## Mail delivery

The plugin keeps the Geeklog site address in `From:` and puts the visitor address in `Reply-To`. This avoids spoofing the visitor address and is friendlier to SPF, DKIM and DMARC while still allowing the recipient to use the normal Reply action.

Recipient and sender addresses are passed to Geeklog as raw email addresses for compatibility with stricter Geeklog 2.2.x mail handling.

## Anti-spam and security

Public submissions use Geeklog's mail speed limit, Spam-X, a honeypot, minimum-fill-time validation, CSRF validation when available, optional human confirmation, optional Geeklog reCAPTCHA and a configurable maximum message length.

The local protections remain active even when reCAPTCHA is unavailable.

## Shared interoperability

Contact 2.1.0 follows the shared provider contract documented in the Geeklog memorandum.

`plugin_getcapabilities_contact()` declares Contact as a `service` provider with:

- `contact.form.describe`
- `dashboard.summary`

The bounded `contact_form_describe` service exposes only public form behavior and configuration state useful to Agent, Hub and other consumers. It deliberately excludes the configured recipient, visitor messages, email addresses and other submission data.

The administrator-only `dashboard_summary` service lets Eclipse and future dashboards show Contact state without reading Contact configuration or private data directly.

`contact.submit` is intentionally **not** declared in 2.1.0. Machine-triggered submissions require an explicit shared authorization/action contract and abuse controls before they are safe to expose.

## Configuration help

Consequential options such as recipient UID, anti-spam mode, minimum fill time, message-length limit and privacy URL expose native Geeklog configuration tooltips.

## Cache handling

The plugin clears Geeklog's compiled template/CSS cache after installation and upgrade when `CTL_clearCache()` is available. The public stylesheet uses `style.css?v=2.1.0` to avoid stale browser CSS after upgrades.

## Upgrade notes

Upgrades from Contact 1.x preserve existing configuration where possible. Contact 2.x uses the fixed public path `/contact/`; the old configurable folder setting is removed during upgrade when the Geeklog Configuration API supports it.

Contact 2.1.0 is primarily an interoperability, packaging and maintenance release. It does not add persistent tables or expose stored contact messages.

## Installation

Install the plugin using Geeklog's plugin installer, then review **Configuration → Contact**. Verify the recipient UID and perform a real mail-delivery test.

## Recommended pre-release tests

Test at least:

- clean install on Geeklog 2.1.1 and 2.2.2
- upgrade from Contact 2.0.7 and an older 1.x installation
- activation, deactivation and uninstall
- anonymous and logged-in submissions
- invalid email and empty required fields
- expired/invalid form token
- honeypot and minimum-fill-time rejection
- speed-limit and message-length rejection
- sender copy and Reply-To behavior
- reCAPTCHA enabled, disabled and unavailable
- privacy acknowledgement and privacy-policy link
- `plugin_getcapabilities_contact()`
- `PLG_invokeService('contact', 'contact_form_describe', ...)`
- authorized and unauthorized `dashboard_summary`
- Eclipse dashboard rendering without plugin-specific SQL
- PHP 5.6 and PHP 8.x syntax/runtime compatibility

No telemetry, installation reporting or external callback is included.

See `ROADMAP.md` and `RELEASE-NOTES.md` for the next modernization steps and this release summary.
