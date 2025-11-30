<?php
/**
 * AI Chatbot Widget Component
 * Displays a floating chat widget on all pages
 */
$chatbot_base_path = isset($chatbot_base_path) ? $chatbot_base_path : '';
?>
<!-- Chatbot Widget -->
<div id="chatbotWidget" class="fixed bottom-6 right-6 z-50 hidden md:block">
    <!-- Chat Button -->
    <button id="chatbotToggle" 
        class="flex h-14 w-14 items-center justify-center rounded-full transition-all hover:scale-110 chatbot-button"
        aria-label="Open chat">
        <svg id="chatbotIcon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <svg id="chatbotCloseIcon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>

    <!-- Chat Window -->
    <div id="chatbotWindow" class="absolute bottom-20 right-0 hidden h-[500px] w-[380px] flex-col rounded-lg shadow-2xl"
        style="background-color: var(--bg-card); border: 1px solid var(--border-color);">
        <!-- Chat Header -->
        <div class="flex items-center justify-between rounded-t-lg px-4 py-3"
            style="background-color: var(--bg-secondary); border-bottom: 1px solid var(--border-color);">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-full"
                    style="background-color: var(--accent);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        <path d="M2 17l10 5 10-5"></path>
                        <path d="M2 12l10 5 10-5"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold" style="color: var(--text-primary);">AI Assistant</h3>
                    <p class="text-xs" style="color: var(--text-secondary);">We're here to help</p>
                </div>
            </div>
            <button id="chatbotMinimize" class="text-muted hover:text-primary transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
            </button>
        </div>

        <!-- Chat Messages -->
        <div id="chatbotMessages" class="flex-1 overflow-y-auto p-4 space-y-4">
            <!-- Welcome Message -->
            <div class="flex items-start gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                    style="background-color: var(--bg-secondary);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" style="color: var(--accent);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        <path d="M2 17l10 5 10-5"></path>
                        <path d="M2 12l10 5 10-5"></path>
                    </svg>
                </div>
                <div class="rounded-lg px-3 py-2 max-w-[80%]"
                    style="background-color: var(--bg-secondary);">
                    <p class="text-sm" style="color: var(--text-primary);">
                        Hi! I'm your AI assistant. I can help you find venues, answer questions about bookings, and guide you through the platform. How can I help you today?
                    </p>
                </div>
            </div>
        </div>

        <!-- Typing Indicator -->
        <div id="chatbotTyping" class="hidden px-4 pb-2">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                    style="background-color: var(--bg-secondary);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" style="color: var(--accent);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        <path d="M2 17l10 5 10-5"></path>
                        <path d="M2 12l10 5 10-5"></path>
                    </svg>
                </div>
                <div class="flex gap-1">
                    <div class="h-2 w-2 rounded-full animate-bounce" style="background-color: var(--text-muted); animation-delay: 0ms;"></div>
                    <div class="h-2 w-2 rounded-full animate-bounce" style="background-color: var(--text-muted); animation-delay: 150ms;"></div>
                    <div class="h-2 w-2 rounded-full animate-bounce" style="background-color: var(--text-muted); animation-delay: 300ms;"></div>
                </div>
            </div>
        </div>

        <!-- Chat Input -->
        <div class="border-t p-4" style="border-color: var(--border-color);">
            <form id="chatbotForm" class="flex gap-2">
                <input type="text" 
                    id="chatbotInput" 
                    placeholder="Type your message..." 
                    class="flex-1 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2"
                    style="background-color: var(--bg-primary); color: var(--text-primary); border: 1px solid var(--border-color); focus:ring-color: var(--accent);"
                    autocomplete="off">
                <button type="submit" 
                    class="flex h-9 w-9 items-center justify-center rounded-lg transition-colors"
                    style="background-color: var(--accent); color: #ffffff;"
                    onmouseover="this.style.backgroundColor='var(--accent-hover)'"
                    onmouseout="this.style.backgroundColor='var(--accent)'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-4px); }
}
.animate-bounce {
    animation: bounce 1s infinite;
}

/* Chatbot button styling - Orange in dark mode */
.chatbot-button {
    background-color: var(--accent);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(255, 107, 53, 0.4), 0 2px 4px rgba(0, 0, 0, 0.1);
    border: 2px solid rgba(255, 255, 255, 0.2);
}

/* Chatbot button - Black in light mode for better visibility */
[data-theme="light"] .chatbot-button {
    background-color: #0a0a0a;
    color: #ffffff;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3), 0 2px 8px rgba(0, 0, 0, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.1);
}

[data-theme="light"] .chatbot-button:hover {
    background-color: #1a1a1a;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4), 0 4px 10px rgba(0, 0, 0, 0.25);
}

/* Dark mode hover */
.chatbot-button:hover {
    box-shadow: 0 4px 20px rgba(255, 107, 53, 0.6), 0 2px 8px rgba(0, 0, 0, 0.2);
}
</style>

<script>
(function() {
    const widget = document.getElementById('chatbotWidget');
    const toggle = document.getElementById('chatbotToggle');
    const window = document.getElementById('chatbotWindow');
    const minimize = document.getElementById('chatbotMinimize');
    const messages = document.getElementById('chatbotMessages');
    const form = document.getElementById('chatbotForm');
    const input = document.getElementById('chatbotInput');
    const typing = document.getElementById('chatbotTyping');
    const icon = document.getElementById('chatbotIcon');
    const closeIcon = document.getElementById('chatbotCloseIcon');
    
    let isOpen = false;
    let chatHistory = [];

    // Toggle chat window
    toggle.addEventListener('click', () => {
        isOpen = !isOpen;
        if (isOpen) {
            window.classList.remove('hidden');
            window.classList.add('flex');
            icon.classList.add('hidden');
            closeIcon.classList.remove('hidden');
            input.focus();
        } else {
            window.classList.add('hidden');
            window.classList.remove('flex');
            icon.classList.remove('hidden');
            closeIcon.classList.add('hidden');
        }
    });

    // Minimize chat
    minimize.addEventListener('click', () => {
        isOpen = false;
        window.classList.add('hidden');
        window.classList.remove('flex');
        icon.classList.remove('hidden');
        closeIcon.classList.add('hidden');
    });

    // Add user message
    function addUserMessage(text) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'flex items-start gap-2 justify-end';
        messageDiv.innerHTML = `
            <div class="rounded-lg px-3 py-2 max-w-[80%]" style="background-color: var(--accent); color: #ffffff;">
                <p class="text-sm">${escapeHtml(text)}</p>
            </div>
        `;
        messages.appendChild(messageDiv);
        messages.scrollTop = messages.scrollHeight;
    }

    // Add bot message
    function addBotMessage(text) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'flex items-start gap-2';
        messageDiv.innerHTML = `
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full" style="background-color: var(--bg-secondary);">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" style="color: var(--accent);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                    <path d="M2 17l10 5 10-5"></path>
                    <path d="M2 12l10 5 10-5"></path>
                </svg>
            </div>
            <div class="rounded-lg px-3 py-2 max-w-[80%]" style="background-color: var(--bg-secondary);">
                <div class="text-sm" style="color: var(--text-primary);">${sanitizeHtml(text)}</div>
            </div>
        `;
        messages.appendChild(messageDiv);
        messages.scrollTop = messages.scrollHeight;
    }

    // Sanitize HTML - allow safe tags like <a> while escaping others
    function sanitizeHtml(text) {
        if (!text || typeof text !== 'string') return '';
        
        // Create a temporary div to parse the HTML
        const div = document.createElement('div');
        div.innerHTML = text;
        
        // Get all elements
        const allElements = div.querySelectorAll('*');
        
        // Whitelist of allowed tags and attributes
        const allowedTags = ['a', 'strong', 'em', 'br', 'p'];
        const allowedAttributes = {
            'a': ['href', 'style']
        };
        
        // Process each element (iterate backwards to avoid issues with removing elements)
        for (let i = allElements.length - 1; i >= 0; i--) {
            const element = allElements[i];
            const tagName = element.tagName.toLowerCase();
            
            // If tag is not allowed, replace with text content
            if (!allowedTags.includes(tagName)) {
                const textNode = document.createTextNode(element.textContent);
                element.parentNode.replaceChild(textNode, element);
            } else {
                // Remove disallowed attributes
                Array.from(element.attributes).forEach(attr => {
                    if (!allowedAttributes[tagName] || !allowedAttributes[tagName].includes(attr.name)) {
                        element.removeAttribute(attr.name);
                    }
                });
            }
        }
        
        return div.innerHTML;
    }

    // Escape HTML (for user messages)
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Handle form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const userMessage = input.value.trim();
        if (!userMessage) return;

        // Add user message
        addUserMessage(userMessage);
        chatHistory.push({ role: 'user', content: userMessage });
        input.value = '';
        input.disabled = true;

        // Show typing indicator
        typing.classList.remove('hidden');

        try {
            const response = await fetch('<?php echo $chatbot_base_path; ?>actions/chatbot_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    message: userMessage,
                    history: chatHistory.slice(-10) // Last 10 messages for context
                })
            });

            const data = await response.json();
            
            if (data.success) {
                addBotMessage(data.response);
                chatHistory.push({ role: 'assistant', content: data.response });
            } else {
                addBotMessage('Sorry, I encountered an error. Please try again.');
            }
        } catch (error) {
            console.error('Chat error:', error);
            addBotMessage('Sorry, I\'m having trouble connecting. Please try again later.');
        } finally {
            typing.classList.add('hidden');
            input.disabled = false;
            input.focus();
        }
    });
})();
</script>

