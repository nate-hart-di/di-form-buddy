# reCAPTCHA Bypass for Automated Leads

This plan outlines the final implementation of the reCAPTCHA and spam check bypass for the Leads Team's automation tools.

## User Review Required

> [!IMPORTANT]
> The bypass is secured using a custom HTTP header `X-DI-Test-Auth`. The automation tools **must** send this header with the secret value configured in the environment.

> [!WARNING]
> This bypass overrides all reCAPTCHA and DI Spam filters. Ensure that the secret key is kept secure and not exposed to the public.

## Proposed Changes

### [Component] Website Theme (Heiser Automaxx Center of Sturgeon Bay)

#### [MODIFY] [functions.php](file:///Users/nathanhart/di-websites-platform/dealer-themes/heiserautomaxxcenterofsturgeonbay/functions.php)

Summary of changes:

1.  **Clean up debug logs**: Refine the `error_log` messages to be more informative for production monitoring.
2.  **Ensure prioritized execution**: Confirm that spam and reCAPTCHA status hooks remain at priority 20 to override default plugin behavior.

## Verification Plan

### Automated Tests

The following `curl` command can be used to verify the bypass:

```bash
curl -i -X POST "http://jeffbelzersautogroup.localhost/contact-us/" \
  -H "X-DI-Test-Auth: test-automation-key-2026" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "is_submit_2=1&gform_submit=2&input_1=Test&input_2=Lead&input_3=1234567890&input_4=test@example.com&input_5=Automation+Test"
```

**Success Criteria**:

- HTTP 200 Response.
- Body contains `"status":"active"` or a success confirmation.
- No "reCAPTCHA error" messages in the response HTML.

### Manual Verification

1.  Access the WordPress Admin.
2.  Navigate to **Forms > Entries**.
3.  Verify that the lead submitted via the automation header appears as "Active" (not spam).
4.  Check the Docker logs for:
    - `[DI-Automation] Injected fake recaptcha_response token`
    - `[DI-Automation] Intercepted reCAPTCHA API request - returning mock success`
    - `[DI-Automation] JWT bypass activated - captcha removed from form 2`
