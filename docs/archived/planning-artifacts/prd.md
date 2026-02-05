---
stepsCompleted:
- step-01-init
- step-02-discovery
- step-03-success
- step-04-journeys
- step-05-domain
- step-06-innovation
- step-07-project-type
- step-08-scoping
- step-09-functional
- step-10-nonfunctional
- step-11-polish
inputDocuments:
- _bmad-output/antigravity-chats/Finalizing reCAPTCHA Bypass.md
- docs/knowledge/recaptcha-bypass-poc/Automation Ideas.md
- docs/knowledge/recaptcha-bypass-poc/Lead Form Delivery Workflow Summary.md
- docs/knowledge/recaptcha-bypass-poc/Meeting Action Plan.md
- docs/knowledge/recaptcha-bypass-poc/Meeting Proposal Message.md
- docs/knowledge/recaptcha-bypass-poc/Multi-Agent Brainstorming Session.md
- docs/knowledge/recaptcha-bypass-poc/Technical Feasibility Brief.md
- docs/knowledge/recaptcha-bypass-poc/previous_implementation_plan.md
- docs/knowledge/recaptcha-bypass-poc/previous_session_task.md
- docs/knowledge/recaptcha-bypass-poc/walkthrough.md
- docs/knowledge/recaptcha-bypass-poc/Finalizing reCAPTCHA Bypass.md
classification:
  projectType: api_backend
  domain: automotive
  complexity: medium
  projectContext: brownfield
workflowType: prd
---

# Product Requirements Document - reCAPTCHA Bypass for Automated Lead Testing

**Author:** Nate
**Date:** 2026-01-26

## Executive Summary

This project implements a secure, platform-wide bypass mechanism for reCAPTCHA and spam filters on the Dealer-Inspire platform. By utilizing a signed security header (`X-DI-Test-Auth`), internal automation tools can verify lead form delivery on live production sites without manual intervention or compromising public-facing security.

## Success Criteria

### Measurable Outcomes
*   **Bypass Success Rate:** >99.9% of authorized automated submissions pass reCAPTCHA.
*   **Security Integrity:** 0 unauthorized uses of the bypass mechanism.
*   **Production Parity:** 0 instances where the bypass works but a real user would have failed due to configuration errors.

### Stakeholder Success
*   **Leads Team:** Ability to execute automated form verification scripts across 1,000+ live sites without reCAPTCHA interference.
*   **Business Operations:** Reduce manual testing overhead and accelerate site launch timelines.
*   **CRM Management:** Ensure test leads are accurately flagged or routed to prevent dealer CRM pollution.

## User Journeys

### 1. Implementation & Deployment (Nate)
*   **Context**: Nate needs to deploy a scalable solution for lead testing across the platform.
*   **Journey**: Nate implements the bypass logic as a Must-Use (MU) plugin. He configures a global secret key in the server environment.
*   **Outcome**: The bypass is active platform-wide without requiring individual theme modifications.

### 2. Automated Testing Suite (Primary User)
*   **Context**: A Leads Team Analyst triggers a verification suite for a high-priority launch.
*   **Journey**: The automation suite includes the `X-DI-Test-Auth` header in its POST requests. The system detects the header, mocks a successful reCAPTCHA response, and processes the lead.
*   **Outcome**: The suite verifies the full delivery chain (Shift/Manifold) in seconds, receiving a real Lead ID in the response.

### 3. Data Integrity Monitoring (Stakeholder)
*   **Context**: A CRM Manager reviews monthly lead reports for a dealership.
*   **Journey**: The manager observes "Synthetic" leads in the system logs.
*   **Outcome**: These leads are easily filtered from ROI reports, preserving data integrity while maintaining 100% confidence in form uptime.

## Project Scoping & Roadmap

### Phase 1: MVP (Minimum Viable Product)
*   **Core Logic**: `X-DI-Test-Auth` header validation using HMAC-SHA256.
*   **Interception**: Hook into `gform_validation` and `gform_entry_is_spam` (Priority 20+).
*   **Mocking**: Local mocking of `google.com/recaptcha` API responses to ensure parity and performance.
*   **Observability**: Detailed error logging for authorized bypass attempts to assist troubleshooting.

### Phase 2: Growth (Scale & Management)
*   **Platform Rollout**: Deployment as a global MU-plugin across the DI ecosystem.
*   **Secret Management**: Secure UI for rotating the automation secret key without code deployments.
*   **Test Dashboard**: Real-time monitoring of bypass success rates and health status.

### Phase 3: Vision (Advanced Automation)
*   **Synthetic Routing**: Intercept leads before CRM delivery and route to a dedicated sandbox endpoint.
*   **Platform Health-Check**: Nightly automated sweeps of every live site to verify lead-readiness.

## Domain & Technical Requirements

### Compliance & Regulatory
*   **PII Protection**: Automated leads must adhere to internal data privacy and retention policies.
*   **Audit Trail**: Log every authorized bypass attempt including timestamp, source, and form ID.

### Project-Type Specifics (API/Backend)
*   **Interception Priority**: Must hook into the WordPress lifecycle early (`init` or `plugins_loaded`) to satisfy all security plugins.
*   **Mock API Response**: Returns a 200 OK with a successful "v3" score (e.g., 0.9) locally, eliminating outbound latency.
*   **Symmetric Authentication**: Backend verifies `X-DI-Test-Auth` against a server environment variable (`DI_TEST_AUTOMATION_KEY`).

### Risk Mitigation
*   **Production Drift**: Use version-agnostic API mocks rather than UI-specific selectors to ensure compatibility across reCAPTCHA/Plugin updates.
*   **Credential Leakage**: Mitigated by JWT/Symmetric encryption and a planned rotation strategy.

## Functional Requirements

### 1. Authentication
*   **FR1:** Detect `X-DI-Test-Auth` header in POST requests.
*   **FR2:** Validate token against a secure server-side environment variable.
*   **FR3:** Refuse bypass for invalid or missing headers.

### 2. Interception & Mocking
*   **FR4:** Intercept `gform_validation` to remove reCAPTCHA requirements for authorized requests.
*   **FR5:** Inject a mock `recaptcha_response` token into the request payload.
*   **FR6:** Intercept outbound HTTP requests to Google reCAPTCHA and return a local "Success" response.
*   **FR7:** Force `gform_entry_is_spam` to `false` (Priority 20+) for authorized requests.

### 3. Observability & Integration
*   **FR8:** Log bypass activation events with granular detail for troubleshooting.
*   **FR9:** Maintain compatibility with Roxanne tracking and middleware (Shift/Manifold) response schemas.
*   **FR10:** Capability for global deployment as an MU-plugin without site-specific code.

## Non-Functional Requirements

### Security & Reliability
*   **Token Security**: Use HMAC-SHA256 signatures to prevent header spoofing.
*   **Failsafe Default**: System must default to standard reCAPTCHA validation if the secret key is missing.
*   **Privilege Isolation**: Bypass grants no administrative access beyond skipping security filters.

### Performance & Integration
*   **Latancy Overhead**: Total processing overhead for header validation must be <5ms.
*   **API Latency Elimination**: Local mocking must reduce reCAPTCHA verification time by >200ms compared to outbound requests.
*   **Data Parity**: Automated leads must trigger all standard hooks to ensure parity in database and CRM routing.
