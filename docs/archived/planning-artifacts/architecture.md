---
stepsCompleted:
- step-01-init
- step-02-context
- step-03-starter
- step-04-decisions
- step-05-patterns
- step-06-structure
- step-07-validation
- step-08-complete
workflowType: architecture
project_name: pdf2md
user_name: Nate
date: 2026-01-26
lastStep: 8
status: complete
completedAt: 2026-01-26T20:15:00Z
inputDocuments:
- _bmad-output/planning-artifacts/prd.md
---

# Architecture Decision Document

_This document builds collaboratively through step-by-step discovery. Sections are appended as we work through each architectural decision together._

## Project Context Analysis

### Requirements Overview

**Functional Requirements:**
The architecture must support three core capability areas:
1.  **Request Authentication:** Validating the `X-DI-Test-Auth` header using symmetric signatures.
2.  **Lifecycle Interception:** Hooking into WordPress and Gravity Forms at multiple stages to strip captcha requirements and inject fake tokens.
3.  **Network-Level Mocking:** Intercepting outbound HTTP requests to Google's reCAPTCHA API to return local success payloads.

**Non-Functional Requirements:**
- **Security:** HMAC-SHA256 signature verification is non-negotiable to prevent unauthorized lead injection.
- **Performance:** Local mocking is required not just for consistency, but to eliminate the ~200ms+ latency of the real Google API.
- **Integrity:** The bypass must preserve the "Active" status of leads in the database and Roxanne tracking.

**Scale & Complexity:**
- Primary domain: API / Backend (WordPress Platform)
- Complexity level: Medium
- Estimated architectural components: 4 (Auth Engine, Interception Layer, API Mocker, Observability Logger)

### Technical Constraints & Dependencies
- **WordPress Environment**: Must operate within the constraints of the DI platform's WordPress multi-site/child-theme architecture.
- **Plugin Dependencies**: Relies on hooks provided by Gravity Forms and the DI reCAPTCHA plugin.
- **Security Protocols**: Must use server-side environment variables for secret management.

### Cross-Cutting Concerns Identified
- **Logging & Audit**: Centralized logging of bypass events for security and troubleshooting.
- **Scalability**: Implementation as a Must-Use (MU) plugin to ensure global activation across 1,000+ sites.
- **Error Handling**: Failsafe mechanisms to ensure the site remains secure if the bypass configuration is missing.

## Starter Template Evaluation

### Primary Technology Domain

**API / Backend (WordPress Integration)** based on project requirements analysis.

### Starter Options Considered

1.  **WP-CLI Scaffold Plugin**: Standard and lightweight, but lacks advanced OOP structure and Composer integration by default.
2.  **DevinVinson/WordPress-Plugin-Boilerplate**: Very popular and well-documented, but might be overkill for a focused bypass logic and requires manual conversion to MU-plugin.
3.  **Custom MU-Plugin Starter (Target Architecture)**: A focused, object-oriented bootstrap structure that prioritizes early lifecycle execution and secure HMAC validation.

### Selected Starter: Custom MU-Plugin Starter (Target)

**Rationale for Selection:**
This approach provides the most control over the WordPress lifecycle, which is critical for a "nuclear" bypass that must run before security plugins (like DI Spam). It ensures the code is globally active and cannot be deactivated by users.

**Critical Constraint - Prototyping Phase:**
Before deploying this MU-plugin architecture, the solution **MUST** be proven on a live/staging site using a single-file injection into the active theme's `functions.php`. This proves the logic works in the real production environment before requesting platform-level deployment.

**Initialization Command (Target Architecture):**

```bash
# Create the directory structure
mkdir -p wp-content/mu-plugins/di-automation-bypass/src
cd wp-content/mu-plugins/di-automation-bypass

# Initialize Composer for JWT and Testing
composer init --name="dealer-inspire/di-automation-bypass" \
              --require="lcobucci/jwt:^5.0" \
              --require-dev="phpunit/phpunit:^10.0" \
              --require-dev="wp-coding-standards/wpcs:^3.0"
```

**Architectural Decisions Provided by Starter:**

**Language & Runtime:**
PHP 8.1+, Namespaced (`DealerInspire\AutomationBypass`), and Strict Types.

**Code Organization:**
*   **Prototype Phase:** Single-file logic injected into `themes/active-theme/functions.php`.
*   **Production Phase:** `wp-content/mu-plugins/di-automation-bypass.php` (Bootstrap) -> `src/` (Logic).

**Testing Framework:**
PHPUnit configured for unit testing the Auth Engine independently of WordPress.

**Development Experience:**
Composer-driven workflow with linting (WPCS) and autoloading (PSR-4).

**Note:** Project initialization using this command should be the first implementation story.

## Core Architectural Decisions

### Decision Priority Analysis

**Critical Decisions (Block Implementation):**
*   HMAC Library Selection (Native PHP vs Library)
*   HTTP Interception Strategy
*   Secret Key Management

### Authentication & Security

*   **Category:** HMAC Library
*   **Decision:** **Native PHP `hash_hmac`**
*   **Rationale:** Provides zero-dependency, ultra-fast performance (<1ms) which is critical for the "Zero Latency" requirement. Perfect for the single-file prototype phase. Can be upgraded to `lcobucci/jwt` in Phase 2 if complex claims are needed.
*   **Affects:** Auth Engine Component

### API & Communication Patterns

*   **Category:** HTTP Interception Strategy
*   **Decision:** **`pre_http_request` Filter**
*   **Rationale:** The "Nuclear Option" ensures we catch requests from *any* version of the reCAPTCHA plugin (v2/v3) or even custom implementations. It matches the URL string `google.com/recaptcha` strictly to avoid side effects.
*   **Affects:** API Mocker Component

### Infrastructure & Deployment

*   **Category:** Secret Key Management
*   **Decision:** **Hybrid Approach (Env Var + Constant)**
*   **Rationale:** Checks `getenv('DI_AUTOMATION_SECRET_KEY')` first for containerized environments, falling back to a `define()` constant in `wp-config.php` for standard hosting.
*   **Affects:** Configuration Module

### Decision Impact Analysis

**Implementation Sequence:**
1.  Implement `hash_hmac` validation logic.
2.  Implement `pre_http_request` interception with URL matching.
3.  Implement Hybrid Config loader.
4.  Wrap in `functions.php` prototype for testing.
5.  Refactor into MU-Plugin structure for Phase 2.

**Cross-Component Dependencies:**
The **Interception Layer** depends entirely on the **Auth Engine** returning `true`. If Auth fails, Interception must *never* run (Fail-Safe).

## Implementation Patterns & Consistency Rules

### Pattern Categories Defined

**Critical Conflict Points Identified:**
3 areas where AI agents could make different choices (Naming, Logging, State Management).

### Naming Patterns

**WordPress Coding Standards (WPCS):**
*   **Prefix:** `di_ab_` (e.g., `di_ab_is_authorized()`).
*   **Namespace:** `DealerInspire\AutomationBypass`.
*   **Global Variables:** Prohibited. Use static methods or filters.

### Format Patterns

**Logging Patterns:**
*   **Tag:** All logs must start with `[DI-Automation]`.
*   **Detail:** Include context (e.g., `[DI-Automation] Bypass activated for Form ID: 35`).

### Communication Patterns

**Internal State Management:**
*   **Pattern:** **Static Memoization**. The result of the `X-DI-Test-Auth` validation must be calculated once and stored in a static variable to avoid repeated HMAC overhead during the same request.

### Enforcement Guidelines

**All AI Agents MUST:**
*   Adhere to WordPress PHP Coding Standards (WPCS).
*   Use the `di_ab_` prefix for all global symbols.
*   Ensure the "Fail-Safe" default (Bypass is OFF if key is missing).

### Pattern Examples

**Good Example:**
```php
function di_ab_is_authorized() {
    static $is_auth = null;
    if ($is_auth === null) {
        $is_auth = di_ab_verify_hmac();
    }
    return $is_auth;
}
```

**Anti-Pattern:**
```php
// Bad: No prefix, repeats calculation
function check_header() {
    return $_SERVER['HTTP_X_DI_TEST_AUTH'] === 'secret'; 
}
```

## Project Structure & Boundaries

### Complete Project Directory Structure (Architecture: Theme Snippet)

**Goal:** Create a "Snippet Library" that can be injected into any Child Theme `functions.php`.

```
di-automation-bypass/
├── README.md (Deployment Instructions)
├── snippets/
│   └── functions-injection.php (The Production Code)
├── tests/
│   └── functions-test.php (Unit tests for the snippet)
└── .gitignore
```

### Architectural Boundaries

**Code Boundary:**
The snippet must be self-contained. It cannot rely on `vendor/` autoloading because Child Themes don't support Composer by default.
*   **Constraint:** All logic must fit in `functions-injection.php` without external dependencies.

**Integration Boundary:**
*   **Input:** `$_SERVER['HTTP_X_DI_TEST_AUTH']`
*   **Output:** Modified global state (`$_POST['recaptcha_response']`, `pre_http_request` return).

### Requirements to Structure Mapping

**Feature: Core Logic**
*   FR1-FR10 -> `snippets/functions-injection.php`

**Feature: Deployment**
*   FR19 (Scalability) -> Resolved by "Copy-Paste" strategy into Child Theme (Manual but allowed).

### Integration Points

**Internal Communication:**
The snippet uses `add_filter()` and `add_action()` to communicate with WordPress Core. It uses `static` variables for internal state.

**Data Flow:**
1.  Request -> `init` hook -> Check Header -> Set Static Auth State.
2.  `pre_http_request` hook -> Check Auth State -> Return Mock Response.
3.  `gform_validation` hook -> Check Auth State -> Remove Captcha Field.

## Architecture Validation Results

### Coherence Validation ✅
*   **Decision Compatibility:** The "Snippet" architecture perfectly matches the "No Plugins" constraint.
*   **Pattern Consistency:** WordPress Coding Standards are enforced via naming/structure patterns.
*   **Structure Alignment:** The single-file structure enables the required "Copy-Paste Deployment."

### Requirements Coverage Validation ✅
*   **Functional Requirements:** All 10 FRs are mapped to the single-file snippet.
*   **Non-Functional Requirements:** Security and Performance are handled via native PHP functions.

### Implementation Readiness Validation ✅
*   **Decision Completeness:** All critical decisions (Auth, Interception, Config) are made.
*   **Structure Completeness:** The file structure is simple and well-defined.
*   **Pattern Completeness:** Naming and logging patterns are explicit.

### Architecture Readiness Assessment

**Overall Status:** READY FOR IMPLEMENTATION

**Confidence Level:** HIGH (Validated via Party Mode)

**Key Strengths:**
*   **Resilient:** Works on legacy Gravity Forms (1.9) and modern versions.
*   **Platform-Safe:** Zero new plugins required; respects platform stability concerns.
*   **Performant:** Ultra-low latency via native PHP and local mocking.

## Architecture Completion Summary

### Workflow Completion

**Architecture Decision Workflow:** COMPLETED ✅
**Total Steps Completed:** 8
**Date Completed:** 2026-01-26
**Document Location:** _bmad-output/planning-artifacts/architecture.md

### Final Architecture Deliverables

**📋 Complete Architecture Document**
* All architectural decisions documented with specific versions.
* Implementation patterns ensuring AI agent consistency.
* Project structure with all files and directories.
* Requirements to architecture mapping.
* Validation confirming coherence and completeness.

**🏗️ Implementation Ready Foundation**
* 3 critical architectural decisions made.
* 4 implementation patterns defined.
* 1 major architectural component (The Snippet) specified.
* 10 requirements fully supported.

### Implementation Handoff

**For AI Agents:**
This architecture document is your complete guide for implementing reCAPTCHA Bypass. Follow all decisions, patterns, and structures exactly as documented.

**First Implementation Priority:**
Implement the `functions-injection.php` snippet logic following the established WPCS naming and logging patterns.

### Quality Assurance Checklist

**✅ Architecture Coherence**
- [x] All decisions work together without conflicts
- [x] Technology choices are compatible
- [x] Patterns support the architectural decisions
- [x] Structure aligns with all choices

**✅ Requirements Coverage**
- [x] All functional requirements are supported
- [x] All non-functional requirements are addressed
- [x] Cross-cutting concerns are handled
- [x] Integration points are defined

**✅ Implementation Readiness**
- [x] Decisions are specific and actionable
- [x] Patterns prevent agent conflicts
- [x] Structure is complete and unambiguous
- [x] Examples are provided for clarity

---

**Architecture Status:** READY FOR IMPLEMENTATION ✅

**Next Phase:** Begin implementation using the architectural decisions and patterns documented herein.
