# WordPress REST API JWT Authentication

## Overview

This document covers JWT (JSON Web Token) authentication for WordPress REST API endpoints, including lightweight plugin options and implementation patterns.

## Recommended JWT Plugins

### 1. JWT Auth (by Useful Team)

**Repository:** https://github.com/usefulteam/jwt-auth

**Key Features:**
- Simple, non-complex, easy to use
- Access tokens expire after 10 minutes by default
- Refresh token support (30-day expiration)
- Device-based token rotation for multi-device authentication
- WordPress.org plugin repository available

**Best for:** Projects needing a lightweight, modern JWT implementation with refresh token support.

### 2. JWT Authentication for WP REST API

**Plugin URL:** https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/

**Key Features:**
- Implements industry-standard RFC 7519
- Clear `/token` and `/token/validate` endpoints
- Configurable via wp-config.php
- Optional CORS support
- Active maintenance

**Best for:** Standard JWT implementation with straightforward setup.

## Installation & Configuration

### Step 1: Enable HTTP Authorization Header

Most shared hosting providers disable the HTTP Authorization Header by default. Add to `.htaccess`:

**Standard hosting:**
```apache
RewriteEngine on
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]
```

**WPEngine hosting:**
```apache
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

### Step 2: Configure Secret Key

Edit `wp-config.php` and add before the "That's all, stop editing!" comment:

```php
define('JWT_AUTH_SECRET_KEY', 'your-top-secret-key-here');
define('JWT_AUTH_CORS_ENABLE', true);  // Optional: Enable CORS
```

**Generate a secure key:**
```
https://api.wordpress.org/secret-key/1.1/salt/
```

### Step 3: Install and Activate Plugin

Install the JWT plugin via WordPress admin or WP-CLI:

```bash
wp plugin install jwt-authentication-for-wp-rest-api --activate
```

## API Endpoints (JWT Auth by Useful Team)

The plugin adds a `/jwt-auth/v1` namespace with three endpoints:

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/wp-json/jwt-auth/v1/token` | POST | Generate access token |
| `/wp-json/jwt-auth/v1/token/validate` | POST | Validate existing token |
| `/wp-json/jwt-auth/v1/token/refresh` | POST | Refresh expired token |

## Generating Tokens

### Request

**Endpoint:** `POST /wp-json/jwt-auth/v1/token`

**Parameters:**
- `username` (required): WordPress username
- `password` (required): WordPress password
- `device` (optional): Device identifier for multi-device support

**Example:**
```bash
curl -X POST https://example.com/wp-json/jwt-auth/v1/token \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "password123"
  }'
```

### Success Response

```json
{
  "success": true,
  "statusCode": 200,
  "code": "jwt_auth_valid_credential",
  "message": "Credential is valid",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvZXhhbXBsZS5jb20iLCJpYXQiOjE3MDY4MDAwMDAsIm5iZiI6MTcwNjgwMDAwMCwiZXhwIjoxNzA2ODAwNjAwLCJkYXRhIjp7InVzZXIiOnsiaWQiOiIxIn19fQ.xyz",
    "id": 1,
    "email": "admin@example.com",
    "nicename": "admin",
    "firstName": "John",
    "lastName": "Doe",
    "displayName": "John Doe"
  }
}
```

### Error Response

```json
{
  "success": false,
  "statusCode": 403,
  "code": "jwt_auth_invalid_credentials",
  "message": "Invalid username or password",
  "data": []
}
```

## Using JWT Tokens

### Authorization Header Format

Pass the token as a Bearer token in the `Authorization` header:

```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**Example request:**
```bash
curl https://example.com/wp-json/custom/v1/protected-endpoint \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

## Token Validation

### Request

**Endpoint:** `POST /wp-json/jwt-auth/v1/token/validate`

**Headers:**
- `Authorization: Bearer {token}`

**Example:**
```bash
curl -X POST https://example.com/wp-json/jwt-auth/v1/token/validate \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### Valid Token Response

```json
{
  "success": true,
  "statusCode": 200,
  "code": "jwt_auth_valid_token",
  "message": "Token is valid",
  "data": []
}
```

### Invalid Token Responses

**Expired token:**
```json
{
  "success": false,
  "statusCode": 401,
  "code": "jwt_auth_invalid_token",
  "message": "Expired token",
  "data": []
}
```

**Invalid signature:**
```json
{
  "success": false,
  "statusCode": 401,
  "code": "jwt_auth_invalid_token",
  "message": "Invalid signature",
  "data": []
}
```

## Token Refresh

Access tokens expire after 10 minutes by default. A refresh token is sent as an HTTP-only cookie upon authentication.

### Refresh Access Token

**Endpoint:** `POST /wp-json/jwt-auth/v1/token`

**Requirements:** Must include the refresh token cookie

**Returns:** New access token with extended expiration

### Refresh Token Rotation

**Endpoint:** `POST /wp-json/jwt-auth/v1/token/refresh`

**Requirements:** Must include the refresh token cookie

**Returns:** New access token AND new refresh token

Refresh tokens expire after 30 days and support device-based rotation for multi-device authentication.

## Protecting Custom REST API Endpoints

### Method 1: Using permission_callback

```php
// Register a protected endpoint
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/protected', array(
        'methods'  => 'POST',
        'callback' => 'custom_protected_callback',
        'permission_callback' => 'validate_jwt_token',
    ));
});

// Permission callback to validate JWT
function validate_jwt_token() {
    $auth_header = isset($_SERVER['HTTP_AUTHORIZATION'])
        ? $_SERVER['HTTP_AUTHORIZATION']
        : '';

    if (empty($auth_header)) {
        return new WP_Error(
            'jwt_auth_no_auth_header',
            'Authorization header not found',
            array('status' => 401)
        );
    }

    // Remove "Bearer " prefix
    $token = str_replace('Bearer ', '', $auth_header);

    // Validate token (this depends on the JWT plugin you're using)
    // For jwt-auth plugin, the token is automatically validated
    // if the user is authenticated

    return is_user_logged_in();
}

function custom_protected_callback($request) {
    return array(
        'success' => true,
        'message' => 'Access granted to protected endpoint',
        'user_id' => get_current_user_id(),
    );
}
```

### Method 2: Manual Token Validation in Callback

```php
function custom_route_callback(WP_REST_Request $request) {
    // Get Authorization header
    $headers = $request->get_headers();
    $auth_header = isset($headers['authorization'][0])
        ? $headers['authorization'][0]
        : '';

    if (empty($auth_header)) {
        return new WP_Error(
            'no_auth',
            'Authorization header missing',
            array('status' => 401)
        );
    }

    // Remove "Bearer " prefix
    $token = str_replace('Bearer ', '', $auth_header);

    // Validate token using Firebase JWT library or plugin function
    try {
        $decoded = JWT::decode($token, JWT_AUTH_SECRET_KEY, array('HS256'));
        $user_id = $decoded->data->user->id;

        // Process request with authenticated user
        return array(
            'success' => true,
            'user_id' => $user_id,
        );

    } catch (Exception $e) {
        return new WP_Error(
            'jwt_auth_invalid_token',
            $e->getMessage(),
            array('status' => 401)
        );
    }
}
```

### Method 3: Using JWT Auth Plugin's Built-in Validation

The JWT Auth plugin automatically handles authentication. If the token is valid, `get_current_user_id()` and `is_user_logged_in()` will work as expected:

```php
function protected_endpoint_callback($request) {
    // Token is automatically validated by the plugin
    if (!is_user_logged_in()) {
        return new WP_Error(
            'unauthorized',
            'Invalid or missing token',
            array('status' => 401)
        );
    }

    $user_id = get_current_user_id();

    return array(
        'success' => true,
        'message' => 'Authenticated successfully',
        'user_id' => $user_id,
    );
}
```

## Available Filter Hooks

The JWT Auth plugin provides several filters for customization:

### jwt_auth_expire

Modify token expiration time (default: 10 minutes):

```php
add_filter('jwt_auth_expire', function($expire) {
    return time() + (DAY_IN_SECONDS * 7); // 7 days
});
```

### jwt_auth_cors_allow_headers

Modify allowed CORS headers:

```php
add_filter('jwt_auth_cors_allow_headers', function($headers) {
    $headers[] = 'X-Custom-Header';
    return $headers;
});
```

### jwt_auth_authorization_header

Change the Authorization header key (default: `HTTP_AUTHORIZATION`):

```php
add_filter('jwt_auth_authorization_header', function($header) {
    return 'HTTP_X_AUTHORIZATION';
});
```

### jwt_auth_iss

Change token issuer (default: site URL):

```php
add_filter('jwt_auth_iss', function($iss) {
    return 'https://custom-issuer.com';
});
```

## Common Error Codes

| Code | Status | Description |
|------|--------|-------------|
| `jwt_auth_bad_config` | 500 | Secret key not configured |
| `jwt_auth_no_auth_header` | 401 | Authorization header missing |
| `jwt_auth_invalid_token` | 401 | Invalid signature or expired token |
| `jwt_auth_invalid_credential` | 403 | Invalid username/password |
| `jwt_auth_user_not_found` | 401 | User doesn't exist |

## Security Best Practices

1. **Use strong secret keys** - Generate keys from the WordPress salt generator
2. **Use HTTPS** - Always use HTTPS in production to prevent token interception
3. **Short token expiration** - Keep access token expiration short (10-15 minutes)
4. **Rotate refresh tokens** - Use device-based refresh token rotation
5. **Validate on every request** - Always validate tokens on protected endpoints
6. **Store tokens securely** - On the client side, use secure storage (e.g., HTTP-only cookies for web)
7. **Don't log tokens** - Avoid logging authorization headers or tokens

## Testing JWT Authentication

### Test token generation:
```bash
curl -X POST http://localhost/wp-json/jwt-auth/v1/token \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}'
```

### Test protected endpoint:
```bash
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
curl http://localhost/wp-json/custom/v1/protected \
  -H "Authorization: Bearer $TOKEN"
```

### Test token validation:
```bash
curl -X POST http://localhost/wp-json/jwt-auth/v1/token/validate \
  -H "Authorization: Bearer $TOKEN"
```

## Gotchas & Limitations

1. **Shared hosting header issues** - Some hosts block the Authorization header; use .htaccess rules
2. **Token size** - JWT tokens can be large; consider payload size
3. **Stateless nature** - Can't revoke tokens before expiration (use short expiration times)
4. **User changes** - Tokens remain valid even if user password changes (until expiration)
5. **Plugin conflicts** - Some security plugins may block JWT endpoints
6. **REST API disabled** - JWT won't work if REST API is disabled

## References

- [JWT Auth GitHub Repository](https://github.com/usefulteam/jwt-auth)
- [JWT Authentication for WP REST API Plugin](https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/)
- [WP-API JWT Auth GitHub](https://github.com/WP-API/jwt-auth)
- [WordPress REST API Authentication](https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/)
- [JWT RFC 7519 Specification](https://tools.ietf.org/html/rfc7519)
