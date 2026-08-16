# Contact 2.0.7 for Geeklog

Contact is a lightweight contact-form plugin for Geeklog, modernized while preserving broad compatibility.

## Compatibility

- Geeklog 2.1.1 through 2.2.2
- PHP 5.6 through PHP 8.x
- No external service is required
- Optional integration with Geeklog reCAPTCHA and Spam-X

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

The following protections are applied to public submissions:

- Geeklog mail speed limit
- Spam-X content check
- Honeypot field
- Minimum submission delay
- Geeklog CSRF token validation when the supported token API is available
- Optional human confirmation
- Optional Geeklog reCAPTCHA integration
- Configurable maximum message length

The plugin only uses reCAPTCHA when the reCAPTCHA plugin is active, Contact integration is enabled and the required keys are configured. Missing keys never block the contact form.

Protection modes:

1. **Automatic** — local protections plus reCAPTCHA when available.
2. **Honeypot + delay** — local protections only.
3. **Honeypot + delay + human confirmation** — local protections plus a confirmation checkbox.
4. **reCAPTCHA when available** — explicitly prefers reCAPTCHA, with local protections as fallback.

## Privacy acknowledgement

When enabled, the form displays the configured privacy text. If a valid `http://` or `https://` privacy URL is configured, that text becomes a link to the privacy policy. The checkbox state is preserved when validation fails.

## Cache handling

The plugin clears Geeklog's compiled template/CSS cache after installation and upgrade when `CTL_clearCache()` is available. The public stylesheet also uses a versioned URL (`style.css?v=2.0.7`) to prevent stale browser CSS after upgrades.

## Upgrade notes

Upgrades from Contact 1.x preserve existing configuration where possible. Contact 2.x uses the fixed public path `/contact/`; the old configurable folder setting is removed during upgrade when the Geeklog Configuration API supports it.

Version 2.0.7 adds the `max_message_length` setting with a default of 10,000 characters.

## Installation

Install the plugin using Geeklog's plugin installer, then review **Configuration → Contact**. At minimum, verify the recipient UID and perform a real mail-delivery test.

## Recommended pre-release tests

Test at least:

- Anonymous submission
- Logged-in submission
- Invalid email and empty required fields
- Expired/invalid form token
- Honeypot submission
- Speed-limit rejection
- Message-length rejection
- Sender copy
- Reply action in the received email
- reCAPTCHA enabled and disabled
- Missing reCAPTCHA keys
- Privacy link and validation
- Upgrade from an existing Contact 1.x/2.0.x installation

No telemetry, installation reporting or external callback is included.
