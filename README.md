# Flow Payment API Documentation

A Laravel-based REST API for user authentication and payment processing using QiCard Payment Gateway.

## Table of Contents

- [Base URL](#base-url)
- [Authentication](#authentication)
- [API Endpoints](#api-endpoints)
  - [Authentication Endpoints](#authentication-endpoints)
  - [Payment Endpoints](#payment-endpoints)
  - [Chat Endpoints](#chat-endpoints)
- [MCP Integration](#mcp-integration)
  - [What is MCP?](#what-is-mcp)
  - [How It Works](#how-it-works)
  - [Available Tools](#available-tools)
  - [Tool Calling Flow](#tool-calling-flow)
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

### Chat Endpoints

#### 1. Create Conversation

Create a new chat conversation.

**Endpoint:** `POST /api/chat/conversations`

**Authentication:** Not required

**Request Body:**
```json
{
  "tenant_id": "00000000-0000-0000-0000-000000000001",
  "title": "Customer Support Chat"
}
```

**Request Parameters:**
- `tenant_id` (optional): Tenant ID (UUID) for multi-tenant support
- `title` (optional): Conversation title (default: "New Conversation")

**Response (200 OK):**
```json
{
  "conversation_id": "86e6087f-b221-46b6-b519-772be551acc1",
  "title": "Customer Support Chat",
  "created_at": "2025-12-31T18:50:36.000000Z"
}
```

---

#### 2. Send Message (Streaming)

Send a message to the AI assistant and receive a streaming response. The AI can automatically use MCP tools to perform actions like creating tickets, searching tickets, etc.

**Endpoint:** `POST /api/chat/conversations/{conversationId}/message`

**Authentication:** Not required

**Content-Type:** `text/event-stream` (Server-Sent Events)

**Request Body:**
```json
{
  "message": "Create a ticket for a billing issue"
}
```

**Request Parameters:**
- `message` (required): The user's message to the AI assistant

**Response (200 OK - SSE Stream):**

The response is streamed using Server-Sent Events (SSE). Events include:

1. **Tool Call Events** (`tool_call`): When AI decides to use a tool
```json
{
  "event": "tool_call",
  "data": "{\"tool\":\"create_ticket\",\"arguments\":{\"subject\":\"Billing Issue\",\"summary\":\"Customer inquiry about billing\"}}"
}
```

2. **Tool Result Events** (`tool_result`): Result of tool execution
```json
{
  "event": "tool_result",
  "data": "{\"tool\":\"create_ticket\",\"success\":true,\"content\":\"Ticket created successfully!\\n\\nTicket ID: 6393423d-f8be-4840-9411-bbf5bde36f6f\"}"
}
```

3. **Update Events** (`update`): Streaming text response from AI
```
event: update
data: Ticket
```

```
event: update
data:  created
```

```
event: update
data:  successfully!
```

4. **Error Events** (`error`): If an error occurs
```json
{
  "event": "error",
  "data": "Error message here"
}
```

5. **Done Event** (`done`): When the response is complete
```
event: done
data: complete
```

**Example Client Code (JavaScript):**
```javascript
async function sendMessage(conversationId, message) {
  const response = await fetch(
    `/api/chat/conversations/${conversationId}/message`,
    {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'text/event-stream',
      },
      body: JSON.stringify({ message }),
    }
  );

  const reader = response.body.getReader();
  const decoder = new TextDecoder();
  let buffer = '';
  let currentEvent = '';

  while (true) {
    const { done, value } = await reader.read();
    if (done) break;

    buffer += decoder.decode(value, { stream: true });
    const lines = buffer.split('\n');
    buffer = lines.pop(); // Keep incomplete line in buffer

    for (const line of lines) {
      if (line.startsWith('event: ')) {
        currentEvent = line.substring(7).trim();
      } else if (line.startsWith('data: ')) {
        const data = line.substring(6).trim();
        handleEvent(currentEvent, data);
      }
    }
  }
}

function handleEvent(event, data) {
  switch (event) {
    case 'tool_call':
      const toolCall = JSON.parse(data);
      console.log('AI is using tool:', toolCall.tool);
      break;
    case 'tool_result':
      const toolResult = JSON.parse(data);
      console.log('Tool result:', toolResult);
      break;
    case 'update':
      // Append streaming text to UI
      appendToChat(data);
      break;
    case 'error':
      console.error('Error:', data);
      break;
    case 'done':
      console.log('Response complete');
      break;
  }
}
```

---

## MCP Integration

### What is MCP?

**MCP (Model Context Protocol)** is a protocol that allows AI assistants to interact with external tools and services. In this application, MCP tools enable the AI to:

- Create and manage support tickets
- Search for existing tickets
- Update ticket information
- Assign tickets to agents
- Perform other call center operations

The AI automatically decides when to use these tools based on the user's request, making the chat interface intelligent and action-oriented.

### How It Works

1. **User sends a message** → The message is saved to the conversation
2. **System builds context** → Includes system prompt and conversation history
3. **AI analyzes request** → OpenAI analyzes the message and available tools
4. **Tool calling (if needed)** → AI calls relevant MCP tools automatically
5. **Tool execution** → Tools perform actions (create ticket, search, etc.)
6. **AI generates response** → AI uses tool results to generate a natural response
7. **Streaming response** → Response is streamed back to the user word-by-word

### Available Tools

The Call Center MCP server provides the following tools:

#### 1. `create_ticket`

Creates a new support ticket for a customer inquiry.

**Parameters:**
- `subject` (required): Brief subject line describing the issue
- `summary` (required): Detailed description of the customer inquiry
- `tenant_id` (optional): Tenant ID (UUID), defaults to "00000000-0000-0000-0000-000000000001"
- `channel` (optional): Communication channel - `voice`, `chat`, or `email` (default: `voice`)
- `priority` (optional): Priority level - `low`, `medium`, `high`, or `urgent` (default: `medium`)
- `category` (optional): Ticket category - `billing`, `technical`, `shipping`, `account`, `general`, or `other` (default: `general`)

**Example Usage:**
```
User: "Create a ticket for a billing issue - customer was overcharged $50"
AI: [Automatically calls create_ticket tool]
```

#### 2. `view_ticket`

Retrieves detailed information about a specific ticket.

**Parameters:**
- `ticket_id` (required): The unique ticket ID (UUID) to retrieve

**Example Usage:**
```
User: "Show me details for ticket 6393423d-f8be-4840-9411-bbf5bde36f6f"
AI: [Automatically calls view_ticket tool]
```

#### 3. `update_ticket`

Updates ticket information (subject, summary, priority, category).

**Parameters:**
- `ticket_id` (required): The ticket ID (UUID) to update
- `subject` (optional): New subject line
- `summary` (optional): Updated summary
- `priority` (optional): New priority level
- `category` (optional): New category

**Example Usage:**
```
User: "Update ticket 6393423d-f8be-4840-9411-bbf5bde36f6f to high priority"
AI: [Automatically calls update_ticket tool]
```

#### 4. `search_tickets`

Searches for tickets based on various criteria.

**Parameters:**
- `tenant_id` (optional): Filter by tenant ID
- `status` (optional): Filter by status - `open`, `pending`, `resolved`, or `closed`
- `priority` (optional): Filter by priority
- `category` (optional): Filter by category
- `limit` (optional): Maximum number of results (default: 10)

**Example Usage:**
```
User: "Show me all open billing tickets"
AI: [Automatically calls search_tickets tool with status=open and category=billing]
```

#### 5. `assign_ticket`

Assigns a ticket to a specific agent.

**Parameters:**
- `ticket_id` (required): The ticket ID (UUID) to assign
- `agent_id` (required): The agent ID (UUID) to assign the ticket to

**Example Usage:**
```
User: "Assign ticket 6393423d-f8be-4840-9411-bbf5bde36f6f to agent 00000000-0000-0000-0000-000000000002"
AI: [Automatically calls assign_ticket tool]
```

#### 6. `update_ticket_status`

Updates the status of a ticket.

**Parameters:**
- `ticket_id` (required): The ticket ID (UUID) to update
- `status` (required): New status - `open`, `pending`, `resolved`, or `closed`

**Example Usage:**
```
User: "Mark ticket 6393423d-f8be-4840-9411-bbf5bde36f6f as resolved"
AI: [Automatically calls update_ticket_status tool]
```

### Tool Calling Flow

The AI uses a sophisticated tool-calling mechanism:

1. **User Request**: User sends a natural language message
   ```
   "Create a ticket for a billing issue"
   ```

2. **AI Analysis**: OpenAI analyzes the message and determines it needs to use `create_ticket`

3. **Tool Call Event**: Client receives `tool_call` event
   ```json
   {
     "event": "tool_call",
     "data": {
       "tool": "create_ticket",
       "arguments": {
         "subject": "Billing Issue",
         "summary": "Customer inquiry about billing",
         "category": "billing"
       }
     }
   }
   ```

4. **Tool Execution**: System executes the tool and creates the ticket

5. **Tool Result Event**: Client receives `tool_result` event
   ```json
   {
     "event": "tool_result",
     "data": {
       "tool": "create_ticket",
       "success": true,
       "content": "Ticket created successfully!\n\nTicket ID: 6393423d-f8be-4840-9411-bbf5bde36f6f"
     }
   }
   ```

6. **AI Response**: AI generates a natural language response using the tool result
   ```
   "I've created a ticket for the billing issue. The ticket ID is 6393423d-f8be-4840-9411-bbf5bde36f6f. 
   It has been categorized as a billing issue and set to medium priority."
   ```

7. **Streaming**: Response is streamed word-by-word to the client

**Multiple Tool Calls**: The AI can call multiple tools in sequence if needed. For example:
- First: `search_tickets` to find existing tickets
- Then: `create_ticket` if no matching ticket exists
- Finally: `update_ticket_status` to mark it as resolved

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

---

### Complete Chat Flow Example

#### Step 1: Create a Conversation
```bash
curl -X POST http://your-domain.com/api/chat/conversations \
  -H "Content-Type: application/json" \
  -d '{
    "tenant_id": "00000000-0000-0000-0000-000000000001",
    "title": "Customer Support"
  }'
```

**Response:**
```json
{
  "conversation_id": "86e6087f-b221-46b6-b519-772be551acc1",
  "title": "Customer Support",
  "created_at": "2025-12-31T18:50:36.000000Z"
}
```

#### Step 2: Send a Message (with Tool Calling)
```bash
curl -X POST http://your-domain.com/api/chat/conversations/86e6087f-b221-46b6-b519-772be551acc1/message \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Create a ticket for a billing issue - customer was overcharged $50 on their last invoice"
  }'
```

**SSE Stream Response:**
```
event: tool_call
data: {"tool":"create_ticket","arguments":{"subject":"Billing Issue","summary":"Customer was overcharged $50 on their last invoice","category":"billing","priority":"high"}}

event: tool_result
data: {"tool":"create_ticket","success":true,"content":"Ticket created successfully!\n\nTicket ID: 6393423d-f8be-4840-9411-bbf5bde36f6f\nSubject: Billing Issue\nStatus: open\nPriority: high\nCategory: billing"}

event: update
data: I've

event: update
data:  created

event: update
data:  a

event: update
data:  ticket

event: update
data:  for

event: update
data:  the

event: update
data:  billing

event: update
data:  issue.

event: update
data:  The

event: update
data:  ticket

event: update
data:  ID

event: update
data:  is

event: update
data:  6393423d-f8be-4840-9411-bbf5bde36f6f

event: done
data: complete
```

#### Step 3: Search for Tickets
```bash
curl -X POST http://your-domain.com/api/chat/conversations/86e6087f-b221-46b6-b519-772be551acc1/message \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Show me all open billing tickets"
  }'
```

The AI will automatically use the `search_tickets` tool with appropriate filters.

#### Step 4: Update Ticket Status
```bash
curl -X POST http://your-domain.com/api/chat/conversations/86e6087f-b221-46b6-b519-772be551acc1/message \
  -H "Content-Type: application/json" \
  -d '{
    "message": "Mark ticket 6393423d-f8be-4840-9411-bbf5bde36f6f as resolved"
  }'
```

The AI will automatically use the `update_ticket_status` tool.

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
- Chat conversations are stored with full message history
- MCP tools are automatically selected by the AI based on user requests
- Tool calls are transparent - users see what tools are being used via SSE events
- The AI can chain multiple tool calls to complete complex requests
- Conversation history is limited to the last 10 messages for context efficiency

## Support

For issues or questions, please contact the development team.
