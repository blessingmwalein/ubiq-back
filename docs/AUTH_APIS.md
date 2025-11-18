# Authentication APIs

Complete authentication and authorization endpoints for the UBIQ Entertainment Platform.

---

## Table of Contents

1. [User Registration](#user-registration)
2. [User Login](#user-login)
3. [User Logout](#user-logout)
4. [Email Verification](#email-verification)
5. [Password Reset](#password-reset)
6. [Two-Factor Authentication](#two-factor-authentication)

---

## User Registration

Register a new user account.

### Endpoint
```
POST /api/register
```

### Headers
```
Content-Type: application/json
Accept: application/json
```

### Request Body

```json
{
  "name": "John Doe",
  "email": "john.doe@example.com",
  "password": "SecurePassword123!",
  "password_confirmation": "SecurePassword123!"
}
```

### Request Parameters

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | Yes | User's full name (min: 2, max: 255 chars) |
| email | string | Yes | Valid email address (unique) |
| password | string | Yes | Password (min: 8 chars, must include uppercase, lowercase, number) |
| password_confirmation | string | Yes | Must match password |

### Success Response (201 Created)

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john.doe@example.com",
      "email_verified_at": null,
      "created_at": "2025-11-16T10:30:00.000000Z",
      "updated_at": "2025-11-16T10:30:00.000000Z"
    },
    "token": "1|abc123def456ghi789jkl012mno345pqr678stu901vwx234yz"
  },
  "message": "Registration successful"
}
```

### Error Responses

**422 Unprocessable Entity** (Validation Failed)
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Frontend Example

```javascript
async function register(userData) {
  try {
    const response = await fetch('https://your-domain.com/api/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(userData)
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Store token
      localStorage.setItem('auth_token', data.data.token);
      localStorage.setItem('user', JSON.stringify(data.data.user));
      
      // Redirect to dashboard
      window.location.href = '/dashboard';
    } else {
      // Handle errors
      displayErrors(data.errors);
    }
  } catch (error) {
    console.error('Registration failed:', error);
  }
}

// Usage
register({
  name: 'John Doe',
  email: 'john@example.com',
  password: 'SecurePass123!',
  password_confirmation: 'SecurePass123!'
});
```

---

## User Login

Authenticate existing user and receive access token.

### Endpoint
```
POST /api/login
```

### Headers
```
Content-Type: application/json
Accept: application/json
```

### Request Body

```json
{
  "email": "john.doe@example.com",
  "password": "SecurePassword123!",
  "remember": true
}
```

### Request Parameters

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| email | string | Yes | User's email address |
| password | string | Yes | User's password |
| remember | boolean | No | Keep user logged in (default: false) |

### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john.doe@example.com",
      "email_verified_at": "2025-11-16T10:35:00.000000Z",
      "subscription_status": "active",
      "subscription_end": "2025-12-16T10:35:00.000000Z",
      "created_at": "2025-11-16T10:30:00.000000Z",
      "updated_at": "2025-11-16T10:35:00.000000Z"
    },
    "token": "2|xyz987wvu654tsr321qpo098nml765kji432hgf109edc876ba",
    "profiles": [
      {
        "id": 1,
        "name": "John's Profile",
        "avatar": "/storage/avatars/default.png",
        "is_kids": false,
        "is_default": true
      }
    ]
  },
  "message": "Login successful"
}
```

### Error Responses

**401 Unauthorized** (Invalid Credentials)
```json
{
  "success": false,
  "message": "Invalid credentials",
  "errors": {
    "email": ["These credentials do not match our records."]
  }
}
```

**403 Forbidden** (Email Not Verified)
```json
{
  "success": false,
  "message": "Email address not verified",
  "errors": {
    "email": ["Please verify your email address before logging in."]
  }
}
```

### Frontend Example

```javascript
async function login(credentials) {
  try {
    const response = await fetch('https://your-domain.com/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(credentials)
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Store authentication data
      localStorage.setItem('auth_token', data.data.token);
      localStorage.setItem('user', JSON.stringify(data.data.user));
      localStorage.setItem('profiles', JSON.stringify(data.data.profiles));
      
      // Set default authorization header
      axios.defaults.headers.common['Authorization'] = `Bearer ${data.data.token}`;
      
      return data.data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Login failed:', error);
    throw error;
  }
}

// Usage
login({
  email: 'john@example.com',
  password: 'SecurePass123!',
  remember: true
});
```

---

## User Logout

Revoke current authentication token.

### Endpoint
```
POST /api/logout
```

### Headers
```
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### Frontend Example

```javascript
async function logout() {
  try {
    const token = localStorage.getItem('auth_token');
    
    const response = await fetch('https://your-domain.com/api/logout', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    
    // Clear local storage
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    localStorage.removeItem('profiles');
    
    // Redirect to login
    window.location.href = '/login';
  } catch (error) {
    console.error('Logout failed:', error);
  }
}
```

---

## Email Verification

### Send Verification Email

Request a new verification email.

#### Endpoint
```
POST /api/email/verification-notification
```

#### Headers
```
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Verification email sent"
}
```

### Verify Email

Verify email address using the token from email link.

#### Endpoint
```
GET /api/email/verify/{id}/{hash}
```

#### URL Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| id | integer | User ID |
| hash | string | Verification hash |

#### Query Parameters

| Parameter | Type | Required |
|-----------|------|----------|
| expires | integer | Yes |
| signature | string | Yes |

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Email verified successfully"
}
```

---

## Password Reset

### Request Password Reset

Send password reset email.

#### Endpoint
```
POST /api/forgot-password
```

#### Request Body

```json
{
  "email": "john.doe@example.com"
}
```

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Password reset link sent to your email"
}
```

### Reset Password

Reset password using token from email.

#### Endpoint
```
POST /api/reset-password
```

#### Request Body

```json
{
  "token": "abc123def456...",
  "email": "john.doe@example.com",
  "password": "NewSecurePassword123!",
  "password_confirmation": "NewSecurePassword123!"
}
```

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Password has been reset successfully"
}
```

---

## Two-Factor Authentication

### Enable 2FA

Generate 2FA QR code and secret.

#### Endpoint
```
POST /api/user/two-factor-authentication
```

#### Headers
```
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

#### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "qr_code": "data:image/svg+xml;base64,...",
    "secret": "JBSWY3DPEHPK3PXP",
    "recovery_codes": [
      "abc12-def34",
      "ghi56-jkl78",
      "mno90-pqr12"
    ]
  },
  "message": "2FA enabled successfully"
}
```

### Disable 2FA

#### Endpoint
```
DELETE /api/user/two-factor-authentication
```

#### Headers
```
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "2FA disabled successfully"
}
```

### Confirm 2FA

Verify 2FA code to complete setup.

#### Endpoint
```
POST /api/user/confirmed-two-factor-authentication
```

#### Request Body

```json
{
  "code": "123456"
}
```

#### Success Response (200 OK)

```json
{
  "success": true,
  "message": "2FA confirmed successfully"
}
```

---

## Get Authenticated User

Retrieve current authenticated user details.

### Endpoint
```
GET /api/user
```

### Headers
```
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

### Success Response (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john.doe@example.com",
    "email_verified_at": "2025-11-16T10:35:00.000000Z",
    "subscription_status": "active",
    "subscription_end": "2025-12-16T10:35:00.000000Z",
    "two_factor_enabled": false,
    "created_at": "2025-11-16T10:30:00.000000Z",
    "updated_at": "2025-11-16T10:35:00.000000Z"
  }
}
```

### Frontend Example

```javascript
async function getCurrentUser() {
  try {
    const token = localStorage.getItem('auth_token');
    
    const response = await fetch('https://your-domain.com/api/user', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    
    const data = await response.json();
    
    if (data.success) {
      return data.data;
    }
  } catch (error) {
    console.error('Failed to get user:', error);
  }
}
```

---

## Authentication Middleware

To protect routes that require authentication, use the `auth:sanctum` middleware.

Protected routes automatically check for:
- Valid Bearer token
- Non-expired token
- User account not suspended

---

**Next:** [Content APIs →](./CONTENT_APIS.md)
