<?php

use App\Mcp\Servers\CallCenterServer;
use Laravel\Mcp\Facades\Mcp;

// Register as web server for HTTP transport
// Accessible at: http://localhost:8000/mcp/call-center
// Supports both GET and POST requests
// Use this for: VSCode MCP extensions, HTTP clients, testing with cURL
Mcp::web('/mcp/call-center', CallCenterServer::class);

// Register as local server for STDIO transport
// Used by: Cursor IDE (via mcp:start command)
// Access via: php artisan mcp:start call-center
// Mcp::local('call-center', CallCenterServer::class);
//   {
//     "mcpServers": {
//       "laravel-call-center": {
//         "command": "php",
//         "args": [
//           "/home/ahmed/laravel/Flow/artisan",
//           "mcp:start",
//           "call-center"
//         ]
//       }
//     }
//   }
