# Web MCP Server Setup Guide

Your Laravel MCP Call Center server is configured to work via HTTP/web transport.

## Server Endpoint

**URL:** `http://localhost:8000/mcp/call-center`

The server is accessible via both GET and POST requests.

## Testing the Web Route

### 1. Start Laravel Server

```bash
php artisan serve
```

This will start the server on `http://127.0.0.1:8000`

### 2. Test with cURL

#### Initialize the MCP connection:
```bash
curl -X POST http://127.0.0.1:8000/mcp/call-center \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": 1,
    "method": "initialize",
    "params": {
      "protocolVersion": "2024-11-05",
      "capabilities": {},
      "clientInfo": {
        "name": "test-client",
        "version": "1.0.0"
      }
    }
  }'
```

#### List available tools:
```bash
curl -X POST http://127.0.0.1:8000/mcp/call-center \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": 2,
    "method": "tools/list",
    "params": {}
  }'
```

#### List available prompts:
```bash
curl -X POST http://127.0.0.1:8000/mcp/call-center \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": 3,
    "method": "prompts/list",
    "params": {}
  }'
```

#### List available resources:
```bash
curl -X POST http://127.0.0.1:8000/mcp/call-center \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": 4,
    "method": "resources/list",
    "params": {}
  }'
```

### 3. Using with MCP Clients

#### VSCode MCP Extension

If you're using VSCode with an MCP extension, configure it to connect to:
```
http://localhost:8000/mcp/call-center
```

#### Custom MCP Client

Any MCP-compatible client can connect to the HTTP endpoint using JSON-RPC 2.0 protocol.

## Available Endpoints

- **POST** `/mcp/call-center` - Main MCP endpoint (JSON-RPC 2.0)
- **GET** `/mcp/call-center` - Health check (returns server info)

## MCP Inspector Issue

The `php artisan mcp:inspector` command has a known issue with Node.js argument parsing. However, the web route itself works perfectly. You can:

1. Test directly with cURL (as shown above)
2. Use any MCP-compatible HTTP client
3. Use the STDIO transport with Cursor (which is already configured)

## Production Considerations

For production use:

1. **HTTPS**: Use HTTPS instead of HTTP
2. **Authentication**: Add authentication middleware if needed
3. **CORS**: Configure CORS headers if accessing from different domains
4. **Rate Limiting**: Consider adding rate limiting middleware

## Example: Calling a Tool

```bash
curl -X POST http://127.0.0.1:8000/mcp/call-center \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": 5,
    "method": "tools/call",
    "params": {
      "name": "create-ticket-tool",
      "arguments": {
        "tenant_id": "123e4567-e89b-12d3-a456-426614174000",
        "subject": "Test Ticket",
        "summary": "This is a test ticket created via web MCP",
        "priority": "medium",
        "category": "general"
      }
    }
  }'
```

## Troubleshooting

### Server Not Responding
- Ensure Laravel server is running: `php artisan serve`
- Check the route is registered: `php artisan route:list --path=mcp`

### CORS Issues
- Add CORS middleware if accessing from browser
- Configure `config/cors.php` for cross-origin requests

### Authentication Required
- If you add authentication, include the token in the `Authorization` header:
  ```
  Authorization: Bearer YOUR_TOKEN_HERE
  ```

