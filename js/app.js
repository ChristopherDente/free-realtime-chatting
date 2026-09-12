let ws;
let myUsername = '';
const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
const chatContainer = document.getElementById('chatContainer');
const chatMessages = document.getElementById('chatMessages');

// Show modal on load
document.addEventListener('DOMContentLoaded', () => {
    loginModal.show();
    setTimeout(() => document.getElementById('usernameInput').focus(), 500);
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
        setTimeout(() => document.getElementById('messageInput').focus(), 500);
    }
});

function connectWebSocket() {
    const host = window.location.hostname || 'localhost';
    ws = new WebSocket(`ws://${host}:9095`);

    ws.onopen = () => {
        ws.send(JSON.stringify({ type: 'join', username: myUsername }));
    };

    ws.onmessage = (event) => {
        const data = JSON.parse(event.data);

        if (data.type === 'history') {
            data.data.forEach(msg => appendMessage(msg.username, msg.message, msg.timestamp));
            scrollToBottom();
        } else if (data.type === 'chat') {
            appendMessage(data.username, data.message, data.timestamp);
            scrollToBottom();
        } else if (data.type === 'system') {
            appendSystemMessage(data.message);
            scrollToBottom();
        }
    };
}

document.getElementById('messageForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const input = document.getElementById('messageInput');
    const message = input.value.trim();

    if (message && ws && ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify({ type: 'chat', message: message }));
        input.value = '';
    }
});

function appendMessage(username, message, timestamp) {
    const isSentByMe = username === myUsername;
    const msgDiv = document.createElement('div');
    msgDiv.className = `message-box ${isSentByMe ? 'sent' : 'received'}`;

    const timeFormatted = new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

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
    chatMessages.scrollTo({
        top: chatMessages.scrollHeight,
        behavior: 'smooth'
    });
}
