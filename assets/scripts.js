(function($) {
    'use strict';

    class OpenAIChat {
        constructor(element) {
            this.element = element;
            this.messages = element.find('.chat-messages');
            this.input = element.find('.chat-input');
            this.submit = element.find('.chat-submit');
            this.threadId = null;
            this.isProcessing = false;

            this.bindEvents();
        }

        bindEvents() {
            this.submit.on('click', () => this.sendMessage());
            this.input.on('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
        }

        async sendMessage() {
            const message = this.input.val().trim();
            if (!message || this.isProcessing) return;

            this.isProcessing = true;
            this.submit.prop('disabled', true);
            this.addMessage(message, 'user');
            this.input.val('');

            this.showLoading();

            try {
                const response = await this.makeRequest(message);
                this.hideLoading();
                this.addMessage(response.response, 'assistant');
                this.threadId = response.thread_id;
            } catch (error) {
                this.hideLoading();
                this.showError(error.message);
            }

            this.isProcessing = false;
            this.submit.prop('disabled', false);
            this.input.focus();
        }

        addMessage(content, role) {
            const parsedContent = role === 'assistant' ? marked.parse(content) : this.escapeHtml(content);
            const messageHtml = `
                <div class="chat-message ${role}">
                    <div class="chat-message-content">${parsedContent}</div>
                </div>
            `;
            this.messages.append(messageHtml);
            this.scrollToBottom();
        }

        showLoading() {
            const loadingHtml = `
                <div class="chat-message-loading">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            `;
            this.messages.append(loadingHtml);
            this.scrollToBottom();
        }

        hideLoading() {
            this.messages.find('.chat-message-loading').remove();
        }

        showError(message) {
            const errorHtml = `
                <div class="openai-wrapper-error">
                    ${this.escapeHtml(message)}
                </div>
            `;
            this.messages.append(errorHtml);
            this.scrollToBottom();
        }

        scrollToBottom() {
            this.messages.scrollTop(this.messages[0].scrollHeight);
        }

        async makeRequest(message) {
            const response = await fetch(`${openAIWrapper.ajaxUrl}/chat`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': openAIWrapper.nonce
                },
                body: JSON.stringify({
                    message: message,
                    thread_id: this.threadId
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'An error occurred while processing your request.');
            }

            return data;
        }

        escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    }

    // Initialize chat interface
    $(document).ready(function() {
        $('.openai-wrapper-chat').each(function() {
            new OpenAIChat($(this));
        });
    });

})(jQuery); 