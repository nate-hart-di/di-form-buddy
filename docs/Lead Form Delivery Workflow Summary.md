# Lead Form Delivery Workflow Summary

**Source Reference**: `Lead Form Delivery` (Confluence)

## Overview

The current process involves manual testing of Gravity Forms on client websites to ensure leads are correctly captured, processed by middleware (Shift, Lead Manifold, etc.), and delivered to the dealer's CRM.

## Key Components

- **Gravity Forms**: Standard form plugin used on sites (Get E-Price, Schedule Test Drive, etc.).
- **Middleware/Providers**:
  - **ELMS (Enterprise Lead Management System)**: Basic check for acceptance (Shift, Autodata, Unite).
  - **Lead Manifold**: Accepts leads and dispatches to other systems; returns Success/Failure.
  - **FordDirect / OneSource**: OEM-specific lead processors.
- **Notification Types**:
  - **ADF (Auto-Lead Data Format)**: XML format for CRMs.
  - **Text Notification**: Standard email alerts.

## Current Manual Workflow

1.  **Preparation**:
    - Identify POC and Service Task.
    - Setup testing sheet (Google Sheets) based on Brand template.
    - Send "Alert email" to POC.
2.  **Execution**:
    - Open live site and backend.
    - For each active form:
      - Retrieve destination emails (ADF/Text) from Form Notifications.
      - **Submit a test lead** using _unique_ names/emails (required to avoid CRM deduplication).
      - **Verify**:
        - **Shift**: Check for generated Lead ID.
        - **Lead Manifold**: Check for "Success" message.
3.  **Completion**:
    - Send confirmation email to POC.
    - Update Service Task in Salesforce.

## Pain Points & Constraints

- **Manual Effort**: Requires human interaction for every form on every site.
- **Timing**: "Live websites within a few hours of launch".
- **ReCaptcha**: Enabled on live sites to prevent spam, blocking simple automated scripts.
- **CRM Pollution**: Test leads must be unique and often go to live CRMs (hence the specific names/emails).
