---
project_name: pdf2md
user_name: Nate
date: 2026-01-26
sections_completed:
- technology_stack
- language_rules
- framework_rules
- testing_rules
- quality_rules
- workflow_rules
- anti_patterns
status: complete
rule_count: 15
optimized_for_llm: true
---

# Project Context for AI Agents

_This file contains critical rules and patterns that AI agents must follow when implementing code in this project. Focus on unobvious details that agents might otherwise miss._

---

## Technology Stack & Versions

- **PHP:** 8.1+ (Target), 7.4+ (Legacy Compatibility required)
- **WordPress:** 6.x
- **Gravity Forms:** 1.9.x (Legacy) to 2.8.x (Modern)
- **Environment:** Dealer-Inspire Child Theme (functions.php injection)

## Critical Implementation Rules

### Language-Specific Rules (PHP)

- **Prefixing:** All global functions/constants MUST be prefixed with `di_ab_`.
- **Namespaces:** Use `DealerInspire\AutomationBypass` for classes (where supported).
- **Strict Types:** Use `declare(strict_types=1);` in new class files.
- **Security:** Use `hash_hmac('sha256', ...)` for signature verification.

### Framework-Specific Rules (WordPress)

- **Logging:** Use `error_log()` with prefix `[DI-Automation]`. Do not use `WP_DEBUG_LOG` constant directly.
- **Hooks:** Hook `init` early (priority 1) for auth checks. Hook `pre_http_request` for API mocking.
- **Database:** Avoid direct DB calls; use WP APIs.

### Testing Rules

- **Prototype:** Verification via `curl` and `docker logs` (or `tail -f debug.log`).
- **Production:** PHPUnit tests in `tests/` directory.

### Critical Don't-Miss Rules

- **Fail-Safe Default:** If `DI_AUTOMATION_SECRET_KEY` is undefined/null, bypass is DISABLED.
- **Zero-Dependency:** Prototype snippet must not require Composer `vendor/`.
- **Latency:** Auth check must be memoized (static variable) to prevent re-hashing on every hook.

---

## Usage Guidelines

**For AI Agents:**

- Read this file before implementing any code
- Follow ALL rules exactly as documented
- When in doubt, prefer the more restrictive option
- Update this file if new patterns emerge

**For Humans:**

- Keep this file lean and focused on agent needs
- Update when technology stack changes
- Review quarterly for outdated rules
- Remove rules that become obvious over time

Last Updated: 2026-01-26