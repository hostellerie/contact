# Changelog

## Contact 2.0.7

- Added Geeklog CSRF token validation for contact submissions when the supported token API is available.
- Added configurable maximum message length (10,000 characters by default).
- Added sender name and email to Spam-X input.
- Made single-line truncation UTF-8-safe when `mbstring` is available.
- Fixed stylesheet cache busting so the code now actually loads `style.css?v=2.0.7`.
- Fixed privacy-policy URL handling: configured HTTP(S) URLs now link the privacy acknowledgement text.
- Preserve the privacy checkbox after a validation error.
- Escape the configurable form introduction as plain text; rich content can continue to be supplied with the Static Pages integration.
- Updated plugin metadata and documentation for the `hostellerie/contact` fork.

## Contact 2.0.6

- Versioned the public stylesheet URL so browsers reload CSS after plugin updates.
- Clear Geeklog template/CSS cache automatically after install and upgrade to prevent stale Contact templates.

## Contact 2.0.4

- Added a `Reply-To` header using the visitor email while keeping the site address in `From:` for SPF/DKIM/DMARC compatibility.
- Fixed Geeklog 2.2.x mail delivery compatibility by passing raw recipient/from email addresses to `COM_mail()`.
- Added validation/fallback for the configured site sender address.
- Added anti-spam protection modes and safer reCAPTCHA detection/fallback.
- Added Geeklog 2.2.2 support for reCAPTCHA v2, invisible and v3 template variables.

## Contact 2.0.1

- Removed the configurable public folder; Contact now always uses `/contact/`.
- Removed the legacy `folder_name` setting during upgrade when supported.
- Fixed silent validation failures by displaying a dedicated error block.
- Improved surfacing of CAPTCHA/reCAPTCHA validation failures.

## Contact 2.0.0

- Removed all external installation/update reporting emails.
- Hardened output and removed arbitrary `msg` query-string rendering.
- Added guarded POST access and stricter validation.
- Stopped using the visitor email as the `From` address.
- Added subject field, honeypot, minimum submission time and optional privacy acknowledgement.
- Added responsive, UI-framework-neutral markup/CSS.
- Added French configuration translations and a 1.x → 2.0 upgrade path.
