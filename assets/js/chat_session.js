// assets/js/chat_session.js

// Global variables
let selectedMessageId = null;
let isTyping = false;
let typingUrl = null;
let localRole = null;
let remoteRole = null;
let lastTypingTs = 0;
let typingHideTimer = null;
let typingIdleTimer = null;
let typingLastSentAt = 0;

const typingThrottleMs = 1200;
const typingIdleMs = 1800;
const typingVisibleMs = 4000;

// Auto scroll to bottom
function scrollToBottom() {
    const chatMessages = document.getElementById('chatMessages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
}

// Auto resize textarea
function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = (textarea.scrollHeight) + 'px';
    isTyping = textarea.value.length > 0;
    handleTypingInput(textarea.value);
}

function handleTypingInput(value) {
    if (!typingUrl || !localRole || typeof window.SESSION_ID === 'undefined') return;
    const trimmed = value.trim();
    if (!trimmed) {
        scheduleTypingStop();
        return;
    }

    const now = Date.now();
    if ((now - typingLastSentAt) >= typingThrottleMs) {
        typingLastSentAt = now;
        sendTyping('typing');
    }

    if (typingIdleTimer) {
        clearTimeout(typingIdleTimer);
    }
    typingIdleTimer = setTimeout(() => {
        sendTyping('stop');
    }, typingIdleMs);
}

function scheduleTypingStop() {
    if (!typingUrl || !localRole || typeof window.SESSION_ID === 'undefined') return;
    if (typingIdleTimer) {
        clearTimeout(typingIdleTimer);
    }
    typingIdleTimer = setTimeout(() => {
        sendTyping('stop');
    }, 0);
}

function sendTyping(status) {
    if (!typingUrl || !localRole || typeof window.SESSION_ID === 'undefined') return;
    const payload = new URLSearchParams({
        id_sesi: window.SESSION_ID,
        role: localRole,
        status
    });
    fetch(typingUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: payload
    }).catch(() => {});
}

function setTypingIndicator(isActive) {
    const indicator = document.getElementById('typingIndicator');
    if (!indicator) return;
    if (isActive) {
        indicator.style.display = 'flex';
        if (typingHideTimer) {
            clearTimeout(typingHideTimer);
        }
        typingHideTimer = setTimeout(() => {
            indicator.style.display = 'none';
        }, typingVisibleMs);
    } else {
        indicator.style.display = 'none';
        if (typingHideTimer) {
            clearTimeout(typingHideTimer);
        }
    }
}

function applyTypingState(typing) {
    if (!typing || !remoteRole) {
        setTypingIndicator(false);
        return;
    }
    setTypingIndicator(Boolean(typing[remoteRole]));
}

// Load messages with AJAX
function loadMessages() {
    if (typeof window.SESSION_ID === 'undefined' || typeof window.GET_MESSAGES_URL === 'undefined') return;
    let url = `${window.GET_MESSAGES_URL}?id_sesi=${window.SESSION_ID}`;
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const messagesContainer = document.getElementById('chatMessages');
                if (!messagesContainer) return;
                
                const currentCount = messagesContainer.querySelectorAll('.message:not(.system)').length;
                
                if (data.messages.length > currentCount) {
                    const wasScrolledToBottom = 
                        messagesContainer.scrollHeight - messagesContainer.scrollTop === messagesContainer.clientHeight;
                    
                    messagesContainer.innerHTML = '';
                    
                    if (data.messages.length === 0) {
                        messagesContainer.innerHTML = `
                            <div class="empty-conversation">
                                <div class="empty-icon">
                                    <i class="fas fa-comment-slash"></i>
                                </div>
                                <h4>Belum ada percakapan</h4>
                                <p>Mulai percakapan dengan mengirim pesan pertama</p>
                            </div>
                        `;
                    } else {
                        lastMessageId = 0;
                        data.messages.forEach(message => {
                            const messageDiv = createMessageElement(message);
                            messagesContainer.appendChild(messageDiv);
                            if (message.id && message.id > lastMessageId) {
                                lastMessageId = message.id;
                            }
                        });
                        
                        if (wasScrolledToBottom) {
                            setTimeout(scrollToBottom, 100);
                        }
                    }
                }
            }
        })
        .catch(error => console.error('Error loading messages:', error));
}

// Create message element
function createMessageElement(message) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${message.pengirim}`;
    messageDiv.dataset.id = message.id;
    messageDiv.dataset.time = message.dibuat_pada;
    
    const avatar = message.pengirim === 'dokter' ? 
        '<div class="avatar-doctor"><i class="fas fa-user-md"></i></div>' :
        message.pengirim === 'sistem' ?
        '<div class="avatar-system"><i class="fas fa-robot"></i></div>' :
        '<div class="avatar-patient"><i class="fas fa-user"></i></div>';
    
    const role = message.pengirim === 'dokter' ? 'Dokter' : 
                 message.pengirim === 'sistem' ? 'Sistem' : 'Pasien';
    
    const time = new Date(message.dibuat_pada).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    messageDiv.innerHTML = `
        <div class="message-avatar">${avatar}</div>
        <div class="message-content-wrapper">
            <div class="message-header">
                <div class="sender-info">
                    <strong class="sender-name">${message.nama_pengirim}</strong>
                    <span class="sender-role ${message.pengirim}">${role}</span>
                </div>
                <div class="message-meta">
                    <span class="message-time">
                        <i class="far fa-clock"></i> ${time}
                    </span>
                </div>
            </div>
            <div class="message-body">${message.pesan.replace(/\n/g, '<br>')}</div>
            <div class="message-actions">
                <button class="btn-message-action" onclick="copyMessage('${message.pesan.replace(/'/g, "\\'")}')" title="Salin">
                    <i class="far fa-copy"></i>
                </button>
                <button class="btn-message-action" onclick="replyToMessage(${message.id})" title="Balas">
                    <i class="fas fa-reply"></i>
                </button>
            </div>
        </div>
    `;
    
    return messageDiv;
}

// Send message
document.getElementById('chatForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (!message) return;

    try {
        const response = await fetch('send_message.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageInput.value = '';
            messageInput.style.height = 'auto';
            sendTyping('stop');
            const messagesContainer = document.getElementById('chatMessages');
            // If endpoint returned a single message, append it
            if (data.message) {
                messagesContainer.appendChild(createMessageElement(data.message));
                if (data.message.id && data.message.id > lastMessageId) {
                    lastMessageId = data.message.id;
                }
                setTimeout(scrollToBottom, 50);
            } else if (data.messages && Array.isArray(data.messages)) {
                messagesContainer.innerHTML = '';
                data.messages.forEach(message => {
                    messagesContainer.appendChild(createMessageElement(message));
                    if (message.id && message.id > lastMessageId) {
                        lastMessageId = message.id;
                    }
                });
                setTimeout(scrollToBottom, 100);
            } else {
                loadMessages();
            }
            showNotification('Pesan berhasil dikirim');
            messageInput.focus();
        } else {
            showNotification('Gagal mengirim pesan', 'error');
        }
    } catch (error) {
        console.error('Error sending message:', error);
        showNotification('Terjadi kesalahan', 'error');
    }
});

// Quick actions
document.querySelectorAll('.btn-quick').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('messageInput').value = this.dataset.text;
        document.getElementById('messageInput').focus();
        autoResize(document.getElementById('messageInput'));
    });
});

// Copy message
function copyMessage(text) {
    navigator.clipboard.writeText(text)
        .then(() => showNotification('Pesan disalin ke clipboard'))
        .catch(err => console.error('Copy failed:', err));
}

// Reply to message
function replyToMessage(messageId) {
    const messageElement = document.querySelector(`.message[data-id="${messageId}"]`);
    if (messageElement) {
        const messageText = messageElement.querySelector('.message-body').textContent;
        showNotification(`Membalas: ${messageText.substring(0, 50)}...`);
    }
}

// Show notification
function showNotification(message, type = 'success') {
    const toast = document.getElementById('notificationToast');
    const toastMessage = document.getElementById('toastMessage');
    
    if (toast && toastMessage) {
        toastMessage.textContent = message;
        toast.style.display = 'flex';
        
        setTimeout(() => {
            toast.style.display = 'none';
        }, 3000);
    }
}

// Print session
function printSession() {
    window.print();
}

// Export session
function exportSession() {
    if (typeof window.SESSION_ID !== 'undefined') {
        window.open(`export_session.php?id_sesi=${window.SESSION_ID}`, '_blank');
    }
}

// Clear chat
function clearChat() {
    if (confirm('Apakah Anda yakin ingin menghapus semua pesan?')) {
        showNotification('Fitur clear chat belum diimplementasi');
    }
}

// Auto refresh every 2 seconds
// Long-polling implementation
let lastMessageId = 0;
function getLongPollUrl() {
    const base = (typeof window.GET_MESSAGES_URL !== 'undefined') ? window.GET_MESSAGES_URL : 'get_messages.php';
    return base.replace(/get_messages\.php$/, 'get_messages_longpoll.php');
}

function initialLoadAndStart() {
    if (typeof window.SESSION_ID === 'undefined' || typeof window.GET_MESSAGES_URL === 'undefined') return;
    // load current messages once
    let url = `${window.GET_MESSAGES_URL}?id_sesi=${window.SESSION_ID}`;
    fetch(url)
        .then(r => r.json())
        .then(data => {
            const messagesContainer = document.getElementById('chatMessages');
            if (!messagesContainer) return;
            messagesContainer.innerHTML = '';
            if (data.messages && data.messages.length) {
                data.messages.forEach(message => {
                    messagesContainer.appendChild(createMessageElement(message));
                    if (message.id && message.id > lastMessageId) lastMessageId = message.id;
                });
            }
            scrollToBottom();
            startLongPoll();
        })
        .catch(err => {
            console.error('Initial load error', err);
            // still try long poll
            startLongPoll();
        });
}

function startLongPoll() {
    if (typeof window.SESSION_ID === 'undefined') return;
    const lpUrl = getLongPollUrl();
    let url = `${lpUrl}?id_sesi=${window.SESSION_ID}&since_id=${lastMessageId}&typing_ts=${lastTypingTs}`;
    if (typeof window.LONGPOLL_ONLY_FROM !== 'undefined' && window.LONGPOLL_ONLY_FROM) {
        url += `&only_from=${encodeURIComponent(window.LONGPOLL_ONLY_FROM)}`;
    } else if (typeof window.ONLY_FROM !== 'undefined' && window.ONLY_FROM) {
        url += `&only_from=${encodeURIComponent(window.ONLY_FROM)}`;
    }
    fetch(url, { cache: 'no-store' })
        .then(r => r.json())
        .then(data => {
            if (data && data.messages && data.messages.length) {
                const messagesContainer = document.getElementById('chatMessages');
                const wasAtBottom = messagesContainer.scrollHeight - messagesContainer.scrollTop === messagesContainer.clientHeight;
                data.messages.forEach(message => {
                    messagesContainer.appendChild(createMessageElement(message));
                    if (message.id && message.id > lastMessageId) lastMessageId = message.id;
                });
                if (wasAtBottom) setTimeout(scrollToBottom, 50);
            }
            if (data && typeof data.typing_ts !== 'undefined') {
                lastTypingTs = data.typing_ts;
            }
            if (data && data.typing) {
                applyTypingState(data.typing);
            }
            // reconnect immediately
            setTimeout(startLongPoll, 50);
        })
        .catch(err => {
            console.error('Long poll error', err);
            setTimeout(startLongPoll, 2000);
        });
}

// Initial setup
document.addEventListener('DOMContentLoaded', function() {
    typingUrl = typeof window.TYPING_URL !== 'undefined' ? window.TYPING_URL : null;
    localRole = typeof window.LOCAL_ROLE !== 'undefined' ? window.LOCAL_ROLE : null;
    remoteRole = typeof window.REMOTE_ROLE !== 'undefined' ? window.REMOTE_ROLE : null;

    scrollToBottom();
    initialLoadAndStart();
    
    // Enter to send, Shift+Enter for new line
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('chatForm').dispatchEvent(new Event('submit'));
            }
        });
        messageInput.addEventListener('blur', scheduleTypingStop);
        messageInput.focus();
    }
});

// Handle window resize
window.addEventListener('resize', scrollToBottom);
