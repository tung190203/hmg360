<!-- Chatbot Floating Button -->
<div id="ai-chatbot-btn-container" class="chatbot-btn-container">
    <div class="chatbot-tooltip">{{ \App\Models\Setting::getSettingByKey('chatbot_tooltip', __('app.assistant_ready')) }}</div>
    <div id="ai-chatbot-btn" class="chatbot-floating-btn" onclick="toggleChatbot()">
        <i class="fal fa-comment-alt-lines fa-2x text-white"></i>
    </div>
</div>

<!-- Chatbot Window -->
<div id="ai-chatbot-window" class="chatbot-window">
    <div class="chatbot-header">
        <div class="d-flex align-items-center" style="min-width:0; flex:1; overflow:hidden;">
            <div class="chatbot-avatar" style="flex-shrink:0;">
                @php
                    $cb_avatar = \App\Models\Setting::getSettingByKey('chatbot_avatar');
                @endphp
                @if(!empty($cb_avatar))
                    <img src="{{ asset($cb_avatar) }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                @else
                    <i class="fas fa-robot"></i>
                @endif
            </div>
            <div class="ms-2" style="min-width:0; overflow:hidden;">
                <div class="d-flex align-items-center">
                    <h5 class="mb-0 text-white" style="font-size: 15px; font-weight: 600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ \App\Models\Setting::getSettingByKey('chatbot_name', __('app.assistant_ai')) }}</h5>
                    <span id="chatbot-status-dot" class="ms-2" style="width: 8px; height: 8px; background-color: #94a3b8; border-radius: 50%; display: inline-block; flex-shrink:0;" title="{{ __('app.checking_status') }}"></span>
                </div>
                <div class="d-flex align-items-center mb-1">
                    <span id="chatbot-status-text" class="text-white-50" style="font-size: 11px;">{{ __('app.checking_status') }}</span>
                </div>
                <div id="chatbot-stage-indicator" class="text-white-50" style="font-size: 10px; display: none; line-height: 1;"></div>
            </div>
        </div>
        <div class="chatbot-actions">
            <button onclick="deleteChatSession()" class="text-white" title="{{ __('app.chatbot_delete_title') }}"><i class="fal fa-trash-alt"></i></button>
            <button id="chatbot-expand-btn" onclick="toggleExpandChatbot()" class="text-white" title="{{ __('app.chatbot_expand_title') }}"><i class="fal fa-expand-alt"></i></button>
            <button onclick="toggleChatbot()" class="text-white" title="{{ __('app.chatbot_close_title') }}"><i class="fal fa-times"></i></button>
        </div>
    </div>

    <div class="chatbot-body" id="chatbot-messages">
        <div class="chatbot-message bot-message" data-id="welcome">
            <div class="message-content">
                {{ \App\Models\Setting::getSettingByKey('chatbot_welcome_message', __('app.chatbot_welcome')) }}
            </div>
        </div>
    </div>

    <!-- Feedback Modal (Simple Overlay) -->
    <div id="chatbot-feedback-modal" class="chatbot-modal">
        <div class="chatbot-modal-content">
            <h6 class="mb-3">{{ __('app.chatbot_feedback_title') }}</h6>
            <input type="hidden" id="feedback-message-id">
            <input type="hidden" id="feedback-rating">
            <div class="mb-3">
                <label class="form-label small">{{ __('app.chatbot_feedback_reason') }}</label>
                <select id="feedback-type" class="form-select form-select-sm">
                    <option value="helpful">{{ __('app.chatbot_feedback_helpful') }}</option>
                    <option value="not_helpful">{{ __('app.chatbot_feedback_not_helpful') }}</option>
                    <option value="incorrect">{{ __('app.chatbot_feedback_incorrect') }}</option>
                    <option value="other">{{ __('app.chatbot_feedback_other') }}</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small">{{ __('app.chatbot_feedback_comment') }}</label>
                <textarea id="feedback-comment" class="form-control form-control-sm" rows="3" placeholder="{{ __('app.chatbot_feedback_placeholder') }}"></textarea>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <button onclick="hideFeedbackModal()" class="btn btn-sm btn-light">{{ __('app.chatbot_feedback_cancel') }}</button>
                <button onclick="submitFeedbackForm()" class="btn btn-sm text-white btn-primary">{{ __('app.chatbot_feedback_send') }}</button>
            </div>
        </div>
    </div>

    <div class="chatbot-suggested-actions" id="chatbot-suggested-actions" style="display: none;">
        <!-- Suggested actions will be appended here -->
    </div>

    <!-- Scroll to Bottom Button -->
    <div id="chatbot-scroll-btn" class="chatbot-scroll-btn" onclick="scrollToBottom()" title="{{ __('app.chatbot_scroll_down') }}">
        <i class="fas fa-chevron-down"></i>
    </div>

    <div class="chatbot-footer">
        <form id="chatbot-form" onsubmit="sendChatMessage(event)">
            <div class="chatbot-input-group">
                <input type="text" id="chatbot-input" placeholder="{{ __('app.chatbot_input_placeholder') }}" autocomplete="off">
                <button type="button" id="chatbot-mic-btn" onclick="toggleSpeechRecognition()" aria-label="Voice input" title="Nhập bằng giọng nói">
                    <i class="fas fa-microphone"></i>
                </button>
                <button type="submit" id="chatbot-send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Premium Chatbot Styling */
    :root {
        --cb-primary: {{ $setting['chatbot_primary_color'] ?? '#1a6fc4' }};
        --cb-primary-gradient: linear-gradient(135deg, {{ $setting['chatbot_primary_color'] ?? '#1a6fc4' }}, #00d2ff);
        --cb-bg: #f8fafc;
        --cb-text: #334155;
        --cb-bot-msg: #ffffff;
        --cb-user-msg: {{ $setting['chatbot_primary_color'] ?? '#1a6fc4' }};
        --cb-border: #e2e8f0;
    }

    .chatbot-btn-container {
        position: fixed;
        bottom: 30px;
        right: 30px;
        display: flex;
        align-items: center;
        z-index: 10000;
    }

    .chatbot-tooltip {
        background: white;
        padding: 8px 15px;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        margin-right: 15px;
        font-size: 13px;
        font-weight: 500;
        color: var(--cb-text);
        white-space: nowrap;
        animation: fadeInRight 0.5s ease-out forwards, floatingTooltip 3s ease-in-out infinite;
        position: relative;
        border: 1px solid var(--cb-border);
    }

    .chatbot-tooltip::after {
        content: '';
        position: absolute;
        right: -6px;
        top: 50%;
        transform: translateY(-50%) rotate(45deg);
        width: 10px;
        height: 10px;
        background: white;
        border-right: 1px solid var(--cb-border);
        border-top: 1px solid var(--cb-border);
    }

    .chatbot-floating-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: var(--cb-primary-gradient);
        box-shadow: 0 10px 25px rgba(26, 111, 196, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s;
        animation: bounce 4s infinite;
    }

    .chatbot-floating-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 15px 35px rgba(26, 111, 196, 0.5);
    }

    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-15px); }
        60% { transform: translateY(-7px); }
    }

    @keyframes floatingTooltip {
        0%, 100% { transform: translateX(0); }
        50% { transform: translateX(-5px); }
    }

    @keyframes fadeInRight {
        from { opacity: 0; transform: translateX(20px); }
        to { opacity: 1; transform: translateX(0); }
    }

    .chatbot-window {
        position: fixed;
        bottom: 105px;
        right: 30px;
        width: 380px;
        max-width: calc(100vw - 40px);
        height: 600px;
        max-height: calc(100vh - 120px);
        background: var(--cb-bg);
        border-radius: 20px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        z-index: 10000;
        opacity: 0;
        transform: translateY(20px);
        pointer-events: none;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }

    .chatbot-window.active {
        opacity: 1;
        transform: translateY(0);
        pointer-events: all;
    }

    /* Expanded (fullscreen) state */
    .chatbot-window.expanded {
        width: calc(100vw - 60px);
        max-width: 1200px;
        height: calc(100vh - 80px);
        max-height: calc(100vh - 80px);
        bottom: 30px;
        right: 30px;
        border-radius: 24px;
    }

    @media (max-width: 768px) {
        .chatbot-window.expanded {
            width: calc(100vw - 20px);
            height: calc(100vh - 60px);
            bottom: 10px;
            right: 10px;
        }
    }

    .chatbot-header {
        background: var(--cb-primary-gradient);
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        z-index: 3;
    }

    .chatbot-avatar {
        width: 40px;
        height: 40px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        backdrop-filter: blur(5px);
    }

    .chatbot-actions {
        display: flex;
        align-items: center;
        flex-shrink: 0;
        gap: 2px;
        margin-left: 8px;
    }

    .chatbot-actions button {
        background: none;
        border: none;
        outline: none;
        padding: 4px 6px;
        border-radius: 8px;
        transition: background 0.2s;
        line-height: 1;
    }

    .chatbot-actions button:hover {
        background: rgba(255,255,255,0.1);
    }

    .chatbot-body {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        scroll-behavior: smooth;
    }

    .chatbot-body::-webkit-scrollbar {
        width: 6px;
    }
    .chatbot-body::-webkit-scrollbar-track {
        background: transparent;
    }
    .chatbot-body::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .chatbot-message {
        display: flex;
        flex-direction: column;
        max-width: 85%;
        animation: fadeIn 0.3s ease-out forwards;
        position: relative;
    }

    .chatbot-message.bot-message {
        align-self: flex-start;
        padding-right: 20px;
    }

    .chatbot-message.user-message {
        align-self: flex-end;
    }

    .message-content {
        padding: 12px 16px;
        border-radius: 16px;
        font-size: 14px;
        line-height: 1.5;
        position: relative;
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        word-wrap: break-word;
    }

    .bot-message .message-content {
        background: var(--cb-bot-msg);
        color: var(--cb-text);
        border-bottom-left-radius: 4px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .user-message .message-content {
        background: var(--cb-primary-gradient);
        color: white;
        border-bottom-right-radius: 4px;
    }

    /* Feedback buttons */
    .chatbot-message-feedback {
        display: flex;
        gap: 8px;
        margin-top: 5px;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .bot-message:hover .chatbot-message-feedback {
        opacity: 1;
    }

    .feedback-btn {
        background: none;
        border: none;
        color: #94a3b8;
        font-size: 12px;
        cursor: pointer;
        transition: color 0.2s;
        padding: 2px 4px;
    }

    .feedback-btn:hover {
        color: var(--cb-primary);
    }
    .feedback-btn.active {
        color: var(--cb-primary);
    }

    .chatbot-metadata {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 8px;
    }

    .chatbot-entity-tag {
        font-size: 11px;
        background: #e2e8f0;
        color: #475569;
        padding: 3px 8px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .chatbot-entity-tag i {
        font-size: 10px;
        color: var(--cb-primary);
    }

    .chatbot-related-items {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 12px;
        width: 100%;
    }

    .related-item-card {
        background: white;
        border: 1px solid var(--cb-border);
        border-radius: 12px;
        padding: 12px;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        color: var(--cb-text);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }

    .related-item-card:hover {
        border-color: var(--cb-primary);
        box-shadow: 0 8px 20px rgba(26, 111, 196, 0.12);
        transform: translateY(-3px);
        color: var(--cb-primary);
    }

    .related-item-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--cb-primary);
        font-size: 18px;
        flex-shrink: 0;
    }

    .related-item-info {
        flex: 1;
        overflow: hidden;
    }

    .related-item-title {
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 2px;
    }

    .chatbot-suggested-actions {
        padding: 10px 20px;
        display: flex;
        overflow-x: auto;
        gap: 8px;
        border-top: 1px solid var(--cb-border);
        background: white;
    }

    .chatbot-suggested-actions::-webkit-scrollbar {
        height: 0px; 
    }

    .suggested-action-btn {
        white-space: nowrap;
        background: white;
        border: 1px solid var(--cb-primary);
        color: var(--cb-primary);
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .suggested-action-btn:hover {
        background: var(--cb-primary);
        color: white;
    }

    .retry-action-btn {
        background: white;
        color: #ef4444;
        border-color: #ef4444;
    }
    
    .retry-action-btn:hover, .retry-action-btn:active {
        background: #ef4444 !important;
        color: white !important;
        border-color: #ef4444 !important;
    }

    .chatbot-suggested-actions.active-drag {
        cursor: grabbing;
        cursor: -webkit-grabbing;
    }
    
    .chatbot-suggested-actions.active-drag .suggested-action-btn {
        pointer-events: none;
    }

    .chatbot-footer {
        padding: 15px 20px;
        background: white;
        border-top: 1px solid var(--cb-border);
        z-index: 2;
    }

    .chatbot-input-group {
        display: flex;
        align-items: center;
        background: #f1f5f9;
        border-radius: 24px;
        padding: 4px 4px 4px 15px;
        border: 1px solid transparent;
        transition: border-color 0.2s;
    }

    .chatbot-input-group:focus-within {
        border-color: var(--cb-primary);
        background: white;
        box-shadow: 0 0 0 3px rgba(26, 111, 196, 0.1);
    }

    .chatbot-input-group input {
        flex: 1;
        border: none;
        background: transparent;
        outline: none;
        font-size: 14px;
        color: var(--cb-text);
    }

    .chatbot-input-group button {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: none;
        background: var(--cb-primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s, box-shadow 0.2s, color 0.2s;
        margin-left: 6px;
    }

    .chatbot-input-group button:hover {
        background: #155799;
    }

    .chatbot-input-group button:disabled {
        background: #cbd5e1;
    }
    
    #chatbot-mic-btn {
        background: transparent;
        color: #94a3b8;
    }
    
    #chatbot-mic-btn:hover {
        background: rgba(0,0,0,0.05);
        color: var(--cb-primary);
    }
    
    #chatbot-mic-btn.recording {
        background: #ef4444;
        color: white;
        animation: pulseRecording 1.5s infinite;
    }
    
    @keyframes pulseRecording {
        0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
        70% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
        100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }

    /* Modal Styling */
    .chatbot-modal {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10;
        padding: 20px;
    }
    .chatbot-modal.active {
        display: flex;
    }
    .chatbot-modal-content {
        background: white;
        border-radius: 15px;
        padding: 20px;
        width: 100%;
        max-width: 300px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    .typing-indicator {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 12px 16px;
        background: var(--cb-bot-msg);
        border: 1px solid rgba(0,0,0,0.05);
        border-radius: 16px;
        border-bottom-left-radius: 4px;
        width: fit-content;
    }

    .typing-dot {
        width: 6px;
        height: 6px;
        background: #94a3b8;
        border-radius: 50%;
        animation: typing 1.4s infinite ease-in-out both;
    }

    .typing-dot:nth-child(1) { animation-delay: -0.32s; }
    .typing-dot:nth-child(2) { animation-delay: -0.16s; }

    @keyframes typing {
        0%, 80%, 100% { transform: scale(0); }
        40% { transform: scale(1); }
    }

    /* Scroll to bottom button */
    .chatbot-scroll-btn {
        position: absolute;
        bottom: 80px; /* ngay trên footer */
        left: 50%;
        transform: translateX(-50%) translateY(10px);
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: white;
        border: 1px solid var(--cb-border);
        box-shadow: 0 4px 16px rgba(0,0,0,0.14);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: var(--cb-primary);
        font-size: 14px;
        z-index: 5;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease, transform 0.25s ease, box-shadow 0.2s, bottom 0.25s ease;
    }
    .chatbot-scroll-btn.visible {
        opacity: 1;
        pointer-events: all;
        transform: translateX(-50%) translateY(0);
    }
    .chatbot-scroll-btn:hover {
        background: var(--cb-primary);
        color: white;
        box-shadow: 0 6px 20px rgba(26,111,196,0.3);
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Markdown Table Styling */
    .table-responsive {
        overflow-x: auto;
        margin: 10px 0;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
    }
    .chatbot-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        background: white;
        border: 1px solid #cbd5e1;
    }
    .chatbot-table th {
        background: #f1f5f9;
        color: #1e293b;
        font-weight: 700;
        padding: 10px 12px;
        text-align: left;
        border: 1px solid #cbd5e1;
        white-space: nowrap;
    }
    .chatbot-table td {
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        color: #334155;
        line-height: 1.5;
    }
    .chatbot-table tr:nth-child(even) {
        background: #f8fafc;
    }
    .bot-message .message-content h3 {
        font-size: 16px;
        margin-top: 15px;
        margin-bottom: 10px;
        font-weight: 700;
        color: var(--cb-primary);
    }
    .bot-message .message-content ul, .bot-message .message-content ol {
        padding-left: 20px;
        margin-bottom: 10px;
    }
    .bot-message .message-content li {
        margin-bottom: 5px;
    }

    /* =========================================
       RESPONSIVE - Mobile Small (≤ 480px)
    ========================================= */
    @media (max-width: 480px) {
        /* Floating button + tooltip */
        .chatbot-btn-container {
            bottom: 16px;
            right: 16px;
        }
        .chatbot-tooltip {
            display: none; /* ẩn tooltip trên mobile nhỏ */
        }
        .chatbot-floating-btn {
            width: 52px;
            height: 52px;
        }

        /* Chat window chiếm gần toàn màn hình */
        .chatbot-window {
            bottom: 0;
            right: 0;
            left: 0;
            width: 100vw;
            max-width: 100vw;
            height: 92dvh;            /* dùng dvh để tránh thanh browser */
            max-height: 92dvh;
            border-radius: 20px 20px 0 0;
            transform: translateY(30px);
        }
        .chatbot-window.active {
            transform: translateY(0);
        }

        /* Expanded = full screen trên mobile */
        .chatbot-window.expanded {
            bottom: 0;
            right: 0;
            left: 0;
            width: 100vw;
            height: 100dvh;
            max-height: 100dvh;
            border-radius: 0;
        }

        /* Header compact hơn */
        .chatbot-header {
            padding: 12px 14px;
        }
        .chatbot-avatar {
            width: 34px;
            height: 34px;
            font-size: 16px;
        }

        /* Body */
        .chatbot-body {
            padding: 14px;
            gap: 12px;
        }
        .chatbot-message {
            max-width: 92%;
        }
        .message-content {
            font-size: 13px;
            padding: 10px 13px;
        }

        /* Input */
        .chatbot-footer {
            padding: 10px 14px;
            /* Tránh bàn phím ảo che input */
            padding-bottom: env(safe-area-inset-bottom, 10px);
        }
        .chatbot-input-group input {
            font-size: 16px; /* Ngăn iOS auto-zoom */
        }
        .chatbot-input-group button {
            width: 34px;
            height: 34px;
        }

        /* Suggested actions */
        .chatbot-suggested-actions {
            padding: 8px 14px;
        }
        .suggested-action-btn {
            font-size: 12px;
            padding: 5px 11px;
        }

        /* Related items */
        .related-item-card {
            padding: 10px;
            font-size: 12px;
        }
        .related-item-icon {
            width: 34px;
            height: 34px;
            font-size: 15px;
        }

        /* Feedback hover → always visible trên touch */
        .chatbot-message-feedback {
            opacity: 1;
        }
    }

    /* =========================================
       RESPONSIVE - Tablet (481px – 768px)
    ========================================= */
    @media (min-width: 481px) and (max-width: 768px) {
        .chatbot-btn-container {
            bottom: 20px;
            right: 20px;
        }
        .chatbot-tooltip {
            font-size: 12px;
            padding: 6px 12px;
        }
        .chatbot-window {
            width: calc(100vw - 40px);
            max-width: 400px;
            height: 75vh;
            max-height: 75vh;
            bottom: 90px;
            right: 20px;
        }
        .chatbot-window.expanded {
            width: calc(100vw - 32px);
            height: calc(100vh - 40px);
            bottom: 20px;
            right: 16px;
            border-radius: 20px;
        }
        .chatbot-message {
            max-width: 88%;
        }
        .chatbot-input-group input {
            font-size: 16px; /* Ngăn iOS auto-zoom */
        }

        /* Feedback luôn hiển thị trên touch */
        .chatbot-message-feedback {
            opacity: 1;
        }

        .bot-message .message-content li {
            margin-bottom: 5px;
        }
    }

    /* =========================================
       RESPONSIVE - Desktop lớn (≥ 1024px)
    ========================================= */
    @media (min-width: 1024px) {
        .chatbot-window {
            width: 400px;
            height: 640px;
        }
        .chatbot-window.expanded {
            width: calc(100vw - 80px);
            max-width: 1280px;
            height: calc(100vh - 80px);
            bottom: 40px;
            right: 40px;
        }
        /* Tooltip đẹp hơn trên màn lớn */
        .chatbot-tooltip {
            font-size: 13px;
        }
    }

    /* =========================================
       Safe area (iPhone notch / Dynamic Island)
    ========================================= */
    @supports (padding: env(safe-area-inset-bottom)) {
        @media (max-width: 480px) {
            .chatbot-window {
                padding-bottom: env(safe-area-inset-bottom);
            }
            .chatbot-btn-container {
                bottom: calc(16px + env(safe-area-inset-bottom));
            }
        }
    }
</style>

<script>
    // Localization strings passed from Blade
    const chatbotLang = {
        deleteTitle:        @json(__('app.chatbot_delete_confirm_title')),
        deleteCancel:        @json(__('app.chatbot_cancel')),
        deleteText:         @json(__('app.chatbot_delete_confirm_text')),
        deleteConfirmBtn:   @json(__('app.chatbot_delete_confirm_btn')),
        deleting:           @json(__('app.chatbot_deleting')),
        deleteNewMsg:       @json(__('app.chatbot_delete_new_msg')),
        deleteSuccess:      @json(__('app.chatbot_delete_success')),
        deleteError:        @json(__('app.chatbot_delete_error')),
        statusHealthy:      @json(__('app.chatbot_status_healthy')),
        statusError:        @json(__('app.chatbot_status_error')),
        stageLabel:         @json(__('app.chatbot_stage_label')),
        onlineLabel:        @json(__('app.chatbot_online_label')),
        errorConnection:    @json(__('app.chatbot_error_connection')),
        errorNetwork:       @json(__('app.chatbot_error_network')),
        errorLabel:         @json(__('app.chatbot_error_label')),
        thankYou:           @json(__('app.chatbot_thank_you')),
        feedbackSent:       @json(__('app.chatbot_feedback_sent')),
        feedbackError:      @json(__('app.chatbot_feedback_error')),
        feedbackUseful:     @json(__('app.chatbot_feedback_useful')),
        feedbackNotUseful:  @json(__('app.chatbot_feedback_not_useful')),
        expandTitle:        @json(__('app.chatbot_expand_title')),
        compressTitle:      @json(__('app.chatbot_compress_title')),
        viewProject:        @json(__('app.chatbot_view_project')),
        viewDocument:       @json(__('app.chatbot_view_document')),
        defaultItemName:    @json(__('app.chatbot_default_item_name')),
        retryBtn:           @json(__('app.chatbot_retry_btn')),
        inputPlaceholder:   @json(__('app.chatbot_input_placeholder')),
        listening:          @json(__('app.chatbot_listening')),
    };

    // Generate or retrieve session ID
    function getChatSessionId() {
        let sid = localStorage.getItem('ttxt_chat_session_id');
        if (!sid) {
            sid = 'user-' + Math.random().toString(36).substring(2, 10);
            localStorage.setItem('ttxt_chat_session_id', sid);
        }
        return sid;
    }

    async function deleteChatSession() {
        const result = await Swal.fire({
            title: chatbotLang.deleteTitle,
            text: chatbotLang.deleteText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: chatbotLang.deleteConfirmBtn,
            cancelButtonText: chatbotLang.deleteCancel
        });

        if (!result.isConfirmed) return;

        const sid = getChatSessionId();
        const messagesContainer = document.getElementById('chatbot-messages');
        messagesContainer.innerHTML = `<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> ${chatbotLang.deleting}</div>`;

        try {
            await fetch(`/chat/session/${sid}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
            });

            localStorage.removeItem('ttxt_chat_session_id');
            const newSid = getChatSessionId();
            
            messagesContainer.innerHTML = `
                <div class="chatbot-message bot-message">
                    <div class="message-content">
                        ${chatbotLang.deleteNewMsg}
                    </div>
                </div>
            `;
            document.getElementById('chatbot-suggested-actions').style.display = 'none';
            document.getElementById('chatbot-scroll-btn').style.bottom = '80px';
            document.getElementById('chatbot-status-text').innerText = chatbotLang.onlineLabel;
            const stageInd = document.getElementById('chatbot-stage-indicator');
            stageInd.innerText = '';
            stageInd.style.display = 'none';

            Swal.fire({
                icon: 'success',
                title: chatbotLang.deleteSuccess,
                showConfirmButton: false,
                timer: 1500,
                toast: true,
                position: 'top-end'
            });
        } catch (e) { 
            console.error(e);
            Swal.fire(chatbotLang.errorLabel, chatbotLang.deleteError, 'error');
        }
    }

    function toggleChatbot() {
        const windowEl = document.getElementById('ai-chatbot-window');
        const tooltipEl = document.querySelector('.chatbot-tooltip');
        windowEl.classList.toggle('active');
        if(windowEl.classList.contains('active')) {
            document.getElementById('chatbot-input').focus();
            scrollToBottom();
            if(tooltipEl) tooltipEl.style.display = 'none'; // Hide tooltip when open
        } else {
            // Reset expanded state when closing
            windowEl.classList.remove('expanded');
            const expandBtn = document.getElementById('chatbot-expand-btn');
            if (expandBtn) {
                expandBtn.title = chatbotLang.expandTitle;
                expandBtn.querySelector('i').className = 'fal fa-expand-alt';
            }
            if(tooltipEl) tooltipEl.style.display = 'block'; // Show tooltip when closed
        }
    }

    function toggleExpandChatbot() {
        const windowEl = document.getElementById('ai-chatbot-window');
        const expandBtn = document.getElementById('chatbot-expand-btn');
        const isExpanded = windowEl.classList.toggle('expanded');
        if (isExpanded) {
            expandBtn.title = chatbotLang.compressTitle;
            expandBtn.querySelector('i').className = 'fal fa-compress-alt';
        } else {
            expandBtn.title = chatbotLang.expandTitle;
            expandBtn.querySelector('i').className = 'fal fa-expand-alt';
        }
        scrollToBottom();
    }

    function scrollToBottom() {
        const body = document.getElementById('chatbot-messages');
        body.scrollTo({ top: body.scrollHeight, behavior: 'smooth' });
        document.getElementById('chatbot-scroll-btn').classList.remove('visible');
    }

    // Show/hide scroll-to-bottom button on scroll
    (function initScrollBtn() {
        const body = document.getElementById('chatbot-messages');
        const btn  = document.getElementById('chatbot-scroll-btn');
        if (!body || !btn) return;
        body.addEventListener('scroll', () => {
            const distFromBottom = body.scrollHeight - body.scrollTop - body.clientHeight;
            if (distFromBottom > 80) {
                btn.classList.add('visible');
            } else {
                btn.classList.remove('visible');
            }
        });
    })();

    function renderMarkdownBasic(text) {
        if (!text) return '';
        
        let lines = text.split('\n');
        let html = '';
        let inTable = false;
        let tableRows = [];

        function flushTable() {
            if (tableRows.length > 0) {
                html += renderTable(tableRows);
                tableRows = [];
            }
            inTable = false;
        }

        for (let i = 0; i < lines.length; i++) {
            let line = lines[i].trim();

            // Table detection
            if (line.startsWith('|') && line.includes('|')) {
                inTable = true;
                tableRows.push(line);
                continue;
            } else if (inTable) {
                flushTable();
            }

            // Headers
            if (line.startsWith('### ')) {
                html += `<h3>${processInline(line.substring(4))}</h3>`;
            } else if (line.startsWith('## ')) {
                html += `<h2>${processInline(line.substring(3))}</h2>`;
            } else if (line.startsWith('# ')) {
                html += `<h1>${processInline(line.substring(2))}</h1>`;
            } 
            // Lists
            else if (line.startsWith('- ') || line.startsWith('* ')) {
                html += `<ul><li>${processInline(line.substring(2))}</li></ul>`;
            }
            else if (/^\d+\.\s/.test(line)) {
                html += `<ol><li>${processInline(line.replace(/^\d+\.\s/, ''))}</li></ol>`;
            }
            // Paragraphs
            else {
                if (line) html += `<p>${processInline(line)}</p>`;
            }
        }
        flushTable();

        // Cleanup multiple ul/ol tags
        html = html.replace(/<\/ul><ul>/g, '');
        html = html.replace(/<\/ol><ol>/g, '');

        return html;
    }

    function processInline(text) {
        if (!text) return '';
        let html = text;
        // Links
        html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" style="color: var(--cb-primary); text-decoration: underline;">$1</a>');
        // Raw URLs
        html = html.replace(/(^|[^"'])(https?:\/\/[^\s<)\]"']+)/g, '$1<a href="$2" target="_blank" style="color: var(--cb-primary); text-decoration: underline;">$2</a>');
        // Bold
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        return html;
    }

    function renderTable(rows) {
        if (rows.length < 2) return rows.join('<br>');
        
        let html = '<div class="table-responsive"><table class="chatbot-table">';
        let startIdx = 0;

        // Check for separator row |---|
        let isSeparator = rows[1] && rows[1].includes('-') && rows[1].includes('|');
        
        // Header
        let headerCells = rows[0].split('|').filter((c, idx, arr) => (idx > 0 && idx < arr.length - 1) || c.trim() !== '').map(c => c.trim()).filter(c => c !== '');
        if (headerCells.length === 0) headerCells = rows[0].split('|').map(c => c.trim()).filter(c => c !== '');

        html += '<thead><tr>';
        headerCells.forEach(c => html += `<th>${processInline(c)}</th>`);
        html += '</tr></thead>';
        
        startIdx = isSeparator ? 2 : 1;
        
        // Body
        html += '<tbody>';
        for (let i = startIdx; i < rows.length; i++) {
            let bodyCells = rows[i].split('|').filter((c, idx, arr) => (idx > 0 && idx < arr.length - 1) || c.trim() !== '').map(c => c.trim()).filter(c => c !== '');
            if (bodyCells.length === 0) bodyCells = rows[i].split('|').map(c => c.trim()).filter(c => c !== '');
            
            html += '<tr>';
            bodyCells.forEach(c => html += `<td>${processInline(c)}</td>`);
            html += '</tr>';
        }
        html += '</tbody></table></div>';
        return html;
    }

    function showFeedbackModal(messageId, rating) {
        document.getElementById('feedback-message-id').value = messageId;
        document.getElementById('feedback-rating').value = rating;
        document.getElementById('chatbot-feedback-modal').classList.add('active');
    }

    function hideFeedbackModal() {
        document.getElementById('chatbot-feedback-modal').classList.remove('active');
    }

    async function submitFeedbackForm() {
        const sid = getChatSessionId();
        const mid = document.getElementById('feedback-message-id').value;
        const rating = document.getElementById('feedback-rating').value;
        const type = document.getElementById('feedback-type').value;
        const comment = document.getElementById('feedback-comment').value;

        try {
            await fetch('/chat/feedback', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    session_id: sid,
                    message_id: mid,
                    rating: parseInt(rating),
                    feedback_type: type,
                    comment: comment
                })
            });
            Swal.fire({
                icon: 'success',
                title: chatbotLang.thankYou,
                text: chatbotLang.feedbackSent,
                timer: 2000,
                showConfirmButton: false
            });
            hideFeedbackModal();
        } catch (e) {
            Swal.fire(chatbotLang.errorLabel, chatbotLang.feedbackError, 'error');
        }
    }

    function appendMessage(sender, content, metadata = null, messageId = null) {
        const container = document.getElementById('chatbot-messages');
        const msgDiv = document.createElement('div');
        msgId = messageId || 'msg-' + Math.random().toString(36).substring(2, 7);
        msgDiv.className = `chatbot-message ${sender}-message`;
        msgDiv.setAttribute('data-id', msgId);

        let innerHTML = '';
        if (sender === 'user') {
            innerHTML = `<div class="message-content">${content}</div>`;
        } else {
            let metadataHtml = '';
            let relatedItemsHtml = '';

            if (metadata) {
                if (metadata.stage) {
                    const stageNames = {
                        'greeting': 'Greeting', 'collect_criteria': 'Collecting Criteria',
                        'show_results': 'Showing Results', 'project_detail': 'Project Detail',
                        'deep_dive': 'Deep Dive', 'compare_projects': 'Comparing',
                        'policy_procedure': 'Policy & Procedure', 'cta': 'Call to Action', 'fallback': 'Fallback'
                    };
                    const stageText = chatbotLang.stageLabel + (stageNames[metadata.stage] || metadata.stage);
                    const stageInd = document.getElementById('chatbot-stage-indicator');
                    stageInd.innerText = stageText;
                    stageInd.style.display = 'block';
                }

                if (metadata.entities) {
                    let entityHtml = '<div class="chatbot-metadata">';
                    const e = metadata.entities;
                    (e.industries || []).forEach(v => entityHtml += `<div class="chatbot-entity-tag"><i class="fas fa-industry"></i> ${v}</div>`);
                    (e.districts || []).forEach(v => entityHtml += `<div class="chatbot-entity-tag"><i class="fas fa-map-marker-alt"></i> ${v}</div>`);
                    (e.project_types || []).forEach(v => entityHtml += `<div class="chatbot-entity-tag"><i class="fas fa-building"></i> ${v}</div>`);
                    entityHtml += '</div>';
                    if (entityHtml !== '<div class="chatbot-metadata"></div>') metadataHtml = entityHtml;
                }

                if (metadata.related_items && metadata.related_items.length > 0) {
                    relatedItemsHtml = '<div class="chatbot-related-items">';
                    metadata.related_items.forEach(item => {
                        let link = item.url || (item.type === 'project' ? `/project-detail/${item.id}` : '#');
                        relatedItemsHtml += `
                            <a href="${link}" target="_blank" class="related-item-card">
                                <div class="related-item-icon">
                                    <i class="${item.type === 'project' ? 'fas fa-city' : 'fas fa-file-alt'}"></i>
                                </div>
                                <div class="related-item-info">
                                    <div class="related-item-title">${item.name || item.title || chatbotLang.defaultItemName}</div>
                                    <div class="text-muted" style="font-size: 11px;">${item.type === 'project' ? chatbotLang.viewProject : chatbotLang.viewDocument}</div>
                                </div>
                            </a>
                        `;
                    });
                    relatedItemsHtml += '</div>';
                }
            }

            innerHTML = `
                <div class="message-content">
                    ${renderMarkdownBasic(content)}
                    ${metadataHtml}
                </div>
                ${relatedItemsHtml}
                <div class="chatbot-message-feedback">
                    <button class="feedback-btn" onclick="showFeedbackModal('${msgId}', 5)" title="${chatbotLang.feedbackUseful}"><i class="far fa-thumbs-up"></i></button>
                    <button class="feedback-btn" onclick="showFeedbackModal('${msgId}', 1)" title="${chatbotLang.feedbackNotUseful}"><i class="far fa-thumbs-down"></i></button>
                </div>
            `;
        }

        msgDiv.innerHTML = innerHTML;
        container.appendChild(msgDiv);
        scrollToBottom();
    }

    function appendTypingIndicator() {
        const container = document.getElementById('chatbot-messages');
        const div = document.createElement('div');
        div.className = 'chatbot-message bot-message typing-indicator-wrapper';
        div.id = 'chatbot-typing-indicator';
        div.innerHTML = `<div class="typing-indicator"><div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div></div>`;
        container.appendChild(div); scrollToBottom();
    }

    function removeTypingIndicator() {
        const el = document.getElementById('chatbot-typing-indicator');
        if (el) el.remove();
    }

    function renderSuggestedActions(actions) {
        const container = document.getElementById('chatbot-suggested-actions');
        if (!actions || actions.length === 0) { 
            container.style.display = 'none'; 
            document.getElementById('chatbot-scroll-btn').style.bottom = '80px';
            return; 
        }
        container.innerHTML = '';
        actions.forEach(a => {
            const btn = document.createElement('button');
            btn.className = 'suggested-action-btn'; btn.innerText = a.label;
            btn.onclick = () => { document.getElementById('chatbot-input').value = a.label; document.getElementById('chatbot-form').dispatchEvent(new Event('submit')); };
            container.appendChild(btn);
        });
        container.style.display = 'flex';
        document.getElementById('chatbot-scroll-btn').style.bottom = '140px';
    }

    async function loadChatHistory() {
        const sid = getChatSessionId();
        try {
            const res = await fetch(`/chat/session/${sid}`);
            if (res.ok) {
                const data = await res.json();
                if (data && data.conversation && data.conversation.messages.length > 0) {
                    document.getElementById('chatbot-messages').innerHTML = '';
                    data.conversation.messages.forEach(m => appendMessage(m.role === 'assistant' ? 'bot' : 'user', m.content, m.metadata, m.id));
                }
            }
        } catch (e) { console.error('History error:', e); }
    }

    async function fetchHealthAndModels() {
        // Health Status
        try {
            const hRes = await fetch('/chat/health');
            if (hRes.ok) {
                const hData = await hRes.json();
                const dot = document.getElementById('chatbot-status-dot');
                const statusTxt = document.getElementById('chatbot-status-text');
                if (hData.status === 'healthy') {
                    dot.style.backgroundColor = '#22c55e'; // Green
                    dot.title = chatbotLang.statusHealthy;
                    if (statusTxt) statusTxt.innerText = chatbotLang.onlineLabel || 'Đang trực tuyến';
                } else {
                    dot.style.backgroundColor = '#ef4444'; // Red
                    dot.title = chatbotLang.statusError;
                    if (statusTxt) statusTxt.innerText = chatbotLang.statusError;
                }
            }
        } catch (e) { console.error('Health check failed', e); }
    }

    async function sendChatMessage(e, retryMsg = null, errorNodeToRemove = null) {
        if(e) e.preventDefault();
        
        if (errorNodeToRemove) {
            errorNodeToRemove.remove();
        }
        
        const input = document.getElementById('chatbot-input');
        const btn = document.getElementById('chatbot-send-btn');
        
        const msg = retryMsg !== null ? retryMsg : input.value.trim();
        if (!msg) return;

        if (retryMsg === null) {
            appendMessage('user', msg);
        }
        
        input.value = ''; btn.disabled = true;
        document.getElementById('chatbot-suggested-actions').style.display = 'none';
        document.getElementById('chatbot-scroll-btn').style.bottom = '80px';
        appendTypingIndicator();

        try {
            const res = await fetch('/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                body: JSON.stringify({ 
                    session_id: getChatSessionId(), 
                    message: msg
                })
            });
            removeTypingIndicator(); btn.disabled = false;
            if (res.ok) {
                const data = await res.json();
                appendMessage('bot', data.response, { stage: data.stage, entities: data.entities, related_items: data.related_items }, data.message_id);
                if (data.suggested_actions) renderSuggestedActions(data.suggested_actions);
            } else {
                appendErrorMessage(msg, chatbotLang.errorConnection);
            }
        } catch (e) { removeTypingIndicator(); btn.disabled = false; appendErrorMessage(msg, chatbotLang.errorNetwork); }
    }

    function appendErrorMessage(originalMsg, errorText) {
        const container = document.getElementById('chatbot-messages');
        const msgDiv = document.createElement('div');
        msgDiv.className = `chatbot-message bot-message error-message-box`;
        
        const escapedMsg = originalMsg.replace(/'/g, "\\'").replace(/"/g, '&quot;');
        
        msgDiv.innerHTML = `
            <div class="message-content" style="background-color: #fef2f2; color: #ef4444; border-color: #fecaca;">
                <div><i class="fas fa-exclamation-triangle"></i> ${errorText}</div>
                <button class="suggested-action-btn retry-action-btn mt-2" onclick="sendChatMessage(event, '${escapedMsg}', this.closest('.chatbot-message'))">
                    <i class="fas fa-redo"></i> ${chatbotLang.retryBtn || 'Thử lại'}
                </button>
            </div>
        `;
        container.appendChild(msgDiv);
        scrollToBottom();
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadChatHistory();
        fetchHealthAndModels();
        
        // Init speech logic
        initSpeechRecognition();

        // Drag to scroll for suggested actions
        const suggestSlider = document.getElementById('chatbot-suggested-actions');
        let suggestIsDown = false;
        let suggestStartX;
        let suggestScrollLeft;

        suggestSlider.addEventListener('mousedown', (e) => {
            suggestIsDown = true;
            suggestStartX = e.pageX - suggestSlider.offsetLeft;
            suggestScrollLeft = suggestSlider.scrollLeft;
        });

        suggestSlider.addEventListener('mouseleave', () => {
            suggestIsDown = false;
            suggestSlider.classList.remove('active-drag');
        });

        suggestSlider.addEventListener('mouseup', () => {
            suggestIsDown = false;
            suggestSlider.classList.remove('active-drag');
        });

        suggestSlider.addEventListener('mousemove', (e) => {
            if (!suggestIsDown) return;
            e.preventDefault();
            const x = e.pageX - suggestSlider.offsetLeft;
            const walk = (x - suggestStartX) * 1.5;
            if (Math.abs(walk) > 5) {
                suggestSlider.classList.add('active-drag');
            }
            suggestSlider.scrollLeft = suggestScrollLeft - walk;
        });
    });

    // === Speech Recognition Logic ===
    let speechRecognition = null;
    let isMicRecording = false;

    function initSpeechRecognition() {
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            const micBtn = document.getElementById('chatbot-mic-btn');
            if (micBtn) micBtn.style.display = 'none';
            return false;
        }

        const WebSpeechAPI = window.SpeechRecognition || window.webkitSpeechRecognition;
        speechRecognition = new WebSpeechAPI();
        speechRecognition.continuous = false;
        speechRecognition.interimResults = true;
        speechRecognition.lang = 'vi-VN';

        let finalTranscriptStr = '';

        speechRecognition.onstart = function() {
            isMicRecording = true;
            finalTranscriptStr = ''; // reset on start
            document.getElementById('chatbot-mic-btn').classList.add('recording');
            const input = document.getElementById('chatbot-input');
            input.value = ''; // clear input to capture voice cleanly
            input.placeholder = chatbotLang.listening || "Đang nghe...";
        };

        speechRecognition.onresult = function(event) {
            let interimTranscript = '';
            
            for (let i = event.resultIndex; i < event.results.length; ++i) {
                if (event.results[i].isFinal) {
                    finalTranscriptStr += event.results[i][0].transcript;
                } else {
                    interimTranscript += event.results[i][0].transcript;
                }
            }
            
            const input = document.getElementById('chatbot-input');
            if (finalTranscriptStr || interimTranscript) {
                input.value = finalTranscriptStr + interimTranscript;
            }
        };

        speechRecognition.onerror = function(event) {
            console.error("Speech recognition error", event.error);
            stopRecordingState();
            
            if (event.error !== 'no-speech') {
                let errorMsg = 'Chưa thể lấy tín hiệu giọng nói.';
                if (event.error === 'not-allowed') {
                    errorMsg = 'Bạn chưa cấp quyền Micro (Hoặc đang chạy http thường không bảo mật).';
                } else if (event.error === 'network') {
                    errorMsg = 'Lỗi kết nối mạng máy chủ nhận diện.';
                } else if (event.error === 'aborted') {
                    return; // Ignore manual abort
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi ghi âm (' + event.error + ')',
                    text: errorMsg,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000
                });
            }
        };

        speechRecognition.onend = function() {
            stopRecordingState();
        };

        return true;
    }

    function stopRecordingState() {
        isMicRecording = false;
        const micBtn = document.getElementById('chatbot-mic-btn');
        if (micBtn) micBtn.classList.remove('recording');
        const input = document.getElementById('chatbot-input');
        if (input) input.placeholder = chatbotLang.inputPlaceholder || "Nhập tin nhắn...";
    }

    function toggleSpeechRecognition() {
        if (!speechRecognition) {
            const initialized = initSpeechRecognition();
            if (!initialized) {
                Swal.fire({
                    icon: 'info',
                    title: 'Chưa hỗ trợ',
                    text: 'Trình duyệt của bạn không hỗ trợ nhận diện giọng nói.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            }
        }
        
        if (isMicRecording) {
            speechRecognition.stop();
        } else {
            try {
                speechRecognition.start();
            } catch (e) {
                console.error(e);
            }
        }
    }
</script>
