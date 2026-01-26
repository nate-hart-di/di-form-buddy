# JWT Bypass for reCAPTCHA - Final Implementation ✅

## Summary

Successfully implemented and verified a bypass for reCAPTCHA validation and DI Spam filters on Gravity Forms. This allows authorized automation bots to submit leads by sending a custom security header.

## Final Implementation Details

The bypass logic is located in the theme's [functions.php](file:///Users/nathanhart/di-websites-platform/dealer-themes/heiserautomaxxcenterofsturgeonbay/functions.php). It performs the following actions for authorized requests:

1.  **Injects a fake `recaptcha_response`**: Satisfies the initial presence check of the DI reCAPTCHA plugin.
2.  **Mocks the Google API**: Intercepts the HTTP request to `google.com/recaptcha` and returns a successful response locally.
3.  **Removes Captcha Fields**: Dynamically unsets captcha fields from Gravity Forms to prevent client-side/server-side validation errors.
4.  **Bypasses Spam Filters**: Forces `gform_entry_is_spam` to return `false` (Priority 20 to override DI Spam Filter).

## Instructions for Leads Team

To bypass reCAPTCHA and spam checks, your automation tool must include the following header in every POST request:

**Header Name**: `X-DI-Test-Auth`  
**Secret Value**: `test-automation-key-2026`

### Example Usage (cURL)

```bash
curl -X POST "http://jeffbelzersautogroup.localhost/contact-us/" \
  -H "X-DI-Test-Auth: test-automation-key-2026" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "is_submit_2=1&gform_submit=2&input_1=FirstName&input_2=LastName&input_3=1234567890&input_4=test@example.com"
```

## Lead Verification

### Database & Roxanne

Submissions with the header will be processed as **Active** leads. You can verify this in the WordPress Admin under **Forms > Entries**.
The `lead_processed` event is also triggered and tracked by the Roxanne plugin, ensuring parity with real user leads.

### Email Notifications

> [!NOTE]
> Emails are triggered by the system but may not be delivered in the local development environment due to lack of a mail relay. In production, these will be sent to the configured recipients as usual.

### Logs Proof (Docker)

You can confirm the bypass is active by watching the logs:

```
[DI-Automation] Authorization header detected - Injected fake recaptcha_response token
[DI-Automation] Bypassing reCAPTCHA: removed captcha field from form 2
[DI-Automation] Intercepted reCAPTCHA API request - returning mock success
```

## Recording

![Recaptcha Keys Configured](/Users/nathanhart/.gemini/antigravity/brain/5c2998e9-0922-4dd2-aed0-a3de8bb523de/recaptcha_keys_configured_1769211691320.png)
