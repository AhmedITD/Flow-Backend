# Flow - AI-Powered Call Center Management System

Flow is a comprehensive SaaS platform that provides AI-powered call center and HR services with subscription-based billing, API key authentication, and real-time usage tracking.

## 🚀 Features

- **AI Chat Service** - OpenAI-powered chat with tool calling capabilities
- **MCP Integration** - Model Context Protocol for extensible tool system
- **Subscription Management** - Free trials, recurring subscriptions, and usage-based billing
- **API Key Authentication** - Secure API access without user login
- **Usage Tracking** - Real-time token usage monitoring and limits
- **Multi-Service Support** - Call Center and HR services
- **Payment Processing** - QiCard integration for payments
- **Stateless Chat** - No conversation history, each request is independent
- **JWT Authentication** - Admin dashboard access
- **Comprehensive Analytics** - API usage and token consumption tracking

## 📋 Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [API Documentation](#api-documentation)
- [Authentication](#authentication)
- [User Flow](#user-flow)
- [Database Schema](#database-schema)
- [Usage Examples](#usage-examples)
- [Testing](#testing)

## 🛠️ Installation

### Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js & NPM (for frontend assets)
- SQLite/MySQL/PostgreSQL
- OpenAI API key

### Step 1: Clone Repository

```bash
git clone <repository-url>
cd Flow
```

### Step 2: Install Dependencies

```bash
composer install
npm install
```

### Step 3: Environment Configuration

Copy the environment file and configure:

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set:

```env
APP_NAME=Flow
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# Or for MySQL/PostgreSQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=flow
# DB_USERNAME=root
# DB_PASSWORD=

OPENAI_API_KEY=your_openai_api_key_here
OPENAI_ORGANIZATION=your_org_id_here

QICARD_API_KEY=your_qicard_key
QICARD_SECRET=your_qicard_secret

OTPIQ_API_KEY=your_otpiq_api_key_here
OTPIQ_BASE_URL=https://api.otpiq.com/api
OTPIQ_DEFAULT_PROVIDER=whatsapp-sms
OTPIQ_CODE_LENGTH=6
OTPIQ_CODE_EXPIRES_IN=10
OTPIQ_MAX_ATTEMPTS=5
```

### Step 4: Database Setup

```bash
# Create SQLite database (if using SQLite)
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed database with test data
php artisan db:seed
```

### Step 5: Start Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000` to see the application.

## ⚙️ Configuration

### MCP Server Configuration

Edit `config/mcp.php` to configure MCP servers:

```php
return [
    'default' => 'call-center',
    
    'servers' => [
        'call-center' => [
            'system_prompt' => 'You are a helpful call center assistant...',
            'tools' => [
                'create_ticket',
                'view_ticket',
                'search_tickets',
                // ...
            ],
        ],
        'hr' => [
            'system_prompt' => 'You are an HR assistant...',
            'tools' => [
                // HR-specific tools
            ],
        ],
    ],
];
```

## 🗄️ Database Setup

### Migrations

Run migrations to create all tables:

```bash
php artisan migrate
```

### Seeders

Populate database with test data:

```bash
php artisan db:seed
```

**Test Accounts**:
- Phone: `+9647716418740` | Password: `password`
- Phone: `+9647501234567` | Password: `password`

**Test API Keys**:
- Admin: `flw_test_admin_key_12345678`
- Demo: `flw_test_demo_key_87654321`

## 📚 API Documentation

### Base URL

```
http://localhost:8000/api
```

### Authentication

Flow uses two authentication methods:

1. **JWT** - For admin dashboard routes
2. **API Key** - For customer service routes

---

## 🔐 Authentication

Flow uses **phone number authentication with OTP verification** via OTPIQ SMS/WhatsApp service.

### JWT Authentication (Admin Routes)

**Login**:
```http
POST /api/auth/login
Content-Type: application/json

{
  "phone_number": "+9647716418740",
  "password": "password"
}
```

**Response**:
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600,
  "user": {
    "id": 1,
    "name": "Admin User",
    "phone_number": "9647716418740"
  }
}
```

**Using JWT**:
```http
GET /api/auth/me
Authorization: Bearer {token}
```

### API Key Authentication (Customer Routes)

**Using API Key**:
```http
POST /api/chat/message
X-API-Key: flw_test_admin_key_12345678
Content-Type: application/json

{
  "message": "Create a billing ticket"
}
```

---

## 📡 API Endpoints

### Authentication Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/auth/register` | Public | Register new user (requires verification code from send-verification endpoint) |
| POST | `/api/auth/login` | Public | Login with phone number and password |
| POST | `/api/auth/send-verification` | Public | Send OTP verification code for registration (phone must NOT be registered) |
| POST | `/api/auth/verify-code` | Public | Verify OTP code for registration |
| POST | `/api/auth/forgot-password` | Public | Request password reset OTP code (phone MUST be registered) |
| POST | `/api/auth/reset-password` | Public | Reset password with OTP code verification |
| GET | `/api/auth/me` | JWT | Get current user |
| POST | `/api/auth/logout` | JWT | Logout |
| POST | `/api/auth/refresh` | JWT | Refresh token |

### API Key Management (JWT Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/api-keys` | List all API keys |
| POST | `/api/api-keys` | Generate new API key |
| GET | `/api/api-keys/{id}` | Get API key details |
| DELETE | `/api/api-keys/{id}` | Revoke API key |
| GET | `/api/api-keys/{id}/embed` | Get embed code |

**Generate API Key**:
```http
POST /api/api-keys
Authorization: Bearer {jwt_token}
Content-Type: application/json

{
  "name": "Production Key",
  "environment": "live",
  "scopes": ["chat:read", "chat:write"],
  "expires_at": "2026-12-31 23:59:59"
}
```

**Response**:
```json
{
  "success": true,
  "message": "API key generated successfully...",
  "api_key": {
    "id": "uuid",
    "name": "Production Key",
    "key": "flw_live_abc123xyz789",
    "key_prefix": "flw_live_abc123",
    "status": "active",
    "expires_at": null
  }
}
```

⚠️ **Important**: The plain API key is shown **only once** during creation. Save it securely!

### Chat Endpoints (API Key Required)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/chat/message` | API Key | Send chat message (SSE stream) |

**Chat Request**:
```http
POST /api/chat/message
X-API-Key: flw_test_admin_key_12345678
Content-Type: application/json
Accept: text/event-stream

{
  "message": "Create a billing ticket for customer #12345"
}
```

**Response** (Server-Sent Events):
```
event: tool_call
data: {"tool":"create_ticket","arguments":{"subject":"Billing Issue",...}}

event: tool_result
data: {"tool":"create_ticket","success":true,"content":"Ticket #456 created"}

event: update
data: I've created a billing ticket for you.

event: done
data: complete
```

### Payment Endpoints (JWT Required)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/payments/initiate` | JWT | Initiate payment |
| GET | `/api/payments/status/{id}` | JWT | Get payment status |
| GET | `/api/payments/history` | JWT | Get payment history |

---

## 🔄 User Flow

### Complete User Journey

1. **Registration** → User creates account
2. **Login** → Get JWT token for dashboard
3. **Free Trial** → Admin assigns trial subscription
4. **Generate API Key** → Create API key for service access
5. **Use Chat Service** → Make API calls with API key
6. **Usage Tracking** → Tokens tracked in real-time
7. **Trial Expires** → Subscription expires
8. **Upgrade to Paid** → Process payment
9. **Active Subscription** → Continue using service

### Detailed Flow Diagram

```
┌─────────────┐
│   Register  │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│    Login    │ → Get JWT Token
└──────┬──────┘
       │
       ▼
┌─────────────────┐
│ Free Trial Start│ → Subscription created
└──────┬──────────┘
       │
       ▼
┌─────────────────┐
│ Generate API Key│ → API key created
└──────┬──────────┘
       │
       ▼
┌─────────────────┐
│  Use Chat API   │ → Tokens tracked
└──────┬──────────┘
       │
       ▼
┌─────────────────┐
│  Trial Expires  │ → Subscription expired
└──────┬──────────┘
       │
       ▼
┌─────────────────┐
│  Upgrade to Paid│ → Payment processed
└──────┬──────────┘
       │
       ▼
┌─────────────────┐
│ Active Service  │ → Continue using
└─────────────────┘
```

---

## 🗃️ Database Schema

### Core Tables

- **`users`** - User accounts
- **`plans`** - Subscription plans (pricing, limits, features)
- **`subscriptions`** - User subscriptions to plans
- **`subscription_services`** - Token allocation per service type
- **`usage_records`** - Historical token usage log
- **`api_keys`** - API keys for authentication
- **`api_key_usage`** - API key request analytics
- **`payments`** - Payment transactions
- **`billing_cycles`** - Billing period tracking
- **`tickets`** - Support tickets (for call center service)

### Service Types (Enum)

- `call_center` - Call Center service with ticket management
- `hr` - HR service with employee support tools

### Subscription Status

- `trial` - Free trial period
- `active` - Active paid subscription
- `cancelled` - Cancelled subscription
- `expired` - Expired subscription
- `past_due` - Payment failed

See `database/schema.dbml` for complete schema visualization.

---

## 💻 Usage Examples

### JavaScript/TypeScript

**Chat with API Key**:
```javascript
async function sendChatMessage(message, apiKey) {
  const response = await fetch('http://localhost:8000/api/chat/message', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-API-Key': apiKey,
      'Accept': 'text/event-stream',
    },
    body: JSON.stringify({ message }),
  });

  const reader = response.body.getReader();
  const decoder = new TextDecoder();
  let buffer = '';

  while (true) {
    const { done, value } = await reader.read();
    if (done) break;

    buffer += decoder.decode(value, { stream: true });
    const lines = buffer.split('\n');
    buffer = lines.pop() || '';

    for (const line of lines) {
      if (line.startsWith('event: ')) {
        const eventType = line.slice(7).trim();
        // Handle event type
      }
      if (line.startsWith('data: ')) {
        const data = line.slice(6);
        // Handle data
      }
    }
  }
}
```

**Generate API Key (with JWT)**:
```javascript
async function generateApiKey(jwtToken) {
  const response = await fetch('http://localhost:8000/api/api-keys', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${jwtToken}`,
    },
    body: JSON.stringify({
      name: 'My API Key',
      environment: 'live',
    }),
  });

  const data = await response.json();
  console.log('API Key:', data.api_key.key); // Save this!
  return data;
}
```

### cURL Examples

**Login**:
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"phone_number":"+9647716418740","password":"password"}'
```

**Registration Flow**:

Step 1: Request verification code (phone must NOT be registered):
```bash
curl -X POST http://localhost:8000/api/auth/send-verification \
  -H "Content-Type: application/json" \
  -d '{"phone_number":"+9647501234567"}'
```

Step 2: Register with all info + verification code:
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","phone_number":"+9647501234567","password":"password","password_confirmation":"password","code":"123456"}'
```

**Password Reset Flow**:

Step 1: Request password reset OTP (phone must be registered):
```bash
curl -X POST http://localhost:8000/api/auth/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"phone_number":"+9647716418740"}'
```

Step 2: Reset password with OTP code:
```bash
curl -X POST http://localhost:8000/api/auth/reset-password \
  -H "Content-Type: application/json" \
  -d '{"phone_number":"+9647716418740","code":"123456","password":"newpassword","password_confirmation":"newpassword"}'
```

**Generate API Key**:
```bash
curl -X POST http://localhost:8000/api/api-keys \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Key","environment":"test"}'
```

**Chat Request**:
```bash
curl -X POST http://localhost:8000/api/chat/message \
  -H "X-API-Key: flw_test_admin_key_12345678" \
  -H "Content-Type: application/json" \
  -H "Accept: text/event-stream" \
  -d '{"message":"Hello, create a ticket"}'
```

### Python Example

```python
import requests

# Chat with API Key
def chat(api_key, message):
    response = requests.post(
        'http://localhost:8000/api/chat/message',
        headers={
            'X-API-Key': api_key,
            'Content-Type': 'application/json',
            'Accept': 'text/event-stream',
        },
        json={'message': message},
        stream=True
    )
    
    for line in response.iter_lines():
        if line.startswith(b'event: '):
            event_type = line[7:].decode()
        elif line.startswith(b'data: '):
            data = line[6:].decode()
            print(f"{event_type}: {data}")

# Usage
chat('flw_test_admin_key_12345678', 'Create a ticket')
```

---

## 🧪 Testing

### Run Tests

```bash
php artisan test
```

### Test API Key

Use the seeded test API key:
```
flw_test_admin_key_12345678
```

### Test User Credentials

```
Phone: +9647716418740
Password: password

Phone: +9647501234567
Password: password
```

## 📱 OTPIQ SMS/WhatsApp Integration

Flow uses OTPIQ service for phone number verification via SMS/WhatsApp.

### Configuration

Set the following environment variables:

```env
OTPIQ_API_KEY=your_otpiq_api_key_here
OTPIQ_BASE_URL=https://api.otpiq.com/api
OTPIQ_DEFAULT_PROVIDER=whatsapp-sms
OTPIQ_CODE_LENGTH=6
OTPIQ_CODE_EXPIRES_IN=10
OTPIQ_MAX_ATTEMPTS=5
```

### Usage

The OTPIQ service is automatically used during registration:

1. **Registration Flow** (phone number must NOT be registered):
   - Step 1: User calls `/api/auth/send-verification` with `phone_number` to receive OTP code
   - System validates that phone number does NOT exist before sending code
   - Step 2: User calls `/api/auth/register` with `name`, `phone_number`, `password`, `password_confirmation`, and the `code` received in Step 1
   - If code is valid, account is created and JWT token is issued

2. **Login**: User provides phone number + password → JWT token issued (no OTP required)

3. **Password Reset Flow** (phone number MUST be registered):
   - Step 1: User calls `/api/auth/forgot-password` with `phone_number` to receive OTP code
   - System validates that phone number EXISTS before sending code
   - Step 2: User calls `/api/auth/reset-password` with `phone_number`, `code`, `password`, and `password_confirmation`
   - If code is valid, password is reset and user can login with new password

### OTPIQ API

The service sends verification codes via SMS/WhatsApp using the OTPIQ API:

```php
POST https://api.otpiq.com/api/sms
Authorization: Bearer {OTPIQ_API_KEY}
Content-Type: application/json

{
  "phoneNumber": "9647716418740",
  "smsType": "verification",
  "provider": "whatsapp-sms",
  "verificationCode": "123456"
}
```

### Rate Limiting

- Maximum 5 OTP requests per phone number per hour
- OTP codes expire after 10 minutes
- Invalid codes can be attempted up to 5 times before requiring a new code

---

## 📊 Monitoring & Analytics

### API Key Usage

Query API key usage statistics:

```sql
SELECT 
    endpoint,
    COUNT(*) as calls,
    AVG(response_time_ms) as avg_response_time,
    SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as errors
FROM api_key_usage
WHERE api_key_id = ?
GROUP BY endpoint;
```

### Token Usage

Query token consumption:

```sql
SELECT 
    service_type,
    SUM(tokens_used) as total_tokens,
    COUNT(*) as requests,
    AVG(tokens_used) as avg_tokens_per_request
FROM usage_records
WHERE subscription_id = ?
  AND recorded_at >= DATE('now', 'start of month')
GROUP BY service_type;
```

---

## 🔒 Security

### API Key Security

- API keys are **hashed** with SHA-256 before storage
- Plain keys are shown **only once** during creation
- Keys can be revoked at any time
- Expired keys are automatically rejected

### Best Practices

1. **Never commit API keys** to version control
2. **Use environment variables** for sensitive data
3. **Rotate API keys** regularly
4. **Monitor usage** for suspicious activity
5. **Set expiration dates** for API keys

---

## 🛣️ Roadmap

- [ ] Webhook support for subscription events
- [ ] GraphQL API
- [ ] Rate limiting per API key
- [ ] Usage alerts and notifications
- [ ] Admin dashboard UI
- [ ] Multi-tenant support
- [ ] Advanced analytics dashboard

---

## 📝 License

[Specify your license here]

---

## 🤝 Contributing

[Contributing guidelines]

---

## 📞 Support

For support, email support@flow.example.com or open an issue in the repository.

---

## 🙏 Acknowledgments

- OpenAI for GPT API
- Laravel Framework
- Model Context Protocol (MCP)

---

**Built with ❤️ using Laravel**
