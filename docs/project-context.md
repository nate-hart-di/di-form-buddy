---
project_name: di-form-buddy
user_name: Nate
date: 2026-02-04
sections_completed:
- technology_stack
- language_rules
- framework_rules
- testing_rules
- quality_rules
- workflow_rules
- anti_patterns
- platform_access
status: complete
rule_count: 20
optimized_for_llm: true
---

# Project Context for AI Agents

_This file contains critical rules and patterns that AI agents must follow when implementing code in this project. Focus on unobvious details that agents might otherwise miss._

---

## Technology Stack & Versions

- **PHP:** 7.2.34 (confirmed on production pods — target 7.2+ compatibility)
- **WordPress:** 6.x (loaded via bootstrap, not installed by us)
- **Gravity Forms:** Present on all sites (core + di-gravityforms + no-captcha-recaptcha plugins)
- **Environment:** DI platform pods (`deploy.pod{N}.dealerinspire.com`)
- **Deployment:** POC runs as standalone PHP script on pod via SSH. No MU-plugin, no plugin changes.
- **YAML extension:** NOT available on pods (`php-yaml` not installed). Use JSON configs or simple custom parser.

## Platform Access (Confirmed)

- **SSH:** `sshpass -e ssh nhart@deploy.pod{N}.dealerinspire.com` (uses `$SSH_PASSWORD` env var)
- **Dev pod:** `deploy.poddev.dealerinspire.com`
- **Live pods:** `deploy.pod{N}.dealerinspire.com` (e.g., pod47)
- **Site root:** `/var/www/domains/{domain}/dealer-inspire/`
- **WordPress core:** `/var/www/domains/{domain}/dealer-inspire/wp/wp-load.php`
- **Plugins:** `/var/www/domains/{domain}/dealer-inspire/wp-content/plugins/`
- **Confirmed plugins:** `gravityforms`, `di-gravityforms`, `gravity-forms-no-captcha-recaptcha`

## Critical Implementation Rules

### PHP 7.2 Compatibility (CRITICAL)

- **NO typed properties** (`public int $x` is PHP 7.4+)
- **NO arrow functions** (`fn() =>` is PHP 7.4+)
- **NO null coalescing assignment** (`??=` is PHP 7.4+)
- **NO named arguments** (PHP 8.0+)
- **NO match expressions** (PHP 8.0+)
- **NO union types** (PHP 8.0+)
- **NO `str_contains()`, `str_starts_with()`** (PHP 8.0+)
- **OK:** `declare(strict_types=1)`, type-hinted parameters, return types, `?nullable`, `random_bytes()`, `hash_hmac()`, `hash_equals()`

### Language-Specific Rules (PHP)

- **Prefixing:** All global functions/constants MUST be prefixed with `di_form_buddy_`.
- **Security:** Use `hash_hmac('sha256', ...)` for signature verification, `hash_equals()` for timing-safe comparison.
- **No Composer:** Zero dependencies. Native PHP functions only.

### Framework-Specific Rules (WordPress)

- **Bootstrap:** `require_once '/var/www/domains/{domain}/dealer-inspire/wp/wp-load.php'`
- **Logging:** Use `error_log()` with prefix `[DI-Form-Buddy]`.
- **Hooks:** `add_filter('pre_http_request', ...)` for reCAPTCHA mock, `add_filter('gform_entry_is_spam', ..., 999)` for spam override.
- **GFAPI:** `\GFAPI::submit_form($form_id, $input_values)` after bootstrap.

### Deployment Rules (POC)

- **Script location:** Upload to pod via `scp` or create in `/tmp/` via SSH
- **Execution:** `php /path/to/di-form-buddy.php --form=1 --site=aaroncdjr.com --secret=<key>`
- **WordPress bootstrap:** Script resolves site domain to `/var/www/domains/{domain}/dealer-inspire/wp/wp-load.php`
- **No plugin installation, no MU-plugin, no theme changes**

### Critical Don't-Miss Rules

- **Fail-Safe Default:** If secret is undefined/null/empty, bypass is DISABLED.
- **Zero-Dependency:** Script must not require Composer `vendor/`.
- **Auth memoization:** Auth check must use static variable to prevent re-hashing on every hook.
- **Cleanup:** All reCAPTCHA bypass filters MUST be removed after submission completes.
- **PHP 7.2:** Test all code against PHP 7.2 syntax. No modern PHP features.

---

## Usage Guidelines

**For AI Agents:**

- Read this file before implementing any code
- Follow ALL rules exactly as documented
- When in doubt, prefer the more restrictive option
- PHP 7.2 compatibility is NON-NEGOTIABLE

**For Humans:**

- Keep this file lean and focused on agent needs
- Update when platform access or PHP version changes

Last Updated: 2026-02-04
