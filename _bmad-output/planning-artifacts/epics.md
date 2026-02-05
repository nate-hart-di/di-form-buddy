---
stepsCompleted: [validate-prerequisites, design-epics, create-stories, final-validation]
inputDocuments: [docs/project-context.md]
lastUpdated: 2026-02-04
---

# DI Form Buddy - Epic Breakdown

## Overview

DI Form Buddy is a tool for programmatically submitting Gravity Forms on the Dealer Inspire platform. Delivered in two phases: a feature-complete POC bootstrap script (zero cross-team dependencies) followed by scale enablement for batch automation across 1000+ sites.

> **Pivot Note (2026-02-04):** Original Epic 2 (REST API + remote CLI) was scrapped. The POC exceeded expectations — it already includes config generation, notification rerouting, and full bypass logic. New Epic 2 focuses on enabling batch automation through structured output and health checks.

## Requirements Inventory

### Functional Requirements

- FR1: Submit a Gravity Forms form programmatically via `GFAPI::submit_form()`, triggering the full GF lifecycle.
- ~~FR2: Expose a REST API endpoint (`/wp-json/di-form-buddy/v1/submit`) on every DI site for remote access.~~ *(Scrapped)*
- FR3: Authenticate requests via HMAC-SHA256.
- FR4: Reject unauthenticated or invalid requests with appropriate error responses.
- FR5: Bypass reCAPTCHA validation for authenticated requests only (5-filter shim).
- FR6: Override spam marking for authenticated requests (`gform_entry_is_spam` filter).
- FR7: Return structured response including `entry_id`, `is_valid`, `is_spam`, and validation errors.
- ~~FR8: CLI client generates HMAC tokens and POSTs to any target site URL.~~ *(Scrapped)*
- FR9: CLI/script reads form field mappings from JSON configuration files.
- FR10: Auto-generate configs for any form via `--generate-configs` flag. *(Was: manual configs for 16 types)*
- ~~FR11: Runtime validation of config against live form schema via `GFAPI::get_form()`.~~ *(Deferred)*
- FR12: Log all authenticated bypass events with `[DI-Form-Buddy]` prefix.
- **FR13: Machine-parseable JSON output mode for batch automation.**
- **FR14: Non-destructive health check mode that validates form configuration without submitting.**

### Non-Functional Requirements

- NFR1: Zero new PHP dependencies on the server side (native `hash_hmac`, WP core filters).
- NFR2: Failsafe default: bypass is disabled if `DI_FORM_BUDDY_SECRET` env var is missing.
- NFR3: HMAC timestamp window of 5 minutes to prevent replay attacks.
- NFR4: PHP 7.2 compatible (no typed properties, no arrow functions, no `str_contains()`).
- NFR5: Total overhead for HMAC validation < 5ms.
- NFR6: Follow existing platform patterns (WPCS naming, `Env::get()` for config).
- **NFR7: Health check mode must not require authentication (read-only operation).**

### FR Coverage Map

- FR1, FR3, FR4, FR5, FR6, FR7, FR9, FR10, FR12: Epic 1 (POC Bootstrap Script) ✅ COMPLETE
- FR13: Epic 2, Story 2.1 (Structured JSON Output) ✅ COMPLETE
- FR14: Epic 2, Story 2.2 + 2.3 (Health Check Mode) ✅ COMPLETE
- FR13 + FR14: Epic 2, Story 2.4 (Health Check Campaign) ✅ COMPLETE — batch orchestration

## Epic List

### Epic 1: POC Bootstrap Script (Phase 1) ✅ COMPLETE

**Goal:** Build a single PHP script that bootstraps WordPress on a pod, authenticates via HMAC, bypasses reCAPTCHA, submits a form via GFAPI, and logs the result. Zero cross-team dependencies.

**User Value:** Operators can prove end-to-end programmatic lead delivery on any pod with a single command.

**FRs covered:** FR1, FR3, FR4, FR5, FR6, FR7, FR9, FR10, FR12

**Actual Delivery:** 1,125-line feature-complete tool with config generation, notification rerouting to test inbox, 5-filter bypass shim, and multiple operation modes (`--list`, `--inspect`, `--generate-configs`).

### Epic 2: Scale Enablement (Structured Output + Health Check) ✅ COMPLETE

**Goal:** Enable batch automation and safe production scanning by adding machine-parseable output and a non-destructive health check mode.

**User Value:** Operators can run the tool across hundreds of sites, aggregate results programmatically, and verify form health without creating test entries.

**FRs covered:** FR13, FR14

**Actual Delivery:** JSON output mode, single-form and all-forms health check, batch campaign script with SSH orchestration (10 sites/pod sampling).

### Epic 3: Full-Scale Validation (Phase 3) — PLANNED

**Goal:** Run comprehensive health checks across all production sites, generate actionable remediation reports, and establish baseline form health metrics.

**User Value:** Platform team has visibility into form health across the entire fleet, can prioritize fixes, and track improvements over time.

**FRs covered:** FR13, FR14 (at scale)

---

## Epic 1: POC Bootstrap Script

Build a single PHP script (~105 lines) that proves HMAC auth, reCAPTCHA bypass, GFAPI submission, and full lifecycle delivery — directly on a pod via SSH.

### Story 1.1: Bootstrap Submit Script

As an operator,
I want a single PHP script I can run on any pod to submit a Gravity Forms form with HMAC auth and reCAPTCHA bypass,
So that I can prove end-to-end lead delivery works programmatically.

**Acceptance Criteria:**

**Given** an operator on a pod with the script and a valid secret
**When** they run `php di-form-buddy.php --site=aaroncdjr.com --form=1 --secret=<key> --config=configs/01-eprice.json`
**Then** the script bootstraps WordPress from `/var/www/domains/{site}/dealer-inspire/wp/wp-load.php`
**And** validates the secret via HMAC-SHA256 (`{timestamp}:{form_id}:{nonce}`)
**And** sets `$_REQUEST['recaptcha_response']` to a synthetic token before submission
**And** adds `pre_http_request` filter to mock Google reCAPTCHA verify response `{"success": true, "score": 0.9}`
**And** adds `gform_entry_is_spam` filter at priority 999 returning `false`
**And** calls `GFAPI::submit_form()` with input values from the config file
**And** returns entry_id on success, validation errors on failure, error message on WP_Error
**And** logs all activity via `error_log()` with `[DI-Form-Buddy]` prefix + echoed to CLI
**And** cleans up all bypass filters after submission (in `finally` block)
**And** if `--secret` and `DI_FORM_BUDDY_SECRET` env var are both missing, refuses to run
**And** if form doesn't exist or GFAPI isn't available, exits with clear error

**CLI interface:**
```
php di-form-buddy.php --site=aaroncdjr.com --form=1 --secret=mysecret --config=configs/01-eprice.json
php di-form-buddy.php --site=aaroncdjr.com --form=1 --secret=mysecret --data='{"input_1_3":"Test","input_2":"test@test.com"}'
```

**Platform context (verified from source):**
- `dealer_inspire_validate_request()` in `di-gravityforms/dealer-inspire.php` (HMAC pattern)
- `NoCaptchaReCaptchaPublic.php:49` — hooks fire when `!is_admin()`; REST/CLI are NOT admin
- `NoCaptchaReCaptchaPublic.php:149` — checks `$_REQUEST['recaptcha_response']`
- `NoCaptchaReCaptchaPublic.php:163` — calls `google.com/recaptcha/api/siteverify` via `wp_remote_post`
- `GFAPI::submit_form()` fires `gform_after_submission` → `LeadsHandler->handle()` → Platform Leads API

**PHP 7.2 notes:** Use `strpos()` not `str_contains()`. Use closures not arrow functions. No typed properties. No YAML extension on pods — use JSON configs.

### Story 1.2: Form Configurations (Get E-Price + Contact Us)

As an operator,
I want JSON config files for Get E-Price and Contact Us forms,
So that I can test submissions without memorizing Gravity Forms field IDs.

**Acceptance Criteria:**

**Given** the need to submit Form 1 (Get E-Price) and Form 3 (Contact Us)
**When** I deploy the tool with configs
**Then** `configs/01-eprice.json` exists with: `form_id`, `form_name`, `fields` (label→input_key), `test_data` (label→value), `required_fields`
**And** `configs/03-contact-us.json` exists with the same structure
**And** test data uses safe values: `testlead@dealerinspire.com`, `5551234567`, `Test Lead`
**And** Story 1.1 script can load either config and submit successfully
**And** an `--inspect` flag on Story 1.1 script calls `GFAPI::get_form($id)` to discover field IDs

---

## Epic 2: Scale Enablement

Enable batch automation across 1000+ sites by adding structured JSON output and non-destructive health check mode. Zero cross-team dependencies.

### Story 2.1: Structured JSON Output Mode

As an automation script,
I want machine-parseable JSON output from di-form-buddy,
So that I can aggregate results across multiple sites programmatically.

**Acceptance Criteria:**

**Given** the tool is run with `--output=json`
**When** a submission succeeds
**Then** stdout contains exactly one JSON object:
```json
{
  "success": true,
  "site": "dealer.com",
  "form_id": 1,
  "form_name": "Get E-Price",
  "entry_id": 12345,
  "timestamp": "2026-02-04T15:30:00Z"
}
```

**When** validation fails
**Then** stdout contains:
```json
{
  "success": false,
  "site": "dealer.com",
  "form_id": 1,
  "error": "validation_failed",
  "validation_errors": {"3": "Email is required"}
}
```

**When** a fatal error occurs (no WP, no GFAPI, no form)
**Then** stdout contains:
```json
{
  "success": false,
  "site": "dealer.com",
  "form_id": 1,
  "error": "bootstrap_failed",
  "message": "WordPress not found at /var/www/..."
}
```

**And** all `[DI-Form-Buddy]` log lines go to stderr (not stdout)
**And** exit code 0 for success, non-zero for failure (unchanged)

**Implementation notes:**
- Add `--output` flag accepting `text` (default) or `json`
- When `json`, replace `di_form_buddy_log()` to write to stderr
- Capture result in structured array, `json_encode()` to stdout at end
- ~30 lines of changes

### Story 2.2: Health Check Mode

As an operator,
I want to verify a site's form configuration without submitting a lead,
So that I can safely scan production sites at scale.

**Acceptance Criteria:**

**Given** the tool is run with `--health-check --form=1`
**When** executed
**Then** the tool:
- Bootstraps WordPress ✓
- Verifies GFAPI available ✓
- Verifies form exists ✓
- Reports field count and required fields ✓
- Does NOT install bypass filters
- Does NOT call `GFAPI::submit_form()`
- Does NOT require `--secret`

**Output (human-readable default):**
```
[DI-Form-Buddy] HEALTH CHECK: dealer.com / Form 1
[DI-Form-Buddy] ✓ WordPress bootstrapped
[DI-Form-Buddy] ✓ GFAPI available
[DI-Form-Buddy] ✓ Form exists: "Get E-Price"
[DI-Form-Buddy] ✓ 8 fields, 3 required
[DI-Form-Buddy] HEALTH: PASS
```

**With `--output=json`:**
```json
{
  "mode": "health_check",
  "site": "dealer.com",
  "form_id": 1,
  "form_name": "Get E-Price",
  "status": "pass",
  "field_count": 8,
  "required_count": 3,
  "checks": {
    "wordpress": true,
    "gfapi": true,
    "form_exists": true
  }
}
```

**When** any check fails
**Then** status is `"fail"` and the failing check is `false`

**Implementation notes:**
- New execution branch after bootstrap, before HMAC/bypass
- Reuses existing `GFAPI::get_form()` logic from `--inspect`
- ~40 lines of changes

### Story 2.3: Health Check All Forms

As an operator,
I want to health-check all forms on a site in one command,
So that I can quickly verify a site's lead readiness.

**Acceptance Criteria:**

**Given** the tool is run with `--health-check --all` (no `--form`)
**When** executed
**Then** the tool iterates all forms via `GFAPI::get_forms()`
**And** reports health status for each form
**And** summary line: `HEALTH: X/Y forms passed`

**With `--output=json`:**
```json
{
  "mode": "health_check_all",
  "site": "dealer.com",
  "status": "pass",
  "forms_checked": 5,
  "forms_passed": 5,
  "results": [
    {"form_id": 1, "form_name": "Get E-Price", "status": "pass", ...},
    {"form_id": 3, "form_name": "Contact Us", "status": "pass", ...}
  ]
}
```

**Implementation notes:**
- Combines `--list` enumeration with Story 2.2 health check per form
- ~20 lines of changes

### Story 2.4: Health Check Campaign Script

As an operator,
I want to run health checks across multiple pods in batch,
So that I can validate form health at scale without manual SSH iteration.

**Acceptance Criteria:**

**Given** the campaign script `scripts/health-check-campaign.sh`
**When** run with default settings
**Then** it checks 10 sites per pod across all live pods (1-47)
**And** reads site data from `dealers.json` (devtools cache)
**And** SSHes to each pod and runs `di-form-buddy.php --health-check --all --output=json`
**And** aggregates results to `results/health-checks/YYYY-MM-DD/`
**And** produces per-pod JSONL files + combined `all-results.jsonl`
**And** generates `summary.json` with pass rates and totals
**And** tracks failures separately in `failures.jsonl`

**CLI interface:**
```bash
./scripts/health-check-campaign.sh                    # 10 sites/pod, all pods
./scripts/health-check-campaign.sh --sample=5         # 5 sites/pod
./scripts/health-check-campaign.sh --pods=1,2,3       # specific pods only
./scripts/health-check-campaign.sh --sample=all       # full coverage (future)
./scripts/health-check-campaign.sh --dry-run          # preview only
```

**Environment requirements:**
- `SSH_PASSWORD` or `SSHPASS` env var set (via `setup.sh`)
- `sshpass` installed (`brew install hudochenkov/sshpass/sshpass`)
- `dealers.json` from devtools CLI cache

**Implementation notes:**
- Delivered as `scripts/health-check-campaign.sh` (~280 lines)
- Uses `sshpass -e` for non-interactive SSH
- Default sample of 10 sites/pod validates tooling before full-scale runs
- Full coverage (`--sample=all`) deferred to Story 3.1

---

## Epic 3: Full-Scale Validation

**Goal:** Run comprehensive health checks across all production sites, generate actionable remediation reports, and establish baseline form health metrics.

**User Value:** Platform team has visibility into form health across the entire fleet, can prioritize fixes, and track improvements over time.

**FRs covered:** FR13, FR14 (at scale)

### Story 3.1: Full Coverage Health Check Campaign

As an operator,
I want to run health checks across ALL sites on all pods,
So that I can establish a complete baseline of form health across the platform.

**Acceptance Criteria:**

**Given** the campaign script run with `--sample=all`
**When** executed
**Then** it checks every live production site across all pods
**And** produces comprehensive results in `results/health-checks/YYYY-MM-DD/`
**And** generates summary statistics (pass rate, common failures, per-OEM breakdown)
**And** completes within reasonable time (parallelization as needed)

**Implementation notes:**
- Extends Story 2.4 campaign script
- May require `--parallel=N` for acceptable runtime
- Estimated ~1500+ sites across 47 pods

### Story 3.2: Remediation Reporting

As a platform engineer,
I want automated reports of form health issues,
So that I can prioritize fixes and track progress over time.

**Acceptance Criteria:**

**Given** health check results exist
**When** the reporting script runs
**Then** it generates:
- Per-pod health summary
- Common failure patterns
- Sites needing immediate attention
- Trend comparison (if previous runs exist)

**Implementation notes:**
- Post-processing of JSONL results
- Could output markdown or HTML report
- Optional: integration with monitoring/alerting

---

## Dependency Graph

```
Epic 1 (POC — COMPLETE ✅):
  Story 1.1 (Bootstrap Script) ──► Story 1.2 (Config Generator)
  │
  └── Delivered: HMAC auth, 5-filter bypass, GFAPI lifecycle,
      notification reroute, config generation, logging

Epic 2 (Scale Enablement — COMPLETE ✅):
  Story 2.1 (JSON Output) ◄── Story 2.2 (Health Check) ◄── Story 2.3 (Health All)
                                                              │
                                                              ▼
                                                    Story 2.4 (Campaign Script)
  │                                                           │
  └── Enables: Batch automation, results aggregation          │
                                                              ▼
Epic 3 (Full-Scale Validation — PLANNED):
  Story 3.1 (Full Coverage) ◄── Story 3.2 (Remediation Reports)
  │
  └── Enables: Platform-wide health baseline, prioritized fixes
```

## Implementation Sequence

**Phase 1: POC** ✅ COMPLETE
1. Story 1.1: Bootstrap script with full bypass logic
2. Story 1.2: Config generator (`--generate-configs`)
3. Verified on staging pod, entries created, LeadsHandler fires

**Phase 2: Scale Enablement** ✅ COMPLETE
1. Story 2.1: Add `--output=json` flag (~30 lines) ✅
2. Story 2.2: Add `--health-check` mode (~40 lines) ✅
3. Story 2.3: Add `--health-check --all` variant (~20 lines) ✅
4. Story 2.4: Campaign script for batch execution (~280 lines) ✅
5. **Gate:** Run health checks across 10 sites/pod, aggregate JSON results

**Phase 3: Full-Scale Validation** — Next sprint
1. Story 3.1: Full coverage health check campaign (`--sample=all`)
2. Story 3.2: Automated remediation reporting
