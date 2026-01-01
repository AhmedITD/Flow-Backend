<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call Center Chat</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
            background: linear-gradient(135deg, #0d0d0d 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .chat-container {
            width: 100%;
            max-width: 700px;
            height: 85vh;
            background: rgba(22, 33, 62, 0.95);
            border-radius: 16px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid rgba(233, 69, 96, 0.3);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5),
                        0 0 0 1px rgba(233, 69, 96, 0.1),
                        inset 0 1px 0 rgba(255, 255, 255, 0.05);
        }

        .chat-header {
            background: linear-gradient(90deg, #0f3460 0%, #1a1a2e 100%);
            color: #e94560;
            padding: 20px;
            text-align: center;
            font-weight: 700;
            font-size: 18px;
            letter-spacing: 1px;
            border-bottom: 1px solid rgba(233, 69, 96, 0.2);
            text-transform: uppercase;
        }

        .chat-header span {
            background: linear-gradient(90deg, #e94560, #ff6b6b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            scrollbar-width: thin;
            scrollbar-color: #e94560 #16213e;
        }

        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: #16213e;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: #e94560;
            border-radius: 3px;
        }

        .message {
            padding: 14px 18px;
            border-radius: 12px;
            max-width: 85%;
            word-wrap: break-word;
            white-space: pre-wrap;
            font-size: 14px;
            line-height: 1.5;
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.user {
            background: linear-gradient(135deg, #e94560 0%, #ff6b6b 100%);
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
            box-shadow: 0 4px 15px rgba(233, 69, 96, 0.3);
        }

        .message.assistant {
            background: linear-gradient(135deg, #0f3460 0%, #1a3a5c 100%);
            color: #e0e0e0;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
            border: 1px solid rgba(126, 200, 227, 0.2);
        }

        .message.tool-call {
            background: rgba(126, 200, 227, 0.1);
            color: #7ec8e3;
            align-self: flex-start;
            font-size: 12px;
            border-left: 3px solid #7ec8e3;
            max-width: 95%;
            font-family: 'JetBrains Mono', monospace;
        }

        .message.tool-result {
            background: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
            align-self: flex-start;
            font-size: 12px;
            border-left: 3px solid #2ecc71;
            max-width: 95%;
        }

        .message.tool-result.error {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border-left-color: #e74c3c;
        }

        .message.system {
            background: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
            align-self: center;
            font-size: 12px;
            text-align: center;
            max-width: 70%;
        }

        .tool-name {
            font-weight: 600;
            color: #f39c12;
        }

        .chat-input-container {
            padding: 20px;
            background: linear-gradient(90deg, #0f3460 0%, #1a1a2e 100%);
            display: flex;
            gap: 12px;
            border-top: 1px solid rgba(233, 69, 96, 0.2);
        }

        .chat-input {
            flex: 1;
            padding: 14px 18px;
            border: 1px solid rgba(233, 69, 96, 0.3);
            border-radius: 10px;
            background: rgba(22, 33, 62, 0.8);
            color: white;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: all 0.3s ease;
        }

        .chat-input:focus {
            border-color: #e94560;
            box-shadow: 0 0 0 3px rgba(233, 69, 96, 0.1);
        }

        .chat-input::placeholder {
            color: #666;
        }

        .send-button {
            padding: 14px 28px;
            background: linear-gradient(135deg, #e94560 0%, #ff6b6b 100%);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-family: inherit;
            font-size: 14px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .send-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(233, 69, 96, 0.4);
        }

        .send-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .typing-indicator {
            display: flex;
            gap: 4px;
            padding: 12px 18px;
            background: rgba(15, 52, 96, 0.5);
            border-radius: 12px;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }

        .typing-indicator span {
            width: 8px;
            height: 8px;
            background: #7ec8e3;
            border-radius: 50%;
            animation: bounce 1.4s infinite ease-in-out;
        }

        .typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
        .typing-indicator span:nth-child(2) { animation-delay: -0.16s; }

        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header"><span>MCP Call Center</span></div>
        
        <div class="chat-messages" id="chatMessages">
            <div class="message system">
                Welcome! I can help you manage support tickets.<br>
                Try: "Create a billing ticket" or "Search open tickets"
            </div>
        </div>

        <div class="chat-input-container">
            <input 
                type="text" 
                class="chat-input" 
                id="messageInput" 
                placeholder="Type a message..."
                autocomplete="off"
            >
            <button class="send-button" id="sendButton">Send</button>
        </div>
    </div>

    <script>
        const messagesContainer = document.getElementById('chatMessages');
        const input = document.getElementById('messageInput');
        const button = document.getElementById('sendButton');
        
        let conversationId = null;
        let currentEventType = null;

        function addMessage(type, content, extraClass = '') {
            const div = document.createElement('div');
            div.className = `message ${type} ${extraClass}`.trim();
            div.innerHTML = content;
            messagesContainer.appendChild(div);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            return div;
        }

        function addTypingIndicator() {
            const div = document.createElement('div');
            div.className = 'typing-indicator';
            div.id = 'typingIndicator';
            div.innerHTML = '<span></span><span></span><span></span>';
            messagesContainer.appendChild(div);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
            return div;
        }

        function removeTypingIndicator() {
            const indicator = document.getElementById('typingIndicator');
            if (indicator) indicator.remove();
        }

        async function ensureConversation() {
            if (conversationId) return true;
            
            try {
                const response = await fetch('/api/chat/conversations', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ title: 'Chat Session' })
                });
                const data = await response.json();
                conversationId = data.conversation_id;
                return true;
            } catch (error) {
                console.error('Failed to create conversation:', error);
                return false;
            }
        }

        async function sendMessage() {
            const message = input.value.trim();
            if (!message) return;

            if (!await ensureConversation()) {
                addMessage('assistant', 'Error: Could not start conversation');
                return;
            }

            addMessage('user', escapeHtml(message));
            input.value = '';
            input.disabled = true;
            button.disabled = true;

            addTypingIndicator();

            let responseDiv = null;
            let fullResponse = '';

            try {
                const response = await fetch(`/api/chat/conversations/${conversationId}/message`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'text/event-stream',
                    },
                    body: JSON.stringify({ message: message })
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

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
                            currentEventType = line.slice(7).trim();
                            continue;
                        }
                        
                        if (line.startsWith('data: ')) {
                            const data = line.slice(6);
                            if (data === '</stream>') continue;

                            switch (currentEventType) {
                                case 'tool_call':
                                    removeTypingIndicator();
                                    try {
                                        const toolData = JSON.parse(data);
                                        const argsStr = JSON.stringify(toolData.arguments, null, 2);
                                        addMessage('tool-call', 
                                            `🔧 Calling <span class="tool-name">${toolData.tool}</span>\n<code>${argsStr}</code>`
                                        );
                                    } catch (e) {
                                        addMessage('tool-call', `🔧 ${data}`);
                                    }
                                    addTypingIndicator();
                                    break;

                                case 'tool_result':
                                    removeTypingIndicator();
                                    try {
                                        const resultData = JSON.parse(data);
                                        const isError = !resultData.success;
                                        const icon = isError ? '❌' : '✅';
                                        const content = resultData.content || (isError ? resultData.error : 'Done');
                                        addMessage('tool-result', 
                                            `${icon} <span class="tool-name">${resultData.tool}</span>: ${escapeHtml(content)}`,
                                            isError ? 'error' : ''
                                        );
                                    } catch (e) {
                                        addMessage('tool-result', `📋 ${data}`);
                                    }
                                    addTypingIndicator();
                                    break;

                                case 'update':
                                    removeTypingIndicator();
                                    if (!responseDiv) {
                                        responseDiv = addMessage('assistant', '');
                                    }
                                    fullResponse += data;
                                    responseDiv.textContent = fullResponse;
                                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                                    break;

                                case 'error':
                                    removeTypingIndicator();
                                    addMessage('assistant', `Error: ${data}`);
                                    break;

                                case 'done':
                                    removeTypingIndicator();
                                    break;

                                default:
                                    // Try to parse as JSON for backward compatibility
                                    try {
                                        const parsed = JSON.parse(data);
                                        if (parsed.tool) {
                                            addMessage('tool-call', `🔧 ${parsed.tool}`);
                                        }
                                    } catch (e) {
                                        // Plain text
                                        if (!responseDiv) {
                                            responseDiv = addMessage('assistant', '');
                                        }
                                        fullResponse += data;
                                        responseDiv.textContent = fullResponse;
                                    }
                            }
                        }
                    }
                }

                removeTypingIndicator();

                if (!fullResponse && !responseDiv) {
                    addMessage('system', 'Request completed.');
                }

            } catch (error) {
                removeTypingIndicator();
                console.error('Error:', error);
                addMessage('assistant', `Error: ${error.message}`);
            }

            input.disabled = false;
            button.disabled = false;
            input.focus();
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        button.addEventListener('click', sendMessage);
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });

        input.focus();
    </script>
</body>
</html>
