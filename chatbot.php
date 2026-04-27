<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Power2Connect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ---------- KEYFRAMES (unchanged) ---------- */
        @keyframes chatOpen {
            from { opacity: 0; transform: translateY(-45%) scale(0.92); }
            to { opacity: 1; transform: translateY(-50%) scale(1); }
        }
        @keyframes msgPop {
            from { opacity: 0; transform: scale(0.85) translateY(8px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        @keyframes typingBounce {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
            30% { transform: translateY(-6px); opacity: 1; }
        }
        @keyframes pingAnim {
            0% { box-shadow: 0 0 0 0 rgba(239,68,68,0.6); }
            70% { box-shadow: 0 0 0 10px rgba(239,68,68,0); }
            100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); }
        }
        @keyframes fadeUpChat {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideUpChat {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ---------- MESSAGE BUBBLES & DYNAMIC ELEMENTS ---------- */
        .msg {
            max-width: 78%;
            padding: 14px 18px;
            border-radius: 20px;
            font-size: 0.9rem;
            line-height: 1.5;
            word-wrap: break-word;
            animation: msgPop 0.25s cubic-bezier(0.34,1.56,0.64,1) both;
        }
        .left {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.08);
            color: #fff;
            align-self: flex-start;
            border-bottom-left-radius: 6px;
            backdrop-filter: blur(5px);
        }
        .right {
            background: linear-gradient(135deg, #4a90e2, #7b68ee);
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 6px;
            box-shadow: 0 4px 15px rgba(74,144,226,0.3);
        }
        .msg small {
            display: block;
            font-size: 0.68rem;
            opacity: 0.6;
            margin-top: 5px;
        }
        .msg.staff {
            background: rgba(251,191,36,0.12);
            color: #fcd34d;
            border: 1px solid rgba(251,191,36,0.2);
            border-left: 4px solid #fbbf24;
            align-self: flex-start;
            border-radius: 16px 16px 16px 4px;
            max-width: 80%;
        }
        .msg.admin {
            background: rgba(239,68,68,0.12);
            color: #fca5a5;
            border: 1px solid rgba(239,68,68,0.2);
            border-left: 4px solid #ef4444;
            align-self: flex-start;
            border-radius: 16px 16px 16px 4px;
            max-width: 80%;
        }
        .staff-badge, .admin-badge {
            display: inline-block;
            font-size: 0.66rem;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 6px;
        }
        .staff-badge { background: rgba(251,191,36,0.25); color: #fbbf24; }
        .admin-badge { background: rgba(239,68,68,0.25); color: #ef4444; }
        .staff-joined {
            text-align: center;
            padding: 10px;
            background: rgba(34,197,94,0.1);
            color: #86efac;
            border-radius: 30px;
            font-size: 0.82rem;
            border: 1px solid rgba(34,197,94,0.2);
        }
        .options-grid {
            display: grid;
            grid-template-columns: repeat(2,1fr);
            gap: 10px;
            margin: 8px 0;
        }
        .option-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 16px 12px;
            border-radius: 16px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            cursor: pointer;
            transition: all 0.25s ease;
            min-height: 80px;
            font-family: 'Montserrat', sans-serif;
            color: #ffffff;
        }
        .option-btn:hover {
            background: rgba(74,144,226,0.12);
            border-color: rgba(74,144,226,0.3);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(74,144,226,0.15);
        }
        .option-icon { font-size: 1.5rem; margin-bottom: 6px; }
        .option-text { font-size: 0.8rem; font-weight: 600; color: #fff; text-align: center; }
        .forms-dropdown-container { width: 100%; margin: 8px 0; }
        .forms-toggle-btn {
            width: 100%;
            padding: 12px 18px;
            border-radius: 14px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff;
            font-size: 0.88rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .forms-toggle-btn:hover { background: rgba(255,255,255,0.1); }
        .forms-menu {
            display: none;
            background: rgba(10,14,26,0.95);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px;
            margin-top: 6px;
            padding: 8px;
            list-style: none;
            box-shadow: 0 16px 40px rgba(0,0,0,0.5);
        }
        .forms-menu.show { display: block; }
        .forms-menu li { margin: 3px 0; }
        .forms-menu a {
            display: block;
            padding: 10px 14px;
            border-radius: 10px;
            color: #c8d6f0;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }
        .forms-menu a:hover {
            background: rgba(74,144,226,0.12);
            border-left-color: #4a90e2;
            color: #fff;
            padding-left: 18px;
        }
        .category-dropdowns {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin: 8px 0;
        }
        .dropdown-container {
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.08);
        }
        .dropdown-btn {
            width: 100%;
            padding: 14px 18px;
            background: linear-gradient(135deg, rgba(74,144,226,0.2), rgba(123,104,238,0.15));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .dropdown-btn:hover {
            background: linear-gradient(135deg, rgba(74,144,226,0.3), rgba(123,104,238,0.25));
        }
        .dropdown-icon { font-size: 1.2rem; margin-right: 10px; }
        .dropdown-title { flex: 1; text-align: left; }
        .dropdown-arrow { transition: transform 0.3s ease; font-size: 0.75rem; }
        .dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            background: rgba(10,14,26,0.8);
        }
        .dropdown-content.show { max-height: 200px; }
        .dropdown-item {
            width: 100%;
            padding: 12px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            background: transparent;
            color: #c8d6f0;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.85rem;
        }
        .dropdown-item:hover {
            background: rgba(74,144,226,0.1);
            color: #fff;
            padding-left: 24px;
        }
        .item-icon { font-size: 1.1rem; min-width: 24px; text-align: center; }
        .info-form {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            padding: 20px;
            border-radius: 18px;
            animation: fadeUpChat 0.3s ease both;
        }
        .form-group { margin-bottom: 14px; }
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            font-size: 0.875rem;
            background: rgba(255,255,255,0.06);
            color: #fff;
            transition: all 0.25s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: rgba(74,144,226,0.5);
            background: rgba(74,144,226,0.08);
            box-shadow: 0 0 0 3px rgba(74,144,226,0.12);
        }
        .submit-btn {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #4a90e2, #7b68ee);
            color: white;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 6px 20px rgba(74,144,226,0.25);
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(74,144,226,0.35);
        }
        .typing-indicator {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 12px 16px;
            border-radius: 18px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.06);
            width: fit-content;
        }
        .typing-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgba(74,144,226,0.7);
            animation: typingBounce 1.2s ease-in-out infinite;
        }
        .typing-dot:nth-child(2) { animation-delay: 0.15s; }
        .typing-dot:nth-child(3) { animation-delay: 0.3s; }
        .btn-orange, .btn-blue, .btn-green, .btn-secondary {
            padding: 10px 18px;
            margin: 5px;
            border-radius: 10px;
            font-weight: 600;
            transition: all .2s;
            display: inline-block;
            cursor: pointer;
        }
        .btn-orange { background: linear-gradient(135deg, #f97316, #ea580c); color: white; }
        .btn-blue { background: linear-gradient(135deg, #4a90e2, #2563eb); color: white; }
        .btn-green { background: linear-gradient(135deg, #22c55e, #16a34a); color: white; }
        .btn-secondary { background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.12); }
        .btn-orange:hover, .btn-blue:hover, .btn-green:hover, .btn-secondary:hover { transform: translateY(-2px); opacity: 0.9; }

        @media (max-width: 768px) {
            .chat-glass {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                width: 100% !important;
                height: 100dvh !important;
                transform: none !important;
                border-radius: 0 !important;
                border: none !important;
                display: flex;
                flex-direction: column;
            }
            .chat-header { padding: 14px 16px; }
            .chat-body { flex: 1; overflow-y: auto; padding: 12px; }
            .chat-input { padding: 10px 12px calc(10px + env(safe-area-inset-bottom)); }
            .chat-mini, .chat-bubble { display: flex; }
        }
        @media (max-width: 480px) {
            .options { flex-direction: column; }
            .chat-bubble span { display: none; }
            .chat-bubble {
                width: 58px;
                height: 58px;
                border-radius: 50%;
                padding: 0;
                justify-content: center;
                font-size: 1.2rem;
            }
            .bubble-ping { top: -4px; right: -4px; }
            .chat-body { padding: 12px; }
            .chat-input input { font-size: 0.8rem; padding: 10px 14px; }
        }
        @media (max-width: 360px) {
            .msg { font-size: 0.8rem; }
            .option-text { font-size: 0.75rem; }
        }
        .talk-human-btn {
            background: linear-gradient(135deg, #f97316, #ea580c);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 18px;
            border-radius: 40px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin: 10px auto;
            width: fit-content;
            display: inline-block;
            box-shadow: 0 4px 12px rgba(249,115,22,0.3);
        }
        .talk-human-btn:hover {
            transform: scale(1.02);
            background: linear-gradient(135deg, #fb923c, #ea580c);
        }

        /* Ensure active images are fully visible */
        .bg-slider img.active {
            opacity: 1 !important;
        }
    </style>
</head>
<body style="margin:0; font-family: 'Montserrat', sans-serif; background: #0a0e1a; color: white; overflow-x: hidden;">

    <!-- BACKGROUND CAROUSEL - WORKING CROSSFADE -->
    <div class="bg-slider fixed inset-0 z-0 pointer-events-none">
        <img src="assets/carousel1.png" class="bg active absolute top-0 left-0 w-full h-full object-cover opacity-0 transition-opacity duration-[1.5s] ease-in-out">
        <img src="assets/carousel2.png" class="bg absolute top-0 left-0 w-full h-full object-cover opacity-0 transition-opacity duration-[1.5s] ease-in-out">
        <img src="assets/carousel3.png" class="bg absolute top-0 left-0 w-full h-full object-cover opacity-0 transition-opacity duration-[1.5s] ease-in-out">
        <img src="assets/carousel4.png" class="bg absolute top-0 left-0 w-full h-full object-cover opacity-0 transition-opacity duration-[1.5s] ease-in-out">
        <!-- <img src="assets/carousel5.png" class="bg absolute top-0 left-0 w-full h-full object-cover opacity-0 transition-opacity duration-[1.5s] ease-in-out"> -->
        <!-- <img src="assets/carousel6.png" class="bg absolute top-0 left-0 w-full h-full object-cover opacity-0 transition-opacity duration-[1.5s] ease-in-out"> -->
        <img src="assets/carousel7.png" class="bg absolute top-0 left-0 w-full h-full object-cover opacity-0 transition-opacity duration-[1.5s] ease-in-out">
        <img src="assets/carousel8.png" class="bg absolute top-0 left-0 w-full h-full object-cover opacity-0 transition-opacity duration-[1.5s] ease-in-out">
        <img src="assets/carousel9.png" class="bg absolute top-0 left-0 w-full h-full object-cover opacity-0 transition-opacity duration-[1.5s] ease-in-out">
        <img src="assets/carousel10.png" class="bg absolute top-0 left-0 w-full h-full object-cover opacity-0 transition-opacity duration-[1.5s] ease-in-out">
        <img src="assets/carousel11.png" class="bg absolute top-0 left-0 w-full h-full object-cover opacity-0 transition-opacity duration-[1.5s] ease-in-out">
        <img src="assets/carousel12.png" class="bg absolute top-0 left-0 w-full h-full object-cover opacity-0 transition-opacity duration-[1.5s] ease-in-out">
    </div>

    <script>
        // Carousel rotation script
        (function() {
            const images = document.querySelectorAll('.bg-slider img');
            if (!images.length) return;
            let current = 0;
            // First image is already active (from HTML)
            setInterval(() => {
                images[current].classList.remove('active');
                current = (current + 1) % images.length;
                images[current].classList.add('active');
            }, 5000);
        })();
    </script>
    <!-- LOGO -->
    <header class="logo fixed top-10 left-10 z-[100] text-white text-[2.2rem] font-medium leading-tight tracking-wide drop-shadow-lg max-md:top-5 max-md:left-5 max-md:text-2xl" style="text-shadow: 0 2px 20px rgba(0,0,0,0.5);">
        <strong class="font-bold">POWER</strong><span class="inline-block mx-0.5"><img src="assets/logo1.png" class="logo-img w-[50px] h-auto align-middle -mt-1 max-md:w-[35px]"></span><br>CONNECT
    </header>

    <!-- CHATBOX -->
    <div id="chatBox" class="chat-glass fixed right-7 top-1/2 -translate-y-1/2 w-[min(420px,94vw)] h-[min(600px,85vh)] bg-[rgba(10,14,26,0.92)] backdrop-blur-2xl rounded-3xl border border-white/10 shadow-2xl hidden flex-col z-[1000] overflow-hidden transition-all duration-300" style="box-shadow: 0 32px 80px rgba(0,0,0,0.6), inset 0 1px 0 rgba(255,255,255,0.08); animation: chatOpen 0.35s cubic-bezier(0.34,1.56,0.64,1) both;">
        
        <div class="chat-header flex items-center justify-end gap-2 px-5 py-4 bg-black/20 border-b border-white/5 flex-shrink-0">
            <button class="win-btn minimize-btn w-9 h-9 rounded-xl flex items-center justify-center bg-white/10 text-white/80 hover:bg-white/15 hover:text-white transition-all duration-200" onclick="minimizeChat()">
                <img src="assets/Minimize.png" class="w-4 h-4 invert brightness-0" style="margin-top: -10px;" alt="minimize">
            </button>
            <button class="win-btn close w-9 h-9 rounded-xl flex items-center justify-center bg-white/10 text-white/80 hover:bg-white/15 hover:text-white transition-all duration-200 close:hover:bg-red-500/25" onclick="closeChat()">
                <img src="assets/Exit.png" class="w-4 h-4 invert brightness-0" alt="close">
            </button>
        </div>

        <div id="chatBody" class="chat-body flex-1 p-5 overflow-y-auto flex flex-col gap-3 scrollbar-thin" style="scrollbar-width: thin; scrollbar-color: rgba(74,144,226,0.35) transparent;">
            <div id="languageOptions" class="options flex gap-3 justify-center my-5 animate-[fadeUpChat_0.4s_ease_both]">
                <button onclick="setLanguage('en')" class="flex-1 py-4 px-5 rounded-xl bg-white/10 border border-white/15 text-white font-semibold text-base backdrop-blur-sm hover:bg-gradient-to-br hover:from-[rgba(74,144,226,0.2)] hover:to-[rgba(123,104,238,0.2)] hover:border-[rgba(74,144,226,0.4)] hover:-translate-y-1 hover:shadow-lg transition-all duration-200">🇺🇸 English</button>
                <button onclick="setLanguage('tl')" class="flex-1 py-4 px-5 rounded-xl bg-white/10 border border-white/15 text-white font-semibold text-base backdrop-blur-sm hover:bg-gradient-to-br hover:from-[rgba(74,144,226,0.2)] hover:to-[rgba(123,104,238,0.2)] hover:border-[rgba(74,144,226,0.4)] hover:-translate-y-1 hover:shadow-lg transition-all duration-200">🇵🇭 Tagalog</button>
            </div>
        </div>

        <div class="chat-input p-4 flex gap-2.5 items-center border-t border-white/5 bg-black/20 rounded-b-3xl flex-shrink-0">
            <input id="userInput" type="text" placeholder="Type your message..." disabled class="flex-1 py-3 px-5 rounded-full border border-white/10 bg-white/5 text-white text-sm placeholder:text-white/40 focus:outline-none focus:border-[rgba(74,144,226,0.4)] focus:bg-[rgba(74,144,226,0.08)] transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed">
            <button id="sendBtn" onclick="sendText()" disabled class="send-btn w-11 h-11 rounded-full bg-gradient-to-br from-[#4a90e2] to-[#7b68ee] text-white text-xl flex items-center justify-center shadow-[0_4px_15px_rgba(74,144,226,0.3)] hover:scale-110 hover:shadow-[0_8px_24px_rgba(74,144,226,0.4)] transition-all duration-200 disabled:opacity-35 disabled:cursor-not-allowed">➤</button>
        </div>
    </div>

    <!-- MINIMIZE BAR -->
    <div id="chatMini" onclick="restoreChat()" class="chat-mini fixed bottom-6 right-7 z-[1000] hidden items-center gap-2.5 py-3.5 px-6 rounded-3xl bg-[rgba(10,14,26,0.92)] border border-white/10 backdrop-blur-xl text-white text-sm font-semibold cursor-pointer shadow-lg hover:scale-105 hover:bg-[rgba(20,26,46,0.95)] transition-all duration-200 animate-[slideUpChat_0.3s_ease_both]">
        <i class="fas fa-comment-dots text-[#4a90e2]"></i><span>💬 Click2Connect</span>
    </div>

    <!-- CHAT BUBBLE -->
    <div id="chatBubble" onclick="openChat()" class="chat-bubble fixed bottom-6 right-7 z-[1000] flex items-center gap-2.5 py-4 px-7 rounded-full bg-gradient-to-br from-[#4a90e2] to-[#7b68ee] text-white font-semibold text-sm cursor-pointer shadow-[0_8px_30px_rgba(74,144,226,0.4)] hover:scale-105 hover:-translate-y-1 hover:shadow-[0_16px_40px_rgba(74,144,226,0.5)] transition-all duration-300">
        <i class="fas fa-bolt"></i> <span>Power2Connect</span>
        <div class="bubble-ping absolute -top-1.5 -right-1.5 w-[18px] h-[18px] rounded-full bg-red-500 border-2 border-[rgba(10,14,26,0.5)] animate-[pingAnim_2s_ease_infinite]"></div>
    </div>

    <!-- PATCHED EXTERNAL SCRIPT (no "undefined" anywhere) -->
    <script>
        
        // =====================================================
        // Power2Connect_UI_Function.js (FULLY PATCHED)
        // - Removed third button from category options
        // - Added missing humanAgent translation for English
        // - Includes safety cleanup for any "undefined" text
        // =====================================================
        
        const chatBox = document.getElementById("chatBox");
        const chatBody = document.getElementById("chatBody");
        const input = document.getElementById("userInput");
        const sendBtn = document.getElementById("sendBtn");
        const chatMini = document.getElementById("chatMini");
        const chatBubble = document.getElementById("chatBubble");
        const languageOptions = document.getElementById("languageOptions");
        
        let currentLang = null;
        let conversationState = "language_selection";
        let conversationHistory = [];
        let useLocalAI = false;
        let chatSessionId = generateSessionId();
        let currentUser = null;
        let staffRequested = false;
        let userPhone = null;
        let userName = null;
        let staffJoined = false;
        let currentStaffName = null;
        let staffMessageInterval = null;
        let lastMessageCheck = new Date().toISOString();
        let displayedMessageIds = new Set();
        let currentTicketNumber = null;
        let generatedTicketId = null;
        let helpShown = false;
        
        function generateSessionId() {
            return 'chat_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }
        
        function formatTicketNumber(id) {
            return '#' + String(id).padStart(3, '0');
        }
        
        const translations = {
            en: {
                welcomeMessage: "Welcome to Power2Connect! Please select your preferred language:",
                welcome: "Hello! Welcome to Power2Connect Customer Service, How can I help you today?",
                options: "Please choose a category below:",
                inputPlaceholder: "Type your message here...",
                askPhone: "📞 Please enter your mobile number so our staff can contact you:",
                askName: "Please enter your name (optional):",
                invalidPhone: "❌ Invalid mobile number. Please enter a valid 11-digit Philippine mobile number (e.g., 09123456789)",
                phoneSubmitted: "Thank you! Your request has been sent to our staff. They will join the chat soon.",
                thankYou: "Is there anything else I can help you with today?",
                notUnderstood: "I'm sorry, I don't have enough information to answer that. Would you like to speak with a human agent?",
                languageSelected: "English selected.",
                selectLanguage: "Please select your preferred language:",
                aiThinking: "Pitzy Assistant is thinking...",
                chooseIssue: "How can I assist you with your solar or internet concern today?",
                errorServer: "Error connecting to server. Please try again.",
                selectLanguageFirst: "Please select a language first.",
                phoneLengthError: "❌ Mobile number must be 11 digits (e.g., 09123456789)",
                phoneNumberError: "❌ Please enter numbers only",
                cantAnswer: "I'm sorry, I couldn't answer your question. Would you like to speak with a human agent?",
                continueAI: "🤖 Continue with AI",
                submitting: "Submitting your request...",
                nameOptional: "Name is optional, you can skip by clicking Continue",
                phoneSuccess: "✅ Phone number saved! Staff will join shortly.",
                staffJoined: "👨‍💼 A staff member has joined the chat. They will assist you now.",
                adminJoined: "👨‍💼 An administrator has joined the chat. They will assist you now.",
                staffTyping: "Staff is typing...",
                staffMessage: "👨‍💼 Staff: ",
                adminMessage: "👨‍💼 Admin: ",
                ticketNumber: "🎫 Check Ticket Status",
                enterTicket: "Please enter your ticket number (e.g., #015, #018, #019):",
                ticketPlaceholder: "e.g., #018",
                ticketChecking: "Checking your ticket status...",
                ticketNotFound: "❌ Ticket not found. Please check the number and try again.",
                ticketFound: "✅ Ticket found! Here's your ticket status:",
                ticketStatus: "**🎫 Ticket Information**\n\n• **Ticket #:** {ticket}\n• **Name:** {name}\n• **Status:** {status}\n• **Date Requested:** {date} at {time}\n• **Last Updated:** {updated}\n• **Department:** {dept}\n• **Assigned To:** {assigned}\n• **Concern:** {concern}\n\n**Remarks:**\n{message}",
                askTicketPhone: "📞 For security, please enter the mobile number used for this ticket:",
                invalidTicket: "❌ Invalid ticket format. Please use format # followed by numbers (e.g., #015, #018)",
                ticketVerified: "✅ Ticket verified. Here's your status:",
                retryTicket: "🔄 Try Again",
                mainMenu: "📋 Main Menu",
                ticketGenerated: "✅ Your request has been submitted successfully!\n\n**Your Ticket Number: {ticket}**\n\nPlease save this ticket number for future reference.",
                saveTicket: "📝 Please save your ticket number: {ticket}",
                ticketInfo: "You will receive updates via SMS. Use your ticket number to check status anytime.",
                ticketFromDB: "Your ticket #{id} has been created in our system.",
                solarInquiry: "☀️ Solar Inquiry",
                internetInquiry: "📡 Internet Inquiry",
                appointment: "📅 Schedule Service Visit",
                connectingToAgent: "Connecting you to a human agent. Please wait...",
                agentWillJoin: "A staff member will join the chat shortly.",
                cancelRequest: "Cancel Request",
                requestSent: "Your request has been sent. A staff member will contact you soon.",
                humanAgent: "👤 Talk to Human Agent"
            },
            tl: {
                welcomeMessage: "Maligayang pagdating sa Pitzy! Mangyaring piliin ang iyong ninanais na wika:",
                welcome: "Kumusta po kayo? Maligayang pagdating sa Pitzy Customer Service. Paano ko po kayo matutulungan ngayon?",
                inputPlaceholder: "I-type ang iyong mensahe dito...",
                askPhone: "📞 Pakilagay ang iyong mobile number para ma-contact ka ng aming staff:",
                askName: "Pakilagay ang iyong pangalan (opsyonal):",
                invalidPhone: "❌ Hindi valid na mobile number. Pakilagay ng 11-digit na Philippine mobile number (hal. 09123456789)",
                phoneSubmitted: "Salamat! Naipadala na ang iyong request sa aming staff. Sila ay sasali sa chat.",
                thankYou: "May iba pa ba akong matutulungan sa iyo ngayon?",
                notUnderstood: "Paumanhin, hindi ko masagot ang iyong tanong. Gusto mo bang makausap ang aming human agent?",
                languageSelected: "Napiling wika: Tagalog.",
                selectLanguage: "Mangyaring piliin ang iyong ninanais na wika:",
                aiThinking: "Ang Pitzy Assistant ay nag-iisip...",
                chooseIssue: "Paano ko po kayo matutulungan sa inyong solar o internet ngayon?",
                errorServer: "Error sa pagkonekta sa server. Pakisubukan muli.",
                selectLanguageFirst: "Mangyaring pumili muna ng wika.",
                phoneLengthError: "❌ Ang mobile number ay dapat 11 digits (hal. 09123456789)",
                phoneNumberError: "❌ Mga numero lamang ang ilagay",
                cantAnswer: "Paumanhin, hindi ko masagot ang iyong tanong. Gusto mo bang makausap ang aming human agent?",
                humanAgent: "👤 Kausapin ang Human Agent",
                continueAI: "🤖 Magpatuloy sa AI",
                submitting: "Ini-submit ang iyong request...",
                nameOptional: "Ang pangalan ay opsyonal, pwede mong i-skip sa pamamagitan ng pag-click ng Continue",
                phoneSuccess: "✅ Nai-save ang phone number! Sasali ang staff.",
                staffJoined: "👨‍💼 Sumali na ang staff sa chat. Sila na ang mag-aassist sa iyo.",
                adminJoined: "👨‍💼 Sumali na ang administrator sa chat. Sila na ang mag-aassist sa iyo.",
                staffTyping: "Nagta-type ang staff...",
                staffMessage: "👨‍💼 Staff: ",
                adminMessage: "👨‍💼 Admin: ",
                ticketNumber: "🎫 Tingnan ang Ticket Status",
                enterTicket: "Pakilagay ang iyong ticket number (hal., #015, #018, #019):",
                ticketPlaceholder: "hal., #018",
                ticketChecking: "Sinusuri ang ticket status...",
                ticketNotFound: "❌ Hindi nahanap ang ticket. Pakisuri ang number at subukan muli.",
                ticketFound: "✅ Nahanap ang ticket! Narito ang status:",
                ticketStatus: "**🎫 Impormasyon ng Ticket**\n\n• **Ticket #:** {ticket}\n• **Pangalan:** {name}\n• **Status:** {status}\n• **Petsa ng Request:** {date} ng {time}\n• **Huling Update:** {updated}\n• **Department:** {dept}\n• **Nakatalaga:** {assigned}\n• **Paksa:** {concern}\n\n**Mga Remarks:**\n{message}",
                askTicketPhone: "📞 Para sa seguridad, pakilagay ang mobile number na ginamit para sa ticket na ito:",
                invalidTicket: "❌ Hindi valid na ticket format. Gamitin ang format # na sinusundan ng numero (hal., #015, #018)",
                ticketVerified: "✅ Na-verify ang ticket. Narito ang status:",
                retryTicket: "🔄 Subukan Muli",
                mainMenu: "📋 Main Menu",
                talkToAgent: "👤 Kausapin ang Agent",
                ticketGenerated: "✅ Matagumpay na naipadala ang iyong request!\n\n**Ang iyong Ticket Number: {ticket}**\n\nPakitandaan ang ticket number na ito para sa susunod na reference.",
                saveTicket: "📝 Paki-save ang iyong ticket number: {ticket}",
                ticketInfo: "Makakatanggap ka ng updates sa pamamagitan ng SMS. Gamitin ang iyong ticket number para tingnan ang status kahit kailan.",
                ticketFromDB: "Ang iyong ticket #{id} ay nalikha na sa aming sistema.",
                solarInquiry: "☀️ Solar Inquiry",
                internetInquiry: "📡 Internet Inquiry",
                appointment: "📅 Mag-schedule ng Service Visit",
                connectingToAgent: "Ikinokonekta ka sa aming human agent. Mangyaring maghintay...",
                agentWillJoin: "May staff na sasali sa chat para tulungan ka.",
                cancelRequest: "Kanselahin ang Request",
                requestSent: "Naipadala na ang iyong request. May staff na tatawag sa iyo sa lalong madaling panahon."
            }
        };
        
        // Safety: remove any element containing "undefined"
        function removeUndefinedElements() {
            const all = document.querySelectorAll('*');
            all.forEach(el => {
                if (el.innerText && /undefined/i.test(el.innerText)) {
                    if (el.tagName === 'BUTTON' || el.classList.contains('option-btn')) {
                        el.remove();
                    } else if (el.children.length === 0) {
                        el.remove();
                    }
                }
            });
        }
        
        function initializeChat() {
            const messages = chatBody.querySelectorAll('.msg');
            messages.forEach(msg => msg.remove());
            const dynamicElements = chatBody.querySelectorAll('.options-grid, .info-form, #dynamicOptions, #helpOptions, #typingIndicator, #staffTypingIndicator, #phoneInputForm, #nameInputForm, #contactInfoForm, #humanAgentOptions, #categoryDropdowns, #categoryOptions, #ticketForm, #ticketPhoneForm, #ticketRetryOptions');
            dynamicElements.forEach(el => el.remove());
            helpShown = false;
            addMsg(translations.en.welcomeMessage, "left");
            showLanguageSelection();
        }
        
        function showLanguageSelection() {
            if (languageOptions) {
                languageOptions.style.display = "flex";
                languageOptions.style.visibility = "visible";
                languageOptions.style.opacity = "1";
                languageOptions.style.pointerEvents = "auto";
            }
            input.disabled = true;
            sendBtn.disabled = true;
            input.placeholder = translations.en.selectLanguage;
            conversationState = "language_selection";
        }
        
        async function setLanguage(lang) {
            currentLang = lang;
            const texts = translations[currentLang];
            addMsg(texts.languageSelected, "left");
            if (languageOptions) languageOptions.style.display = "none";
            conversationHistory = [];
            useLocalAI = false;
            helpShown = false;
            await createChatSession();
            setTimeout(() => startChatSession(), 1000);
        }
        
        async function createChatSession() {
            try {
                await fetch('chat/create_session.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ session_id: chatSessionId, language: currentLang })
                });
            } catch(e) { console.error(e); }
        }
        
        function startChatSession() {
            const texts = translations[currentLang];
            addMsg(texts.welcome, "left");
            addMsg(texts.chooseIssue, "left");
            showCategoryOptions();
            input.disabled = false;
            sendBtn.disabled = false;
            input.placeholder = texts.inputPlaceholder;
            conversationState = "waiting";
            setTimeout(() => input.focus(), 500);
        }
        
        // PATCHED: Only two buttons – no "undefined" possible
        function showCategoryOptions() {
            const texts = translations[currentLang];
            const optionsDiv = document.createElement("div");
            optionsDiv.className = "options";
            optionsDiv.id = "categoryOptions";
            optionsDiv.innerHTML = `
                <button onclick="selectCategory('solar')">${texts.solarInquiry}</button>
                <button onclick="selectCategory('internet')">${texts.internetInquiry}</button>
            `;
            chatBody.appendChild(optionsDiv);
            chatBody.scrollTop = chatBody.scrollHeight;
            removeUndefinedElements();
        }
        
        function selectCategory(category) {
            document.getElementById("categoryOptions")?.remove();
            const texts = translations[currentLang];
            addMsg(category === 'solar' ? texts.solarInquiry : texts.internetInquiry, "right");
            setTimeout(() => {
                let response = category === 'solar' 
                    ? "Please describe your solar inquiry, and I'll do my best to assist you. If you need specific technical advice, I can connect you with our solar specialists."
                    : "For internet inquiries, please describe your concern about connection, speed, or plans. Click 'Talk to Human Agent' to speak with our staff who can help with detailed information.";
                addMsg(response, "left");
                setTimeout(() => askForMoreHelp(), 2000);
            }, 500);
        }
        
        async function searchKnowledgeBase(query, language) {
            try {
                const res = await fetch('chat/knowledge_api.php?action=search&q=' + encodeURIComponent(query) + '&lang=' + language);
                const data = await res.json();
                return (data.success && data.data.length) ? data.data[0] : null;
            } catch(e) { return null; }
        }
        
        async function getAIResponse(userMessage) {
            const start = Date.now();
            let source = 'unknown', finalResponse = '';
            const knowledge = await searchKnowledgeBase(userMessage, currentLang);
            if (knowledge) {
                finalResponse = knowledge.answer;
                conversationHistory.push({ role: 'user', content: userMessage }, { role: 'assistant', content: finalResponse });
                await saveMessage('ai', finalResponse);
                await logAIUsage(userMessage, finalResponse, 'database', Date.now() - start);
                return { text: finalResponse };
            }
            if (useLocalAI) {
                finalResponse = getLocalAIResponse(userMessage).text;
                await logAIUsage(userMessage, finalResponse, 'local', Date.now() - start);
                return { text: finalResponse };
            }
            try {
                const res = await fetch('chat/gemini_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ prompt: userMessage, maxTokens: 150, temperature: 0.7, session_id: chatSessionId })
                });
                if (!res.ok) throw new Error();
                const data = await res.json();
                if (!data.success) throw new Error();
                finalResponse = data.response.trim();
                conversationHistory.push({ role: 'user', content: userMessage }, { role: 'assistant', content: finalResponse });
                await saveMessage('ai', finalResponse);
                await logAIUsage(userMessage, finalResponse, 'gemini', Date.now() - start);
                return { text: finalResponse };
            } catch(e) {
                useLocalAI = true;
                finalResponse = getLocalAIResponse(userMessage).text;
                await logAIUsage(userMessage, finalResponse, 'local', Date.now() - start);
                return { text: finalResponse };
            }
        }
        
        function getLocalAIResponse(userMessage) {
            const isTag = currentLang === 'tl';
            const lower = userMessage.toLowerCase();
            const humanKeywords = ['human agent','talk to agent','speak to agent','live agent','customer service','talk to human','makausap','kausapin','tao','staff','gusto ko makausap'];
            if (humanKeywords.some(k => lower.includes(k))) {
                setTimeout(() => requestHumanAgent(), 500);
                return { text: isTag ? "Naiintindihan ko. Gusto mo bang makausap ang aming human agent? Pakilagay ang iyong contact information sa ibaba." : "I understand. Would you like to speak with a human agent? Please provide your contact information below." };
            }
            if (lower.includes('thank') || lower.includes('salamat')) return { text: isTag ? "Walang anuman! May iba pa ba akong matutulong sa iyo?" : "You're welcome! Is there anything else I can help you with?" };
            const solar = ['solar','panel','battery','inverter','net metering'];
            const internet = ['internet','wifi','fiber','speed','connection'];
            const isSolar = solar.some(k => lower.includes(k));
            const isInternet = internet.some(k => lower.includes(k));
            let response = "";
            if (!isSolar && !isInternet) response = isTag ? "Paumanhin, ang tanong mo ay hindi related sa aming solar at internet topics. Gusto mo bang makausap ang aming human agent?" : "I'm sorry, your question is not related to our solar and internet topics. Would you like to speak with a human agent?";
            else if (lower.includes('ticket')) response = isTag ? "Para sa ticket concerns, maaari mong i-check ang iyong ticket status gamit ang 'Check Ticket' button sa ibaba." : "For ticket concerns, you can check your ticket status using the 'Check Ticket' button below.";
            else if (isSolar) response = isTag ? "Salamat sa iyong mensahe tungkol sa solar. Maaari mo bang ilahad nang mas detalyado ang iyong concern?" : "Thank you for your solar-related message. Could you please provide more details?";
            else if (isInternet) response = isTag ? "Salamat sa iyong mensahe tungkol sa internet. Maaari mo bang ilahad nang mas detalyado ang iyong concern?" : "Thank you for your internet-related message. Could you please provide more details?";
            else response = isTag ? "Salamat sa iyong mensahe. Maaari mo bang ilahad nang mas detalyado ang iyong concern?" : "Thank you for your message. Could you please provide more details?";
            conversationHistory.push({ role: 'user', content: userMessage }, { role: 'assistant', content: response });
            return { text: response };
        }
        
        async function logAIUsage(userMsg, aiResp, source, time) {
            try { await fetch('chat/log_ai_usage.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ session_id: chatSessionId, user_message: userMsg, ai_response: aiResp, source: source, response_time: time }) }); } catch(e) {}
        }
        
        function showTicketInputForm() {
            const texts = translations[currentLang];
            const formDiv = document.createElement("div");
            formDiv.className = "info-form";
            formDiv.id = "ticketForm";
            formDiv.innerHTML = `<div class="form-group"><input type="text" id="ticketNumberInput" placeholder="${texts.ticketPlaceholder}" class="form-input" required><small class="form-hint">${texts.enterTicket}</small></div><button onclick="checkTicketNumber()" class="submit-btn btn-orange">Check Ticket</button><button onclick="cancelTicketCheck()" class="submit-btn btn-secondary">Cancel</button>`;
            chatBody.appendChild(formDiv);
            chatBody.scrollTop = chatBody.scrollHeight;
        }
        
        function checkTicketNumber() {
            const ticketInput = document.getElementById("ticketNumberInput");
            if (!ticketInput) return;
            let ticketNumber = ticketInput.value.trim().toUpperCase();
            const texts = translations[currentLang];
            document.getElementById("ticketForm")?.remove();
            if (!ticketNumber.startsWith('#')) ticketNumber = '#' + ticketNumber;
            const numPart = ticketNumber.replace('#', '');
            if (!/^\d+$/.test(numPart) || numPart.length > 4) { addMsg(texts.invalidTicket, "left"); setTimeout(() => askForMoreHelp(), 2000); return; }
            addMsg(ticketNumber, "right");
            currentTicketNumber = ticketNumber;
            showTypingIndicator();
            sessionStorage.setItem('currentTicket', ticketNumber);
            setTimeout(() => { hideTypingIndicator(); addMsg(texts.askTicketPhone, "left"); showTicketPhoneInput(ticketNumber); }, 1000);
        }
        
        function showTicketPhoneInput(ticketNumber) {
            const texts = translations[currentLang];
            const formDiv = document.createElement("div");
            formDiv.className = "info-form";
            formDiv.id = "ticketPhoneForm";
            formDiv.innerHTML = `<div class="form-group"><input type="tel" id="ticketPhoneInput" placeholder="09123456789" class="form-input" required onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="11"><small class="form-hint">${texts.phoneLengthError}</small></div><button onclick="verifyTicketWithDatabase('${ticketNumber}')" class="submit-btn btn-orange">Verify & Check Status</button><button onclick="cancelTicketCheck()" class="submit-btn btn-secondary">Cancel</button>`;
            chatBody.appendChild(formDiv);
            chatBody.scrollTop = chatBody.scrollHeight;
        }
        
        async function verifyTicketWithDatabase(ticketNumber) {
            const phoneInput = document.getElementById("ticketPhoneInput");
            if (!phoneInput) return;
            const phone = phoneInput.value.trim();
            const texts = translations[currentLang];
            document.getElementById("ticketPhoneForm")?.remove();
            if (!/^\d+$/.test(phone)) { alert(texts.phoneNumberError); showTicketPhoneInput(ticketNumber); return; }
            if (phone.length !== 11) { alert(texts.phoneLengthError); showTicketPhoneInput(ticketNumber); return; }
            if (!/^09\d{9}$/.test(phone)) { alert(texts.invalidPhone); showTicketPhoneInput(ticketNumber); return; }
            addMsg(phone, "right");
            showTypingIndicator();
            try {
                const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
                const response = await fetch(baseUrl + 'chat/get_ticket_status.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ ticket_number: ticketNumber, phone: phone }) });
                const data = await response.json();
                hideTypingIndicator();
                if (data.success) {
                    const t = data.ticket;
                    addMsg(texts.ticketVerified, "left");
                    const statusMsg = texts.ticketStatus.replace('{ticket}', t.ticket).replace('{name}', t.name).replace('{status}', t.status).replace('{date}', t.date).replace('{time}', t.time).replace('{updated}', t.updated).replace('{dept}', t.dept).replace('{assigned}', t.assigned).replace('{concern}', t.concern).replace('{message}', t.message);
                    addMsg(statusMsg, "left");
                    conversationHistory.push({ role: 'system', content: `Ticket ${ticketNumber} status: ${t.status_raw}` });
                    setTimeout(() => askForMoreHelp(), 4000);
                } else {
                    addMsg(data.message, "left");
                    setTimeout(() => { const opts = document.createElement("div"); opts.className = "options"; opts.id = "ticketRetryOptions"; opts.innerHTML = `<button onclick="retryTicketCheck()" class="btn-green">🔄 ${texts.retryTicket}</button><button onclick="showCategoryOptions()" class="btn-green">📋 ${texts.mainMenu}</button><button onclick="requestHumanAgent()" class="btn-green">👤 ${texts.talkToAgent || 'Talk to Agent'}</button>`; chatBody.appendChild(opts); chatBody.scrollTop = chatBody.scrollHeight; }, 1500);
                }
            } catch(e) { hideTypingIndicator(); addMsg("❌ Error connecting to server. Please try again.", "left"); setTimeout(() => askForMoreHelp(), 3000); }
        }
        
        function retryTicketCheck() {
            document.getElementById("ticketRetryOptions")?.remove();
            const last = sessionStorage.getItem('currentTicket');
            last ? showTicketInputForm() : showCategoryOptions();
        }
        
        function cancelTicketCheck() {
            document.getElementById("ticketForm")?.remove();
            document.getElementById("ticketPhoneForm")?.remove();
            document.getElementById("ticketRetryOptions")?.remove();
            addMsg("Ticket check cancelled", "right");
            setTimeout(() => askForMoreHelp(), 1000);
        }
        
        async function saveMessage(sender_type, message) {
            try { await fetch('chat/save_message.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ session_id: chatSessionId, sender_type: sender_type, message: message }) }); } catch(e) {}
        }
        
        async function sendText() {
            if (currentLang === null) { addMsg(translations.en.selectLanguageFirst, "left"); return; }
            const text = input.value.trim();
            if (!text) return;
            input.value = "";
            addMsg(text, "right");
            await saveMessage('user', text);
            const lower = text.toLowerCase();
            const humanKeywords = ['human agent','talk to agent','speak to agent','live agent','customer service','talk to human','makausap','kausapin','tao','staff','gusto ko makausap'];
            if (humanKeywords.some(k => lower.includes(k)) && !staffJoined) {
                const texts = translations[currentLang];
                addMsg(texts.connectingToAgent || "Connecting you to a human agent. Please wait...", "left");
                setTimeout(() => requestHumanAgent(), 1000);
                return;
            }
            if (!staffJoined) await processUserInput(text);
        }
        
        async function processUserInput(text) {
            const lower = text.toLowerCase();
            const humanKeywords = ['human agent','talk to agent','speak to agent','live agent','customer service','talk to human','makausap','kausapin','tao','staff','gusto ko makausap'];
            if (humanKeywords.some(k => lower.includes(k))) {
                hideTypingIndicator();
                addMsg(translations[currentLang].connectingToAgent || "Connecting you to a human agent. Please wait...", "left");
                setTimeout(() => requestHumanAgent(), 1000);
                return;
            }
            showTypingIndicator();
            try {
                const aiResp = await getAIResponse(text);
                hideTypingIndicator();
                addMsg(aiResp.text, "left");
                const needsHuman = aiResp.text.toLowerCase().includes('human agent') || aiResp.text.toLowerCase().includes('makausap ang aming human agent');
                setTimeout(() => needsHuman ? askForHumanAgentConfirmation() : askForMoreHelp(), needsHuman ? 1500 : 2000);
            } catch(e) { hideTypingIndicator(); addMsg(translations[currentLang].notUnderstood, "left"); setTimeout(() => askForHumanAgentConfirmation(), 1500); }
        }
        
        function askForHumanAgentConfirmation() {
            const texts = translations[currentLang];
            addMsg(texts.cantAnswer, "left");
            const opts = document.createElement("div");
            opts.className = "options";
            opts.id = "humanAgentOptions";
            opts.innerHTML = `<button onclick="requestHumanAgent()" class="btn-orange">👤 ${texts.humanAgent}</button><button onclick="continueWithAI()" class="btn-blue">🤖 ${texts.continueAI}</button>`;
            chatBody.appendChild(opts);
            chatBody.scrollTop = chatBody.scrollHeight;
            removeUndefinedElements();
        }
        
        function continueWithAI() {
            document.getElementById("humanAgentOptions")?.remove();
            addMsg(translations[currentLang].continueAI, "right");
            setTimeout(() => askForMoreHelp(), 1000);
        }
        
        function requestHumanAgent() {
            document.getElementById("humanAgentOptions")?.remove();
            document.getElementById("helpOptions")?.remove();
            document.getElementById("categoryOptions")?.remove();
            addMsg(translations[currentLang].humanAgent, "right");
            setTimeout(() => askForContactInfo(), 1000);
        }
        
        function askForContactInfo() {
            const texts = translations[currentLang];
            document.getElementById("contactInfoForm")?.remove();
            addMsg(texts.askPhone, "left");
            const formDiv = document.createElement("div");
            formDiv.className = "info-form";
            formDiv.id = "contactInfoForm";
            formDiv.innerHTML = `<div class="form-group"><input type="tel" id="userPhoneInput" placeholder="09123456789" class="form-input" required onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="11"><small class="form-hint">${texts.phoneLengthError}</small></div><div class="form-group"><input type="text" id="userNameInput" placeholder="${texts.askName}" class="form-input"><small class="form-hint">${texts.nameOptional}</small></div><button onclick="submitContactInfo()" class="submit-btn btn-orange">Submit</button><button onclick="cancelContactRequest()" class="submit-btn btn-secondary">${texts.cancelRequest || 'Cancel'}</button>`;
            chatBody.appendChild(formDiv);
            chatBody.scrollTop = chatBody.scrollHeight;
        }
        
        function cancelContactRequest() {
            document.getElementById("contactInfoForm")?.remove();
            addMsg("Request cancelled. How else can I help you?", "left");
            setTimeout(() => showHelpOptions(), 1000);
        }
        
        async function submitContactInfo() {
            const phone = document.getElementById("userPhoneInput").value.trim();
            const name = document.getElementById("userNameInput").value.trim();
            const texts = translations[currentLang];
            const last = localStorage.getItem(`submitted_${phone}`);
            if (last && (Date.now() - parseInt(last)) < 6*60*60*1000) { alert("You already submitted your issue. Please try again after 6 hours."); return; }
            if (!/^\d+$/.test(phone)) { alert(texts.phoneNumberError); return; }
            if (phone.length !== 11) { alert(texts.phoneLengthError); return; }
            if (!/^09\d{9}$/.test(phone)) { alert(texts.invalidPhone); return; }
            document.getElementById("contactInfoForm")?.remove();
            if (name) addMsg(name, "right");
            addMsg(phone, "right");
            showTypingIndicator();
            try {
                await fetch('chat/save_user.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ mobile_number: phone, name: name }) });
                const cb = await fetch('chat/save_callback.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ session_id: chatSessionId, phone: phone, name: name, reason: "Customer requested human agent assistance" }) });
                const data = await cb.json();
                hideTypingIndicator();
                if (data.success) {
                    const ticketId = data.request_id;
                    const formatted = formatTicketNumber(ticketId);
                    addMsg(texts.ticketGenerated.replace('{ticket}', formatted), "left");
                    setTimeout(() => { addMsg(`📝 Your ticket #${ticketId} has been recorded. Use ${formatted} for follow-ups.`, "left"); addMsg(texts.ticketInfo, "left"); }, 2000);
                    localStorage.setItem(`submitted_${phone}`, Date.now());
                    localStorage.setItem(`ticket_${phone}`, formatted);
                    sessionStorage.setItem('lastTicket', formatted);
                    input.disabled = false;
                    sendBtn.disabled = false;
                    startStaffMessageCheck();
                    setTimeout(() => askForMoreHelp(), 4000);
                } else { addMsg("Error submitting request. Please try again.", "left"); setTimeout(() => askForMoreHelp(), 2000); }
            } catch(e) { hideTypingIndicator(); addMsg(texts.errorServer, "left"); setTimeout(() => askForMoreHelp(), 2000); }
        }
        
        function startStaffMessageCheck() {
            if (staffMessageInterval) clearInterval(staffMessageInterval);
            staffMessageInterval = setInterval(checkForStaffMessages, 3000);
        }
        
        async function checkForStaffMessages() {
            if (!chatSessionId) return;
            try {
                const res = await fetch('chat/get_staff_messages.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ session_id: chatSessionId, last_check: lastMessageCheck }) });
                const data = await res.json();
                if (data.success && data.messages && data.messages.length) {
                    hideStaffTypingIndicator();
                    if (!staffJoined) {
                        staffJoined = true;
                        const first = data.messages[0];
                        addMsg(first.sender_type === 'admin' ? (translations[currentLang].adminJoined || "Admin joined.") : (translations[currentLang].staffJoined || "Staff joined."), "left", first.sender_type === 'staff', first.sender_type === 'admin');
                    }
                    data.messages.forEach(msg => {
                        const mid = `msg_${msg.id}`;
                        if (!displayedMessageIds.has(mid)) {
                            const time = new Date(msg.created_at).toLocaleTimeString();
                            let label = '', isStaff = false, isAdmin = false;
                            if (msg.sender_type === 'admin') { label = `<strong>👨‍💼 ${msg.display_name || 'Admin'}</strong> <span class="admin-badge">Admin</span>`; isAdmin = true; }
                            else if (msg.sender_type === 'staff') { label = `<strong>👨‍💼 ${msg.display_name || 'Staff'}</strong> <span class="staff-badge">Staff</span>`; isStaff = true; }
                            addMsg(`${label}<br>${escapeHtml(msg.message)}<br><small>${time}</small>`, "left", isStaff, isAdmin);
                            displayedMessageIds.add(mid);
                        }
                    });
                    lastMessageCheck = data.last_check;
                }
            } catch(e) { console.error(e); }
        }
        
        function showStaffTypingIndicator() { /* optional */ }
        function hideStaffTypingIndicator() { const el = document.getElementById('staffTypingIndicator'); if (el) el.remove(); }
        
        function askForMoreHelp() {
            if (helpShown) return;
            setTimeout(() => { addMsg(translations[currentLang].thankYou, "left"); showHelpOptions(); helpShown = true; }, 1000);
        }
        
        function showHelpOptions() {
            const lastTicket = sessionStorage.getItem('lastTicket') || localStorage.getItem('lastTicket');
            let btns = `<button onclick="startNewTopic()" class="btn-blue">🆕 New Topic</button><button onclick="showCategoryOptions()" class="btn-green">📋 Main Menu</button>`;
            btns += lastTicket ? `<button onclick="quickCheckTicket('${lastTicket}')" class="btn-orange">🎫 Check Ticket ${lastTicket}</button>` : `<button onclick="showTicketInputForm()" class="btn-orange">🎫 Check My Ticket</button>`;
            btns += `<button onclick="requestHumanAgent()" class="btn-blue">👤 Talk to Human Agent</button>`;
            const helpDiv = document.createElement("div");
            helpDiv.className = "options";
            helpDiv.id = "helpOptions";
            helpDiv.innerHTML = btns;
            chatBody.appendChild(helpDiv);
            chatBody.scrollTop = chatBody.scrollHeight;
            removeUndefinedElements();
        }
        
        function quickCheckTicket(ticketNumber) {
            document.getElementById("helpOptions")?.remove();
            currentTicketNumber = ticketNumber;
            addMsg(`📋 Checking ticket: ${ticketNumber}`, "right");
            setTimeout(() => { addMsg(translations[currentLang].askTicketPhone, "left"); showTicketPhoneInput(ticketNumber); }, 1000);
        }
        
        function startNewTopic() {
            document.getElementById("helpOptions")?.remove();
            helpShown = false;
            addMsg("--- New Conversation Topic ---", "left");
            setTimeout(() => { addMsg(translations[currentLang].chooseIssue, "left"); showCategoryOptions(); }, 500);
        }
        
        function showTypingIndicator() {
            const texts = translations[currentLang];
            const td = document.createElement('div');
            td.className = 'typing-indicator';
            td.id = 'typingIndicator';
            td.innerHTML = `<span>${texts.aiThinking}</span><div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>`;
            chatBody.appendChild(td);
            chatBody.scrollTop = chatBody.scrollHeight;
        }
        
        function hideTypingIndicator() { document.getElementById('typingIndicator')?.remove(); }
        
        function addMsg(text, side, isStaff = false, isAdmin = false) {
            const d = document.createElement("div");
            if (isAdmin) d.className = "msg admin";
            else if (isStaff) d.className = "msg staff";
            else d.className = "msg " + side;
            d.innerHTML = text.replace(/\n/g, '<br>');
            if (languageOptions && languageOptions.parentNode === chatBody && languageOptions.style.display !== 'none') chatBody.insertBefore(d, languageOptions.nextSibling);
            else chatBody.appendChild(d);
            chatBody.scrollTop = chatBody.scrollHeight;
            removeUndefinedElements();
        }
        
        function escapeHtml(str) { if (!str) return ''; const div = document.createElement('div'); div.textContent = str; return div.innerHTML; }
        
        function minimizeChat() { chatBox.style.display = "none"; chatMini.style.display = "flex"; chatBubble.style.display = "none"; }
        function restoreChat() { chatBox.style.display = "flex"; chatMini.style.display = "none"; chatBubble.style.display = "none"; }
        function toggleMax() { chatBox.classList.toggle("maximized"); }
        function closeChat() { chatBox.style.display = "none"; chatMini.style.display = "none"; chatBubble.style.display = "flex"; if (staffMessageInterval) clearInterval(staffMessageInterval); staffJoined = false; displayedMessageIds.clear(); }
        function openChat() { chatBox.style.display = "flex"; chatBubble.style.display = "none"; if (!currentLang) initializeChat(); }
        
        window.onload = function() {
            chatBox.style.display = "none";
            chatMini.style.display = "none";
            chatBubble.style.display = "flex";
            input.addEventListener("keypress", e => { if (e.key === "Enter") { e.preventDefault(); sendText(); } });
        };
        
        // Expose global functions for HTML buttons
        window.minimizeChat = minimizeChat;
        window.restoreChat = restoreChat;
        window.closeChat = closeChat;
        window.openChat = openChat;
        window.setLanguage = setLanguage;
        window.sendText = sendText;
        window.selectCategory = selectCategory;
        window.requestHumanAgent = requestHumanAgent;
        window.continueWithAI = continueWithAI;
        window.checkTicketNumber = checkTicketNumber;
        window.cancelTicketCheck = cancelTicketCheck;
        window.verifyTicketWithDatabase = verifyTicketWithDatabase;
        window.retryTicketCheck = retryTicketCheck;
        window.showTicketInputForm = showTicketInputForm;
        window.quickCheckTicket = quickCheckTicket;
        window.startNewTopic = startNewTopic;
        window.showCategoryOptions = showCategoryOptions;
        window.submitContactInfo = submitContactInfo;
        window.cancelContactRequest = cancelContactRequest;
        window.requestHumanSupport = function() { requestHumanAgent(); };
    </script>
</body>
</html>