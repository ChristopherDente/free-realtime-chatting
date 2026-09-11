<?php
// You can now run PHP logic at the top of your page before rendering the HTML!
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Free Realtime Chatting</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Login/Join Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog modal-dialog-centered px-3">
        <div class="modal-content border-0 shadow-lg blur-bg p-2">
          <div class="modal-header border-0 pb-0">
            <h4 class="modal-title fw-bold" id="loginModalLabel" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Welcome to Realtime</h4>
          </div>
          <div class="modal-body">
            <form id="joinForm">
                <div class="mb-4">
                    <label for="usernameInput" class="form-label text-muted fw-medium mb-2">Choose a username to join</label>
                    <input type="text" class="form-control form-control-lg custom-input" id="usernameInput" placeholder="christopherdev.online" required>
                </div>
                <button type="submit" class="btn btn-gradient w-100 btn-lg fw-bold rounded-pill" id="joinBtn">Join Chat</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Chat Interface -->
    <div class="chat-container d-none" id="chatContainer">
        <div class="chat-wrapper">
            
            <!-- Chat Header -->
            <div class="chat-header p-3 d-flex align-items-center justify-content-between shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar text-white fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm">
                        RC
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Live Room</h5>
                        <small class="text-success fw-medium d-flex align-items-center gap-1">
                            <span class="status-dot bg-success d-inline-block rounded-circle"></span> Online
                        </small>
                    </div>
                </div>
            </div>

            <!-- Chat Messages -->
            <div class="chat-messages" id="chatMessages">
                <!-- Messages will be appended here -->
            </div>

            <!-- Chat Input Area -->
            <div class="chat-input-area">
                <form id="messageForm" class="d-flex gap-2">
                    <input type="text" id="messageInput" class="form-control form-control-lg custom-input flex-grow-1" placeholder="Message..." required>
                    <button type="submit" class="send-btn shadow-sm">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap & App JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
</body>
</html>
