# Technical Feasibility Brief: JWT Bypass for ReCaptcha

## Executive Summary

**Is this real?** Yes. The proposed solution uses **JSON Web Tokens (JWT)** and **HTTP Headers**, which are the global standard for secure API authentication (used by Google, Stripe, AWS, etc.).

**Is it feasible?** **High**. The implementation effort is Low-to-Medium (approx. 1-2 days for a senior dev).

## 1. Industry Standardization

You are **not** proposing something new or experimental.

- **The Concept**: "Service-to-Service Authentication."
- **The Standard**: [RFC 7519 (JSON Web Token)](https://tools.ietf.org/html/rfc7519).
- **Analogy**: This is exactly how a mobile app logs you in. The app sends a token (badge) with every request, and the server validates it. We are simply applying this standard pattern to the specific use case of bypassing a ReCaptcha.

## 2. Implementation Logic (The "How")

Since your sites use **WordPress** and **Gravity Forms**, the implementation is straightforward because Gravity Forms is designed to be extensible.

### The Workflow:

1.  **The Hook**: WordPress has a "filter" called `gform_validation`. This runs _before_ the form is saved.
2.  **The Check**: Inside this filter, we write 5 lines of code:
    - "Does the request have a header called `X-DI-Test-Auth`?"
    - "Is the token inside it valid (signed by us)?"
3.  **The Bypass**: If Yes -> Return "True" immediately, skipping the ReCaptcha validation logic.

### Pseudo-Code (For the Developers):
‰
```php
// In functions.php or a custom plugin
add_filter('gform_validation', function ($result) {
  $token = $_SERVER['HTTP_X_DI_TEST_AUTH'] ?? null;
  if ($token && isValidJwt($token)) {
    // 1. Remove ReCaptcha field from validation list
    // 2. Add a hidden "Is_Test" flag to the entry
    return $result;
  }
  // ... otherwise run normal ReCaptcha Check
  return $result;
});
```

## 3. Risk Assessment

- **Security Risk**: **Very Low**. Identifying the header name isn't enough; an attacker needs the **Private Key** to sign a valid token. As long as you keep the Private Key safe (like a password), it is secure.
- **Maintenance**: **Low**. Once the plugin is installed, it requires zero maintenance unless you change your encryption keys.

## 4. Conclusion for the Meeting

When you present this, you can say:

> "We're proposing a standard **Service-to-Service Authentication pattern** using **Signed JWTs**. It's the same security model used by modern APIs. We can implement it cleanly using Gravity Forms' existing validation hooks without touching the core website code."
