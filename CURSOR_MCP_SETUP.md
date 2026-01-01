# Cursor MCP Server Setup Guide

This guide will help you configure Cursor IDE to connect to your Laravel MCP Call Center server.

## Option 1: Using STDIO Transport (Recommended)

This is the most reliable method for local development.

### Step 1: Find Cursor's MCP Configuration Location

The MCP configuration file location depends on your operating system:

- **macOS**: `~/Library/Application Support/Cursor/User/globalStorage/mcp.json`
- **Windows**: `%APPDATA%\Cursor\User\globalStorage\mcp.json`
- **Linux**: `~/.config/Cursor/User/globalStorage/mcp.json`

### Step 2: Create or Edit the MCP Configuration

Create or edit the `mcp.json` file and add the following configuration:

```json
{
  "mcpServers": {
    "laravel-call-center": {
      "command": "/home/ahmed/laravel/Flow/mcp-server.sh"
    }
  }
}
```

**Alternative (direct command):**
```json
{
  "mcpServers": {
    "laravel-call-center": {
      "command": "php",
      "args": [
        "/home/ahmed/laravel/Flow/artisan",
        "mcp:start",
        "call-center",
        "--no-ansi"
      ]
    }
  }
}
```

**Important**: Replace `/home/ahmed/laravel/Flow` with the absolute path to your Laravel project.

### Step 3: Restart Cursor

After saving the configuration, restart Cursor IDE completely for the changes to take effect.

## Option 2: Using HTTP Transport

If you prefer HTTP transport, you need to:

1. **Start your Laravel server**:
   ```bash
   php artisan serve
   ```

2. **Configure Cursor** with HTTP transport:
   ```json
   {
     "mcpServers": {
       "laravel-call-center": {
         "url": "http://localhost:8000/mcp/call-center",
         "transport": "http"
       }
     }
   }
   ```

## Testing the Connection

After configuring and restarting Cursor:

1. Open Cursor's MCP panel (usually accessible via Command Palette: `Cmd/Ctrl + Shift + P` → "MCP")
2. You should see "laravel-call-center" listed as an available server
3. Try using the tools in Cursor's chat by mentioning ticket operations

## Available Tools

Once connected, you'll have access to these tools:
- `CreateTicketTool` - Create new support tickets
- `ViewTicketTool` - View ticket details
- `UpdateTicketTool` - Update ticket information
- `SearchTicketsTool` - Search for tickets
- `AssignTicketTool` - Assign tickets to agents
- `UpdateTicketStatusTool` - Update ticket status

## Troubleshooting

### Server Not Appearing
- Make sure you've restarted Cursor completely
- Check that the path to `artisan` is correct and absolute
- Verify PHP is in your system PATH

### Connection Errors
- Ensure Laravel dependencies are installed: `composer install`
- Check that your database is configured and migrations are run
- Verify the server route is accessible: `php artisan route:list --path=mcp`

### Testing the Server Directly
You can test if the MCP server works by running:
```bash
php artisan mcp:start mcp/call-center
```

This will start the server in STDIO mode, which you can test manually.

