---
stepsCompleted:
- step-01-validate-prerequisites
- step-02-design-epics
- step-03-create-stories
- step-04-final-validation
inputDocuments:
- _bmad-output/planning-artifacts/prd.md
- _bmad-output/planning-artifacts/architecture.md
---

# pdf2md - Epic Breakdown

## Overview

This document provides the complete epic and story breakdown for pdf2md, decomposing the requirements from the PRD and Architecture into implementable stories.

## Requirements Inventory

### Functional Requirements

- FR1: Detect `X-DI-Test-Auth` header in incoming POST requests.
- FR2: Validate `X-DI-Test-Auth` token/secret against a securely stored environment variable.
- FR3: Refuse the bypass if the header is missing or invalid.
- FR4: Sign and verify tokens using HMAC-SHA256.
- FR5: Intercept `gform_validation` lifecycle.
- FR6: Dynamically remove reCAPTCHA fields from validation.
- FR7: Inject a mock `recaptcha_response` token into the request.
- FR8: Intercept outbound HTTP requests to `google.com/recaptcha/api/siteverify`.
- FR9: Return a successful JSON response locally.
- FR10: Simulate a successful "v3" score (e.g., 0.9).
- FR11: Intercept `gform_entry_is_spam` filter.
- FR12: Force "Not Spam" status for authorized requests.
- FR13: Ensure bypass priority (20+) overrides default DI Spam Filter.
- FR14: Log activation of bypass events (timestamp, form ID).
- FR15: Log specific interception points for troubleshooting.
- FR16: Suppress detailed logs for unauthorized requests.
- FR17: Maintain compatibility with Roxanne tracking.
- FR18: Return standard success HTML/JSON with middleware IDs.
- FR19: Function correctly as an MU-plugin.
- FR20: Deployable globally without theme changes.
- FR21: Retrieve configuration from server environment.

### NonFunctional Requirements

- NFR1: **Token Security**: Use HMAC-SHA256 signatures.
- NFR2: **Failsafe Default**: Default to standard validation if key is missing.
- NFR3: **Privilege Isolation**: Grant no admin access.
- NFR4: **Latency**: Overhead <5ms.
- NFR5: **API Latency**: Local mocking reduces latency by >200ms.
- NFR6: **Data Parity**: Indistinguishable from real leads in DB/CRM.

### Additional Requirements

- **Prototype Phase**: Must support single-file injection into `functions.php`.
- **Production Phase**: Must support MU-Plugin structure (`wp-content/mu-plugins`).
- **Naming Convention**: All global functions/constants must use `di_ab_` prefix.
- **Logging**: Use `[DI-Automation]` prefix for all logs.
- **Compatibility**: Support PHP 7.4+ (Legacy) and 8.1+.

### FR Coverage Map

- FR1, FR2, FR3, FR4, FR21: Epic 1 (Secure Foundation)
- FR5, FR6, FR7: Epic 2 (Lifecycle Bypass)
- FR8, FR9, FR10, FR11, FR12, FR13, FR17, FR18: Epic 3 (Network & Integration)
- FR14, FR15, FR16, FR19, FR20: Epic 4 (Observability & Deployment)

## Epic List

### Epic 1: Secure Foundation & Authentication
**Goal**: Establish the secure "Gatekeeper" that validates requests before any bypass logic runs.
**User Value**: Ensures only authorized automation tools can bypass security.
**FRs covered**: FR1, FR2, FR3, FR4, FR21

### Epic 2: Gravity Forms Lifecycle Interception
**Goal**: Hook into the form submission process to strip frontend captcha requirements.
**User Value**: Prevents "Validation Error" messages for automated submissions.
**FRs covered**: FR5, FR6, FR7

### Epic 3: Network Mocking & Integration Parity
**Goal**: Intercept the outbound Google API call and ensure the lead is processed by downstream systems (Spam Filter, Middleware).
**User Value**: Enables full end-to-end testing without external dependencies or latency.
**FRs covered**: FR8, FR9, FR10, FR11, FR12, FR13, FR17, FR18

### Epic 4: Observability & Deployment Strategy
**Goal**: Provide visibility into bypass events and package the solution for deployment (Prototype & Production).
**User Value**: Allows developers to debug issues and deploy the solution to live sites.
**FRs covered**: FR14, FR15, FR16, FR19, FR20

## Epic 1: Secure Foundation & Authentication

Establish the secure "Gatekeeper" that validates requests before any bypass logic runs.

### Story 1.1: Project Initialization & MU-Plugin Scaffold

As a Developer (Nate),
I want to initialize the project structure as a WordPress MU-Plugin,
So that the bypass logic is deployed globally and cannot be accidentally deactivated.

**Acceptance Criteria:**

**Given** a clean local WordPress environment
**When** I create the `wp-content/mu-plugins/di-automation-bypass/` directory structure
**Then** the plugin should be recognized by WordPress as a "Must-Use" plugin (or manual snippet ready for injection)
**And** the file structure matches the Architecture definition

### Story 1.2: Environment Configuration Loader

As a System Administrator,
I want to configure the bypass secret key via environment variables,
So that I can rotate keys securely without code changes.

**Acceptance Criteria:**

**Given** an environment variable `DI_TEST_AUTOMATION_KEY` is set
**When** the application initializes
**Then** the system should read this value as the active secret
**And** if the env var is missing, it should look for a `DI_TEST_AUTOMATION_KEY` constant
**And** if both are missing, the bypass feature should be disabled (Fail-Safe)

### Story 1.3: HMAC Authentication Engine

As a Security Engineer,
I want to validate the `X-DI-Test-Auth` header using HMAC-SHA256,
So that unauthorized requests cannot spoof the bypass.

**Acceptance Criteria:**

**Given** an incoming request with `X-DI-Test-Auth`
**When** the token signature matches the server-side HMAC calculation
**Then** the request is marked as "Authorized"
**And** if the signature is invalid, the request is marked "Unauthorized"
**And** the validation result is memoized (static variable) to prevent re-calculation

## Epic 2: Gravity Forms Lifecycle Interception

Hook into the form submission process to strip frontend captcha requirements.

### Story 2.1: Mock Token Injection

As a Test Automation Script,
I want the system to automatically inject a fake `recaptcha_response` token into my request,
So that the Gravity Forms plugin doesn't reject my submission for missing fields.

**Acceptance Criteria:**

**Given** an authorized request targeting a Gravity Form
**When** the `init` or `plugins_loaded` hook fires
**Then** `$_POST['recaptcha_response']` and `$_REQUEST['recaptcha_response']` should be populated with a mock token string

### Story 2.2: Dynamic Field Removal

As a Developer,
I want to remove CAPTCHA fields from the Gravity Forms validation object,
So that the backend validation logic doesn't try to verify a non-existent user interaction.

**Acceptance Criteria:**

**Given** a form submission is in progress
**When** `gform_pre_validation` or `gform_validation` fires
**Then** any field of type `captcha` or `recaptcha` should be unset from the `$form` object for authorized requests

## Epic 3: Network Mocking & Integration Parity

Intercept the outbound Google API call and ensure the lead is processed by downstream systems (Spam Filter, Middleware).

### Story 3.1: Network Interceptor

As a Performance Engineer,
I want to intercept outbound calls to Google's API,
So that I eliminate network latency and ensure a consistent success response.

**Acceptance Criteria:**

**Given** an authorized request triggers an HTTP call to `google.com/recaptcha/api/siteverify`
**When** the `pre_http_request` filter runs
**Then** the request should be preempted
**And** a mock JSON response with `success: true` and `score: 0.9` should be returned

### Story 3.2: Spam Filter Override

As a Leads Analyst,
I want my test leads to bypass the Dealer Inspire Spam Filter,
So that they successfully reach the CRM/Middleware.

**Acceptance Criteria:**

**Given** an authorized lead submission
**When** the `gform_entry_is_spam` filter runs
**Then** the function should return `false` (Not Spam)
**And** this filter must run at Priority 20 or higher to override default plugins

## Epic 4: Observability & Deployment Strategy

Provide visibility into bypass events and package the solution for deployment (Prototype & Production).

### Story 4.1: Audit Logging

As a Security Auditor,
I want to see a log entry whenever the bypass is activated,
So that I can monitor for potential abuse.

**Acceptance Criteria:**

**Given** a bypass event occurs (Auth success, Interception, or Mocking)
**When** the event completes
**Then** a line should be written to the PHP error log prefixed with `[DI-Automation]`
**And** detailed logs should NOT be written for unauthorized requests (security noise)

### Story 4.2: Snippet Packaging for Prototype

As a Deployment Engineer,
I want the entire solution packaged as a single PHP snippet,
So that I can easily inject it into a Child Theme `functions.php` for immediate testing.

**Acceptance Criteria:**

**Given** the complete codebase
**When** I copy the code into a `functions.php` file
**Then** it should run without dependency errors or file include failures
