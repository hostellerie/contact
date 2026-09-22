# Contact 2.1.0 Release Notes

Contact 2.1.0 modernizes the plugin's integration with the current Geeklog ecosystem while preserving the transition compatibility required by existing sites.

## Highlights

- Keeps the lightweight public contact form and existing 2.0.x security improvements.
- Normalizes the release version to 2.1.0 across installer, runtime and documentation.
- Adds native Geeklog configuration tooltips for settings where the operational or security effect is not obvious.
- Keeps Geeklog 2.1.1 through 2.2.2 and PHP 5.6 through PHP 8.x compatibility.

## Agent, Hub and Eclipse interoperability

Contact is now explicitly declared as a shared **service provider**.

`plugin_getcapabilities_contact()` advertises:

- `contact.form.describe`
- `dashboard.summary`

The `contact_form_describe` service provides a bounded description of the public form: whether it is enabled, whether login is required, available form behavior, privacy acknowledgement state, message-length limit and anti-spam mode.

It deliberately does **not** expose:

- the configured recipient or recipient email;
- visitor names or email addresses;
- submitted messages;
- mail headers or delivery internals.

The administrator-only `dashboard_summary` service gives Eclipse and future administration consumers a provider-owned status summary without querying Contact internals.

Contact 2.1.0 does **not** advertise `contact.submit`. A machine-triggered send operation should only be introduced after the ecosystem has a stable authorized-action contract with abuse controls and auditability.

## Security and delivery

The existing protection model remains in place:

- Geeklog CSRF validation when available;
- Spam-X checks;
- honeypot protection;
- minimum submission time;
- Geeklog mail speed limiting;
- optional human confirmation;
- optional Geeklog reCAPTCHA;
- configurable maximum message length.

Mail keeps the Geeklog site address in `From:` and the visitor address in `Reply-To` for better SPF/DKIM/DMARC behavior.

## Metadata and packaging

- `plugin.json` remains the safe static discovery manifest.
- Minimum requirements remain Geeklog 2.1.1 and PHP 5.6.0.
- A GitHub Actions workflow builds `dist/contact-2.1.0.zip`.
- The archive workflow rejects dot-prefixed paths that Geeklog 2.2.2 plugin installation rejects.
- `ROADMAP.md` documents transition-compatible improvements and the later Geeklog 2.2.2/PHP 8.1+ modernization phase.

## Upgrade

No persistent database table migration is introduced by 2.1.0.

Geeklog's normal plugin upgrade flow updates the registered plugin version while preserving existing Contact configuration. Installations upgrading from older 1.x versions continue to use the existing configuration migration path.

After upgrading, verify **Configuration → Contact**, the recipient UID, real email delivery, Reply-To behavior and the selected anti-spam mode.
