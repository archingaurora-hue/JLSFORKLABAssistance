<div class="offcanvas offcanvas-end shadow" tabindex="-1" id="chatOffcanvas" aria-labelledby="chatOffcanvasLabel" style="width: 400px; max-width: 100vw; z-index: 1070 !important;">
    <div class="offcanvas-header bg-primary text-white">
        <h5 class="offcanvas-title fw-bold" id="chatOffcanvasLabel">
            <i class="bi bi-chat-dots-fill me-2"></i> Order <span id="chatOrderIdDisplay"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column" style="background-color: #f4f6f9; padding: 0;">
        <div id="chatMessagesContainer" class="flex-grow-1 overflow-auto p-3 d-flex flex-column gap-2">
            <div class="text-center text-muted small mt-2">
                <div class="spinner-border spinner-border-sm text-primary mb-1" role="status"></div><br>
                Loading messages...
            </div>
        </div>

        <div class="p-3 bg-white border-top shadow-sm">
            <form id="chatSendForm" onsubmit="sendChatMessage(event)">
                <div class="input-group">
                    <input type="text" id="chatMessageInput" class="form-control" placeholder="Type your message..." required autocomplete="off">
                    <button class="btn btn-primary px-3 fw-bold" type="submit" id="chatSendBtn">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentChatOrderId = null;
    let chatSyncInterval = null;

    // Ensure DOM is fully loaded before attaching listeners
    document.addEventListener('DOMContentLoaded', function() {
        const offcanvasEl = document.getElementById('chatOffcanvas');
        if (offcanvasEl) {
            // Stop syncing when the chat menu is closed to save resources
            offcanvasEl.addEventListener('hidden.bs.offcanvas', function() {
                if (chatSyncInterval) clearInterval(chatSyncInterval);
                currentChatOrderId = null;
            });
        }
    });

    // Opens the chat menu and starts fetching messages
    function openChat(orderId) {
        console.log("Chat trigger clicked for Order ID:", orderId);

        // 1. Check if any Modals are currently open (Customer side) and safely hide them
        const openModal = document.querySelector('.modal.show');
        if (openModal) {
            const modalInstance = bootstrap.Modal.getInstance(openModal);
            if (modalInstance) {
                modalInstance.hide();
            }
        }

        currentChatOrderId = orderId;
        document.getElementById('chatOrderIdDisplay').innerText = '#' + orderId;
        document.getElementById('chatMessagesContainer').innerHTML = `
        <div class="text-center text-muted small mt-2">
            <div class="spinner-border spinner-border-sm text-primary mb-1" role="status"></div><br>
            Loading messages...
        </div>`;

        // 2. Safely get or create the Offcanvas instance
        const offcanvasElement = document.getElementById('chatOffcanvas');
        const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasElement);

        // 3. Show it (wrap in a slight timeout to let modal transitions clear out)
        setTimeout(() => {
            bsOffcanvas.show();
        }, 150);

        fetchMessages(); // Initial fetch

        // 4. Start live sync every 3 seconds
        if (chatSyncInterval) clearInterval(chatSyncInterval);
        chatSyncInterval = setInterval(fetchMessages, 3000);
    }

    // Fetch messages from the database
    function fetchMessages() {
        if (!currentChatOrderId) return;

        fetch('backend/fetch_messages.php?order_id=' + currentChatOrderId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderMessages(data.messages);
                } else {
                    console.error("Chat Error:", data.error);
                }
            })
            .catch(err => console.error("Network Error fetching messages:", err));
    }

    // Render the messages into the HTML container
    function renderMessages(messages) {
        const container = document.getElementById('chatMessagesContainer');
        if (!container) return;

        // Check if the user is scrolled to the bottom before we update the HTML
        const isScrolledToBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 50;

        if (messages.length === 0) {
            container.innerHTML = `
            <div class="text-center text-muted small mt-3 py-3 bg-light rounded border">
                <i class="bi bi-chat-square-text fs-3 d-block mb-2 text-secondary opacity-50"></i>
                No messages yet. Start the conversation!
            </div>`;
            return;
        }

        let html = '';
        messages.forEach(msg => {
            if (msg.is_mine) {
                // Outgoing Message (Right Side)
                html += `
                <div class="align-self-end text-end" style="max-width: 85%;">
                    <div class="bg-primary text-white rounded-3 py-2 px-3 mb-1 shadow-sm d-inline-block text-start" style="border-bottom-right-radius: 4px !important;">
                        ${escapeHtml(msg.message_text)}
                    </div>
                    <div class="small text-muted" style="font-size: 0.7rem;">${msg.time_sent}</div>
                </div>`;
            } else {
                // Incoming Message (Left Side)
                let roleBadge = msg.role === 'Customer' ? '' : `<span class="badge bg-secondary ms-1" style="font-size:0.6rem;">Staff</span>`;
                html += `
                <div class="align-self-start text-start" style="max-width: 85%;">
                    <div class="small text-muted mb-1" style="font-size: 0.75rem;">
                        <strong>${escapeHtml(msg.first_name)}</strong> ${roleBadge}
                    </div>
                    <div class="bg-white border rounded-3 py-2 px-3 mb-1 shadow-sm d-inline-block text-dark" style="border-bottom-left-radius: 4px !important;">
                        ${escapeHtml(msg.message_text)}
                    </div>
                    <div class="small text-muted" style="font-size: 0.7rem;">${msg.time_sent}</div>
                </div>`;
            }
        });

        container.innerHTML = html;

        // Auto-scroll to bottom if they were already at the bottom
        if (isScrolledToBottom) {
            container.scrollTop = container.scrollHeight;
        }
    }

    // Send a new message
    function sendChatMessage(e) {
        e.preventDefault();
        if (!currentChatOrderId) return;

        const input = document.getElementById('chatMessageInput');
        const msgText = input.value.trim();
        const btn = document.getElementById('chatSendBtn');

        if (msgText === '') return;

        // Disable input while sending to prevent duplicates
        input.disabled = true;
        btn.disabled = true;
        btn.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div>';

        const formData = new FormData();
        formData.append('order_id', currentChatOrderId);
        formData.append('message', msgText);

        fetch('backend/send_message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    input.value = ''; // Clear input
                    fetchMessages(); // Immediately show the sent message

                    // Force scroll to bottom immediately after sending
                    setTimeout(() => {
                        const container = document.getElementById('chatMessagesContainer');
                        if (container) container.scrollTop = container.scrollHeight;
                    }, 100);
                } else {
                    alert('Failed to send message: ' + data.error);
                }
            })
            .catch(err => {
                alert('A network error occurred while sending.');
                console.error(err);
            })
            .finally(() => {
                // Re-enable input
                input.disabled = false;
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-send-fill"></i>';
                input.focus();
            });
    }

    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>