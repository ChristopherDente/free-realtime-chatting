<?php
// You can now run PHP logic at the top of your page before rendering the HTML!
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Free Realtime Chatting</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        /* Modal styling */
        .blur-bg {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }

        .avatar {
            width: 48px;
            height: 48px;
            font-size: 1.2rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
        }

        .chat-wrapper {
            height: 100vh;
        }

        .chat-messages {
            background-color: #e9ecef;
            scroll-behavior: smooth;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .message-box {
            max-width: 75%;
            padding: 12px 16px;
            border-radius: 18px;
            position: relative;
            font-size: 0.95rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .message-box.received {
            background-color: #ffffff;
            border-bottom-left-radius: 4px;
            align-self: flex-start;
        }

        .message-box.sent {
            background-color: #0d6efd;
            color: white;
            border-bottom-right-radius: 4px;
            align-self: flex-end;
        }

        .message-sender {
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 4px;
            color: #6c757d;
        }

        .message-box.sent .message-sender {
            display: none; /* Hide sender name for own messages */
        }

        .message-time {
            font-size: 0.7rem;
            margin-top: 4px;
            opacity: 0.7;
            text-align: right;
            display: block;
        }

        .system-message {
            align-self: center;
            background-color: rgba(0,0,0,0.05);
            color: #6c757d;
            font-size: 0.8rem;
            padding: 6px 12px;
            border-radius: 20px;
            margin: 10px 0;
        }

        .send-btn {
            width: 50px;
            height: 50px;
            padding: 0;
        }

        .send-btn svg {
            margin-left: -2px;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    <!-- Login/Join Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg blur-bg">
          <div class="modal-header border-0">
            <h5 class="modal-title fw-bold" id="loginModalLabel">Join Chat</h5>
          </div>
          <div class="modal-body">
            <form id="joinForm">
                <div class="mb-3">
                    <label for="usernameInput" class="form-label text-muted">Username</label>
                    <input type="text" class="form-control form-control-lg bg-light border-0" id="usernameInput" placeholder="Enter your name..." required>
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold" id="joinBtn">Join Now</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Chat Interface -->
    <div class="container-fluid h-100 p-0 d-none" id="chatContainer">
        <div class="row g-0 h-100 justify-content-center bg-light">
            <div class="col-12 col-md-8 col-lg-6 d-flex flex-column h-100 chat-wrapper shadow-sm bg-white">
                
                <!-- Chat Header -->
                <div class="chat-header p-3 bg-white border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar bg-primary text-white fw-bold rounded-circle d-flex align-items-center justify-content-center">
                            RC
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Realtime Chat</h5>
                            <small class="text-success"><span class="status-dot bg-success d-inline-block rounded-circle"></span> Online</small>
                        </div>
                    </div>
                </div>

                <!-- Chat Messages -->
                <div class="chat-messages flex-grow-1 p-3 overflow-auto" id="chatMessages">
                    <!-- Messages will be appended here -->
                </div>

                <!-- Chat Input Area -->
                <div class="chat-input p-3 bg-white border-top">
                    <form id="messageForm" class="d-flex gap-2">
                        <input type="text" id="messageInput" class="form-control form-control-lg bg-light border-0" placeholder="Type a message..." required autocomplete="off">
                        <button type="submit" class="btn btn-primary btn-lg px-4 rounded-circle d-flex align-items-center justify-content-center send-btn">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap & App JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let ws;
        let myUsername = '';
        const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        const chatContainer = document.getElementById('chatContainer');
        const chatMessages = document.getElementById('chatMessages');

        // Show modal on load
        document.addEventListener('DOMContentLoaded', () => {
            loginModal.show();
        });

        document.getElementById('joinForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const input = document.getElementById('usernameInput');
            const username = input.value.trim();
            if (username) {
                myUsername = username;
                connectWebSocket();
                loginModal.hide();
                chatContainer.classList.remove('d-none');
            }
        });

        function connectWebSocket() {
            // Determine WS URL based on current host (use ws:// or wss:// accordingly)
            const host = window.location.hostname || 'localhost';
            ws = new WebSocket(`ws://${host}:9095`);

            ws.onopen = () => {
                console.log('Connected to server');
                // Send join message
                ws.send(JSON.stringify({
                    type: 'join',
                    username: myUsername
                }));
            };

            ws.onmessage = (event) => {
                const data = JSON.parse(event.data);
                
                if (data.type === 'history') {
                    data.data.forEach(msg => {
                        appendMessage(msg.username, msg.message, msg.timestamp);
                    });
                    scrollToBottom();
                } else if (data.type === 'chat') {
                    appendMessage(data.username, data.message, data.timestamp);
                    scrollToBottom();
                } else if (data.type === 'system') {
                    appendSystemMessage(data.message);
                    scrollToBottom();
                }
            };

            ws.onclose = () => {
                console.log('Disconnected from server');
                appendSystemMessage('Disconnected from the chat server.');
            };

            ws.onerror = (error) => {
                console.error('WebSocket Error: ', error);
            };
        }

        document.getElementById('messageForm').addEventListener('submit', (e) => {
            e.preventDefault();
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (message && ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'chat',
                    message: message
                }));
                input.value = '';
            }
        });

        function appendMessage(username, message, timestamp) {
            const isSentByMe = username === myUsername;
            const msgDiv = document.createElement('div');
            msgDiv.className = `message-box ${isSentByMe ? 'sent' : 'received'}`;
            
            const timeFormatted = new Date(timestamp).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            let senderHtml = '';
            if (!isSentByMe) {
                senderHtml = `<div class="message-sender">${username}</div>`;
            }
            
            msgDiv.innerHTML = `
                ${senderHtml}
                <div class="message-content">${message}</div>
                <div class="message-time">${timeFormatted}</div>
            `;
            
            chatMessages.appendChild(msgDiv);
        }

        function appendSystemMessage(message) {
            const sysDiv = document.createElement('div');
            sysDiv.className = 'system-message';
            sysDiv.innerText = message;
            chatMessages.appendChild(sysDiv);
        }

        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    </script>
</body>
</html>
