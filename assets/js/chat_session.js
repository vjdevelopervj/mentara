// assets/js/chat_session.js

// Global variables
let selectedMessageId = null;
let isTyping = false;

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
}

// Load messages with AJAX
function loadMessages() {
    fetch(`get_messages.php?id_sesi=<?php echo $id_sesi; ?>`)
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
                        data.messages.forEach(message => {
                            const messageDiv = createMessageElement(message);
                            messagesContainer.appendChild(messageDiv);
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
    
    showTypingIndicator();
    
    try {
        const response = await fetch('send_message.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            messageInput.value = '';
            messageInput.style.height = 'auto';
            hideTypingIndicator();
            loadMessages();
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

// Show typing indicator
function showTypingIndicator() {
    const indicator = document.getElementById('typingIndicator');
    if (indicator) indicator.style.display = 'flex';
}

// Hide typing indicator
function hideTypingIndicator() {
    const indicator = document.getElementById('typingIndicator');
    if (indicator) indicator.style.display = 'none';
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
    window.open(`export_session.php?id_sesi=<?php echo $id_sesi; ?>`, '_blank');
}

// Clear chat
function clearChat() {
    if (confirm('Apakah Anda yakin ingin menghapus semua pesan?')) {
        showNotification('Fitur clear chat belum diimplementasi');
    }
}

// Auto refresh every 2 seconds
setInterval(loadMessages, 2000);

// Initial setup
document.addEventListener('DOMContentLoaded', function() {
    scrollToBottom();
    loadMessages();
    
    // Enter to send, Shift+Enter for new line
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('chatForm').dispatchEvent(new Event('submit'));
            }
        });
        messageInput.focus();
    }
});

// Handle window resize
window.addEventListener('resize', scrollToBottom);