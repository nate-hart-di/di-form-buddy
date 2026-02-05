# Story 1.1: Bootstrap Submit Script

Status: review

## Story

As an operator,
I want a single PHP script I can run on any pod to submit a Gravity Forms form with HMAC auth and reCAPTCHA bypass,
so that I can prove end-to-end lead delivery works programmatically.

## Acceptance Criteria

1. Script bootstraps WordPress from `/var/www/domains/{site}/dealer-inspire/wp/wp-load.php`
2. Validates secret via HMAC-SHA256 (`{timestamp}:{form_id}:{nonce}`) from `--secret` arg or `DI_FORM_BUDDY_SECRET` env var
3. Sets `$_REQUEST['recaptcha_response']` to synthetic token before submission
4. Adds `pre_http_request` filter to mock Google reCAPTCHA verify → `{"success": true, "score": 0.9}`
5. Adds `gform_entry_is_spam` filter at priority 999 returning `false`
6. Calls `GFAPI::submit_form()` with input values from `--config` (JSON file) or `--data` (inline JSON)
7. Returns entry_id on success, validation errors on failure, error message on WP_Error
8. Logs all activity via `error_log()` with `[DI-Form-Buddy]` prefix + echoed to CLI
9. Cleans up all bypass filters after submission (in `finally` block)
10. Refuses to run if both `--secret` and `DI_FORM_BUDDY_SECRET` env var are missing (failsafe)
11. Exits with clear error if form doesn't exist or GFAPI isn't available

## Tasks / Subtasks

- [x] Task 1: CLI argument parsing (AC: 2, 6, 10)
  - [x] Parse `--site`, `--form`, `--secret`, `--config`, `--data`, `--inspect` via `getopt()`
  - [x] Validate required args present
  - [x] Resolve secret from arg or env var
- [x] Task 2: WordPress bootstrap (AC: 1, 11)
  - [x] Build path: `/var/www/domains/{--site}/dealer-inspire/wp/wp-load.php`
  - [x] Verify file exists, load it
  - [x] Verify `GFAPI` class available after load
- [x] Task 3: HMAC auth validation (AC: 2)
  - [x] Generate `{timestamp}:{form_id}:{nonce}` payload
  - [x] Compute `hash_hmac('sha256', $payload, $secret)`
  - [x] Log auth event
- [x] Task 4: reCAPTCHA bypass shim (AC: 3, 4, 5, 9)
  - [x] Set `$_REQUEST['recaptcha_response']`
  - [x] Add `pre_http_request` filter scoped to recaptcha URL
  - [x] Add `gform_entry_is_spam` filter at priority 999
  - [x] Cleanup in `finally` block
- [x] Task 5: GFAPI submission + response handling (AC: 6, 7, 8)
  - [x] Load input values from config file or inline JSON
  - [x] Hook `gform_after_submission` to capture entry_id
  - [x] Call `GFAPI::submit_form()`
  - [x] Handle success / validation failure / WP_Error
  - [x] Log + echo results

## Dev Notes

- Single file: `di-form-buddy.php` (~105 lines)
- PHP 7.2: `strpos()` not `str_contains()`, closures not arrow functions, no typed properties
- Config loading: `json_decode(file_get_contents($path), true)` — no YAML extension on pods
- Entry ID capture: hook `gform_after_submission` before `GFAPI::submit_form()`, capture in closure variable
- reCAPTCHA plugin confirmed: `gravity-forms-no-captcha-recaptcha` on all pods

### Project Structure Notes

- Script lives at project root: `di-form-buddy.php`
- Configs in `configs/` directory (JSON files)
- No WordPress plugin structure — standalone script

### References

- [Source: docs/project-context.md — pod paths, PHP 7.2 constraints]
- [Source: epics.md — Story 1.1 acceptance criteria, platform context]
- Platform pattern: `dealer_inspire_validate_request()` in `di-gravityforms/dealer-inspire.php`
- reCAPTCHA source: `NoCaptchaReCaptchaPublic.php:49,149,163,259`
- Lifecycle: `GFAPI::submit_form()` → `gform_after_submission` → `LeadsHandler->handle()`

## Dev Agent Record

### Agent Model Used

Claude Opus 4.5 (claude-opus-4-5-20251101)

### Debug Log References

- All tests run under PHP 7.2.34 (target runtime) and PHP 8.4.10 (local dev) — 41/41 pass on both

### Completion Notes List

- Implemented all 5 tasks in single file `di-form-buddy.php` (~300 lines with docs)
- All 11 acceptance criteria addressed in implementation
- PHP 7.2 compatibility verified via `php -l` lint check AND static source analysis
- CLI argument parsing covers: `--site`, `--form`, `--secret`, `--config`, `--data`, `--inspect`, `--list` via `getopt()`
- Secret resolution: `--secret` arg takes precedence over `DI_FORM_BUDDY_SECRET` env var; refuses to run if neither available
- HMAC auth: `{timestamp}:{form_id}:{nonce}` payload with `hash_hmac('sha256', ...)` signature
- reCAPTCHA bypass (full chain — 5 filters):
  1. `$_REQUEST['recaptcha_response']` synthetic token
  2. `pre_http_request` filter mocks Google verify → `{"success":true,"score":0.9}`
  3. `gform_entry_is_spam` at priority 999 returns `false` (overrides NoCaptchaReCaptcha pri 10 + DISpamCheck pri 11)
  4. `di_gform_spam_filter_active` returns `false` (disables DI content-based spam checker)
  5. `gform_after_submission` at priority 9999 clears `spam_reason` meta and sets `recaptcha_verified=true`
- Notification safety: `gform_notification` at priority 1 reroutes all `to`/`cc`/`bcc` to `di.form.buddy@gmail.com`
- Cleanup in `finally` block removes all bypass + notification filters after submission
- `--inspect` mode dumps form fields without submitting
- `--list` mode enumerates all forms on a site with entry counts
- Entry ID capture via `gform_after_submission` hook before `GFAPI::submit_form()` call
- All functions prefixed `di_form_buddy_` per project conventions

### POC Live Test Results (2026-02-04)

- **Pod:** deploy.pod46.dealerinspire.com
- **Site:** www.albanytoyota.net
- **Test 1 (Entry 1592):** Form 3 "Contact Us" — entry created but flagged "reCAPTCHA V3 Failed" by DI Spam Detector (only `pre_http_request` + `gform_entry_is_spam` bypass active)
- **Test 2 (Entry 1593):** Form 3 "Contact Us" — clean entry, no spam flag after adding `di_gform_spam_filter_active` disable + `spam_reason` meta cleaner
- **Test 3 (Entry 1594):** Form 17 "Test Drive Tour" (active notification to `leads@crm.albanytoyota.net`) — clean entry, notification reroute fired correctly
- **Email delivery:** `wp_mail()` returns true but pod postfix sends from EC2 internal IP with no SPF/DKIM — emails silently dropped by Gmail. Known infrastructure limitation, not a script issue.
- All test entries deleted after verification

### POC Findings — Carried Forward

1. **DI Spam Detector is a second spam layer** beyond standard GF `gform_entry_is_spam`. Must bypass `di_gform_spam_filter_active` filter AND clear `spam_reason`/`recaptcha_verified` entry meta post-submission. Source: `NoCaptchaReCaptchaPublic.php` and `di_gform_spam_filter.php` in platform KB.
2. **Pod email delivery is unreliable** for external addresses. `wp-mail-smtp` plugin is installed but not configured. Postfix sends from EC2 internal IPs. Real dealer notifications likely route through CRM ingest endpoints (`leads@crm.*`), not standard SMTP delivery.
3. **Test data email address:** `testlead@dealerinspire.com` may not have an inbox. Need to investigate valid test email during Story 1.2. Using `di.form.buddy@gmail.com` for now.
4. **`--inspect` and `--list` already implemented** — originally scoped for Story 1.2 Task 1, completed early during 1.1 to support live POC testing.

### Change Log

- 2026-02-04: Initial implementation — all 5 tasks complete, 36/36 tests passing
- 2026-02-04: Added DI Spam Detector bypass (`di_gform_spam_filter_active`, `spam_reason` meta cleaner)
- 2026-02-04: Added notification reroute safety filter (`gform_notification` → `di.form.buddy@gmail.com`)
- 2026-02-04: Added `--list` mode for form discovery
- 2026-02-04: Live POC validated on pod46/albanytoyota — 41/41 tests passing

### File List

- `di-form-buddy.php` (new) — Main script implementing all 5 tasks + `--list` + notification reroute
- `tests/test-di-form-buddy.php` (new) — Test suite with 41 tests covering HMAC, bypass filters, notification reroute, CLI args, PHP 7.2 compat
