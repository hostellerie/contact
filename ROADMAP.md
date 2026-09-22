# Contact Roadmap

## 2.1.0 — interoperability and release foundation

- [x] Geeklog 2.1.1–2.2.2 transition compatibility.
- [x] PHP 5.6–8.x transition compatibility.
- [x] Static `plugin.json` metadata manifest.
- [x] Shared `service` capability declaration.
- [x] Bounded `contact.form.describe` read service.
- [x] Administrator-only `dashboard.summary` service for Eclipse and other administration consumers.
- [x] Native configuration tooltips for consequential settings.
- [x] SPF/DKIM/DMARC-friendly mail delivery using site From and visitor Reply-To.
- [x] CSRF, Spam-X, honeypot, minimum-fill-time and optional reCAPTCHA protection.
- [x] Optional privacy acknowledgement.
- [x] Release notes and automated installable archive generation.
- [x] No telemetry or external installation reporting.

## 2.1.x — transition-compatible improvements

These improvements should remain compatible with Geeklog 2.1.1–2.2.2 and PHP 5.6–8.x.

### Administration and diagnostics

- Add a small Contact administration/status page if it provides more value than the native Configuration screen alone.
- Validate the configured recipient UID and email address in an administrator-facing diagnostic without exposing the address to generic consumers.
- Add a bounded health/status service only if reusable diagnostics become useful to Monitor and Eclipse.
- Improve dashboard alerts for invalid recipient configuration, unavailable reCAPTCHA configuration and unusable privacy URLs.
- Keep all diagnostic checks local and bounded; dashboard rendering must not perform external network requests.

### Configuration and usability

- Review configuration labels and tooltips on Geeklog 2.1.1 and 2.2.2.
- Consider a recipient selector instead of a raw UID while preserving a stable stored value.
- Add clearer configuration grouping for page presentation, delivery, anti-spam and privacy.
- Review accessibility of validation summaries, focus handling and required-field indication.
- Keep presentation in templates/CSS rather than introducing plugin-specific JavaScript unless required.

### Mail and abuse protection

- Add explicit mail-delivery diagnostics for administrator testing without storing visitor messages.
- Review compatibility with current Geeklog mail transports and headers.
- Consider optional rate-limit policy controls only when they can reuse Geeklog/Core facilities.
- Keep Spam-X and native reCAPTCHA integration feature-detected and optional.
- Add automated tests for header injection, Reply-To handling, malformed UTF-8, oversized input and anti-spam paths.

### Interoperability tests

- Add source-contract tests for `plugin_getcapabilities_contact()`.
- Add service tests for `contact_form_describe` and `dashboard_summary`.
- Verify Eclipse consumes the summary generically with no Contact-specific adapter.
- Verify Agent/Hub discovery never exposes recipient addresses or submitted messages.
- Add a shared-files multisite test to confirm configuration and runtime state remain site-specific.

## 2.2+ — after the Geeklog 2.2.2 / PHP 8.1+ baseline

These changes can use the newer project baseline after historical sites have migrated.

### Modern code structure

- Introduce a small internal service layer for form description, validation, delivery and diagnostics.
- Reduce global helper functions where a maintainable namespaced structure improves clarity.
- Adopt stricter typing and newer PHP syntax only after the PHP 5.6 transition requirement is retired.
- Add PHPUnit/static-analysis coverage appropriate to the new baseline.

### Authorized machine actions

- Consider the memorandum capability `contact.submit` only after the shared action/authorization contract is stable.
- Require explicit authorization, caller identity, quotas/rate limits, audit logging and the same spam/privacy rules as the human form.
- Never interpret capability discovery as permission to send mail.
- Never expose recipient addresses or arbitrary mail relay behavior through Agent, Hub, MCP or another connector.

### Form modernization

- Consider optional topic/category routing without turning Contact into a general form builder.
- Consider configurable confirmation content and mail templates.
- Consider attachment support only with explicit persistent-storage, MIME validation, quota, malware-risk and retention rules.
- Consider structured privacy/consent metadata if a shared Geeklog privacy contract emerges.
- Keep Contact intentionally small; advanced multi-form workflows belong in the Forms plugin rather than being duplicated here.

## Architectural boundaries

Contact owns its form configuration, validation, anti-abuse rules and mail delivery.

Agent may expose Contact's shared read capabilities to machine consumers. Eclipse may display its dashboard summary. Hub may discover Contact as a service provider, but Contact does not represent durable content and should not be forced into the content-item contract.

The plugin must not create Agent-specific, Eclipse-specific or Hub-specific registries. Shared capabilities and bounded services remain the integration surface.
