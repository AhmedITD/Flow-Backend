# Flow Payment API Documentation

A Laravel-based REST API for user authentication and payment processing using QiCard Payment Gateway.

## Table of Contents

- [Base URL](#base-url)
- [Authentication](#authentication)
- [API Endpoints](#api-endpoints)
  - [Authentication Endpoints](#authentication-endpoints)
  - [Payment Endpoints](#payment-endpoints)
- [Request/Response Examples](#requestresponse-examples)
- [Error Handling](#error-handling)

## Base URL

```
http://your-domain.com/api
```

## Authentication

This API uses **JWT (JSON Web Tokens)** for authentication. Most endpoints require authentication via Bearer token in the Authorization header.

### Getting a Token

1. Register a new user account
2. Login with your credentials
3. Use the returned `access_token` in subsequent requests

### Using the Token

Include the token in the Authorization header:

```
Authorization: Bearer {your_access_token}
```

## API Endpoints

### Authentication Endpoints

#### 1. Register User

Create a new user account.

**Endpoint:** `POST /api/auth/register`

**Authentication:** Not required

**Request Parameters:**
- `name` (required): User's full name (2-100 characters)
- `email` (required): User's email address (must be unique)
- `password` (required): User's password (minimum 6 characters)
- `password_confirmation` (required): Must match the password field

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response (201 Created):**
```json
{
  "message": "User successfully registered",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2025-12-26T10:00:00.000000Z",
    "updated_at": "2025-12-26T10:00:00.000000Z"
  },
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

**Validation Errors (422 Unprocessable Entity):**
```json
{
  "name": ["The name field is required."],
  "email": ["The email has already been taken."],
  "password": ["The password must be at least 6 characters.", "The password confirmation does not match."]
}
```

---

#### 2. Login

Authenticate user and get access token.

**Endpoint:** `POST /api/auth/login`

**Authentication:** Not required

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (200 OK):**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

**Error Responses:**
- `401 Unauthorized` - Invalid credentials
- `422 Unprocessable Entity` - Validation errors
- `500 Internal Server Error` - Token creation failed

---

#### 3. Get Current User

Get authenticated user information.

**Endpoint:** `GET /api/auth/me`

**Authentication:** Required

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200 OK):**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "created_at": "2025-12-26T10:00:00.000000Z",
  "updated_at": "2025-12-26T10:00:00.000000Z"
}
```

---

#### 4. Logout

Invalidate the current access token.

**Endpoint:** `POST /api/auth/logout`

**Authentication:** Required

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200 OK):**
```json
{
  "message": "Successfully logged out"
}
```

---

#### 5. Refresh Token

Get a new access token using the current token.

**Endpoint:** `POST /api/auth/refresh`

**Authentication:** Required

**Headers:**
```
Authorization: Bearer {access_token}
```

**Response (200 OK):**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

---

### Payment Endpoints

#### 1. Initiate Payment

Create a new payment transaction with QiCard Payment Gateway.

**Endpoint:** `POST /api/payments/initiate`

**Authentication:** Required

**Headers:**
```
Authorization: Bearer {access_token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "amount": 100.50,
  "currency": "IQD",
  "description": "Payment for order #123",
  "return_url": "https://your-app.com/payment/success",
  "callback_url": "https://your-app.com/api/payments/webhook"
}
```

**Request Parameters:**
- `amount` (required): Payment amount (numeric, minimum 0.01)
- `currency` (optional): Currency code (3 characters, default: "IQD")
- `description` (optional): Payment description (max 500 characters)
- `return_url` (optional): URL to redirect user after payment completion
- `callback_url` (optional): Webhook URL for payment status updates

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Payment initiated successfully",
  "payment": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "amount": 100.50,
    "currency": "IQD",
    "status": "processing",
    "payment_url": "https://payment-gateway.com/pay/abc123"
  }
}
```

**Error Response (400 Bad Request):**
```json
{
  "success": false,
  "message": "Payment initiation failed",
  "payment": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "amount": 100.50,
    "currency": "IQD",
    "status": "failed"
  }
}
```

**Validation Errors (422 Unprocessable Entity):**
```json
{
  "errors": {
    "amount": ["The amount field is required."],
    "currency": ["The currency must be 3 characters."]
  }
}
```

**Note:** The `payment_url` in the response should be used to redirect the user to the payment gateway for completing the transaction.

---

#### 2. Payment Webhook

Receive payment status updates from QiCard Payment Gateway.

**Endpoint:** `POST /api/payments/webhook`

**Authentication:** Not required (uses webhook secret validation)

**Note:** This endpoint is called by QiCard Payment Gateway to notify your application about payment status changes.

---

#### 3. Payment Callback

Handle user redirect after payment completion.

**Endpoint:** `GET /api/payments/callback`

**Authentication:** Not required

**Query Parameters:**
- `transaction_id`: Payment transaction ID
- `status`: Payment status

---

#### 4. Payment Return

Alternative endpoint for payment return handling.

**Endpoint:** `GET /api/payments/return`

**Authentication:** Not required

---

## Request/Response Examples

### Complete Payment Flow Example

#### Step 1: Register User
```bash
curl -X POST http://your-domain.com/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

#### Step 2: Login
```bash
curl -X POST http://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

#### Step 3: Initiate Payment
```bash
curl -X POST http://your-domain.com/api/payments/initiate \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {access_token}" \
  -d '{
    "amount": 100.50,
    "currency": "IQD",
    "description": "Payment for order #123",
    "return_url": "https://your-app.com/payment/success",
    "callback_url": "https://your-app.com/api/payments/webhook"
  }'
```

#### Step 4: Redirect User to Payment URL
Use the `payment_url` from the response to redirect the user to complete the payment.

## Error Handling

### HTTP Status Codes

- `200 OK` - Request successful
- `201 Created` - Resource created successfully
- `400 Bad Request` - Invalid request data
- `401 Unauthorized` - Authentication required or invalid token
- `422 Unprocessable Entity` - Validation errors
- `500 Internal Server Error` - Server error

### Error Response Format

```json
{
  "success": false,
  "message": "Error description",
  "error": "Detailed error message",
  "error_code": 27
}
```

### Common Error Codes

- **27**: Authentication required - Invalid credentials or missing authentication
- **5**: Request ID already used - Duplicate payment request
- **1**: Order already exists

## Payment Status Values

- `pending` - Payment created but not yet processed
- `processing` - Payment is being processed
- `completed` - Payment completed successfully
- `failed` - Payment failed
- `cancelled` - Payment was cancelled

## Notes

- All timestamps are in ISO 8601 format (UTC)
- Payment amounts should be numeric with up to 2 decimal places
- Default currency is IQD (Iraqi Dinar)
- JWT tokens expire after 1 hour (3600 seconds)
- Use the refresh token endpoint to get a new token before expiration

## Support

For issues or questions, please contact the development team.
