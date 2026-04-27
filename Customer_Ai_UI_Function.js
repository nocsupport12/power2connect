// Pitzy_UI_Function.js
// Complete working version with Tagalog human agent support

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

// Generate unique session ID
function generateSessionId() {
    return 'chat_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
}

// Format ticket number from database ID (e.g., 15 -> #015)
function formatTicketNumber(id) {
    return '#' + String(id).padStart(3, '0');
}

// Translations 
const translations = {
    en: {
        welcomeMessage: "Welcome to Pitzy! Please select your preferred language:",
        welcome: "Hello! Welcome to Pitzy Customer Service, How can I help you today?",
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
        ticketStatus: "**🎫 Ticket Information**\n\n" +
            "• **Ticket #:** {ticket}\n" +
            "• **Name:** {name}\n" +
            "• **Status:** {status}\n" +
            "• **Date Requested:** {date} at {time}\n" +
            "• **Last Updated:** {updated}\n" +
            "• **Department:** {dept}\n" +
            "• **Assigned To:** {assigned}\n" +
            "• **Concern:** {concern}\n\n" +
            "**Remarks:**\n{message}",
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
        requestSent: "Your request has been sent. A staff member will contact you soon."
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
        ticketStatus: "**🎫 Impormasyon ng Ticket**\n\n" +
            "• **Ticket #:** {ticket}\n" +
            "• **Pangalan:** {name}\n" +
            "• **Status:** {status}\n" +
            "• **Petsa ng Request:** {date} ng {time}\n" +
            "• **Huling Update:** {updated}\n" +
            "• **Department:** {dept}\n" +
            "• **Nakatalaga:** {assigned}\n" +
            "• **Paksa:** {concern}\n\n" +
            "**Mga Remarks:**\n{message}",
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

// Initialize chat
function initializeChat() {
    console.log("initializeChat() called");
    
    const messages = chatBody.querySelectorAll('.msg');
    messages.forEach(msg => msg.remove());
    
    const dynamicElements = chatBody.querySelectorAll('.options-grid, .info-form, #dynamicOptions, #helpOptions, #typingIndicator, #staffTypingIndicator, #phoneInputForm, #nameInputForm, #contactInfoForm, #humanAgentOptions, #categoryDropdowns, #categoryOptions, #ticketForm, #ticketPhoneForm, #ticketRetryOptions');
    dynamicElements.forEach(el => el.remove());
    
    helpShown = false;
    
    addMsg(translations.en.welcomeMessage, "left");
    showLanguageSelection();
}

function showLanguageSelection() {
    console.log("showLanguageSelection() called");
    
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
    console.log("setLanguage() called with:", lang);
    currentLang = lang;
    
    const texts = translations[currentLang];
    addMsg(texts.languageSelected, "left");
    
    if (languageOptions) {
        languageOptions.style.display = "none";
    }
    
    conversationHistory = [];
    useLocalAI = false;
    helpShown = false;
    
    await createChatSession();
    
    setTimeout(() => {
        startChatSession();
    }, 1000);
}

async function createChatSession() {
    try {
        console.log("Creating chat session...");
        await fetch('chat/create_session.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                session_id: chatSessionId,
                language: currentLang
            })
        });
    } catch (error) {
        console.error('Error creating chat session:', error);
    }
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
    
    setTimeout(() => {
        input.focus();
    }, 500);
}

function showCategoryOptions() {
    const texts = translations[currentLang];
    
    const optionsDiv = document.createElement("div");
    optionsDiv.className = "options";
    optionsDiv.id = "categoryOptions";
    optionsDiv.innerHTML = `
        <button onclick="selectCategory('solar')">${texts.solarInquiry}</button>
        <button onclick="selectCategory('internet')">${texts.internetInquiry}</button>
        <button onclick="requestHumanAgent()">👤 ${texts.humanAgent}</button>
    `;
    
    chatBody.appendChild(optionsDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
}

function selectCategory(category) {
    document.getElementById("categoryOptions")?.remove();
    
    const texts = translations[currentLang];
    
    if (category === 'solar') {
        addMsg(texts.solarInquiry, "right");
    } else if (category === 'internet') {
        addMsg(texts.internetInquiry, "right");
    }
    
    setTimeout(() => {
        let response = "";
        if (category === 'solar') {
            response = "Please describe your solar inquiry, and I'll do my best to assist you. If you need specific technical advice, I can connect you with our solar specialists.";
        } else {
            response = "For internet inquiries, please describe your concern about connection, speed, or plans. Click 'Talk to Human Agent' to speak with our staff who can help with detailed information.";
        }
        addMsg(response, "left");
        setTimeout(() => {
            askForMoreHelp();
        }, 2000);
    }, 500);
}

async function searchKnowledgeBase(query, language) {
    try {
        const response = await fetch('chat/knowledge_api.php?action=search&q=' + encodeURIComponent(query) + '&lang=' + language);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            return data.data[0];
        }
        return null;
    } catch (error) {
        console.error('Knowledge base error:', error);
        return null;
    }
}

async function getAIResponse(userMessage) {
    const startTime = Date.now();
    let source = 'unknown';
    let finalResponse = '';
    
    // Check knowledge base first
    const knowledge = await searchKnowledgeBase(userMessage, currentLang);
    
    if (knowledge) {
        source = 'database';
        finalResponse = knowledge.answer;
        conversationHistory.push({ role: 'user', content: userMessage });
        conversationHistory.push({ role: 'assistant', content: finalResponse });
        await saveMessage('ai', finalResponse);
        await logAIUsage(userMessage, finalResponse, source, Date.now() - startTime);
        return { text: finalResponse };
    }
    
    // Fallback to local AI if needed
    if (useLocalAI) {
        source = 'local';
        finalResponse = getLocalAIResponse(userMessage).text;
        await logAIUsage(userMessage, finalResponse, source, Date.now() - startTime);
        return { text: finalResponse };
    }
    
    try {
        const response = await fetch('chat/gemini_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                prompt: userMessage,
                maxTokens: 150,
                temperature: 0.7,
                session_id: chatSessionId
            })
        });
        
        if (!response.ok) throw new Error(`API error: ${response.status}`);
        
        const data = await response.json();
        
        if (!data.success) {
            useLocalAI = true;
            finalResponse = getLocalAIResponse(userMessage).text;
            await logAIUsage(userMessage, finalResponse, 'local', Date.now() - startTime);
            return { text: finalResponse };
        }
        
        source = 'gemini';
        finalResponse = data.response;
        
        // MINIMAL cleaning only - preserve the AI's natural response
        finalResponse = finalResponse.trim();
        
        conversationHistory.push({ role: 'user', content: userMessage });
        conversationHistory.push({ role: 'assistant', content: finalResponse });
        await saveMessage('ai', finalResponse);
        await logAIUsage(userMessage, finalResponse, source, Date.now() - startTime);
        
        return { text: finalResponse };
        
    } catch (error) {
        console.error('Gemini API failed:', error);
        useLocalAI = true;
        
        source = 'local';
        finalResponse = getLocalAIResponse(userMessage).text;
        await logAIUsage(userMessage, finalResponse, source, Date.now() - startTime);
        
        return { text: finalResponse };
    }
}

function getLocalAIResponse(userMessage) {
    const isTagalog = currentLang === 'tl';
    const lowerMessage = userMessage.toLowerCase().trim();
    
    // Check for human agent request (English & Tagalog)
    const humanAgentKeywords = [
        'human agent', 'talk to agent', 'speak to agent', 'real person', 
        'live agent', 'customer service', 'talk to human', 'speak to human',
        'contact agent', 'reach agent', 'agent please', 'human please',
        'i want to talk to someone', 'can i talk to a person', 'real human',
        'makausap', 'kausapin', 'tao', 'agent', 'staff',
        'gusto ko makausap', 'pwede makausap', 'makipag-usap',
        'gusto ko ng tao', 'kausapin ang agent', 'makipag usap sa agent',
        'pwede bang makausap', 'gusto ko sana makausap'
    ];
    
    const wantsHumanAgent = humanAgentKeywords.some(keyword => lowerMessage.includes(keyword));
    
    if (wantsHumanAgent) {
        setTimeout(() => {
            requestHumanAgent();
        }, 500);
        
        if (isTagalog) {
            return { text: "Naiintindihan ko. Gusto mo bang makausap ang aming human agent? Pakilagay ang iyong contact information sa ibaba." };
        } else {
            return { text: "I understand. Would you like to speak with a human agent? Please provide your contact information below." };
        }
    }
    
    // Simple greeting responses
    const greetings = ['hi', 'hello', 'hey', 'good morning', 'good afternoon', 'good evening'];
    if (greetings.some(g => lowerMessage === g || lowerMessage.startsWith(g))) {
        if (isTagalog) {
            return { text: "Kumusta! Ako ang iyong Pitzy assistant. Paano kita matutulungan ngayong araw?" };
        } else {
            return { text: "Hello! I'm your Pitzy assistant. How can I help you today?" };
        }
    }
    
    // Thank you responses
    if (lowerMessage.includes('thank') || lowerMessage.includes('thanks') || lowerMessage.includes('salamat')) {
        if (isTagalog) {
            return { text: "Walang anuman! May iba pa ba akong matutulong sa iyo?" };
        } else {
            return { text: "You're welcome! Is there anything else I can help you with?" };
        }
    }
    
    // Solar/Internet related check
    const solarKeywords = [
        'solar', 'panel', 'sun', 'energy', 'battery', 'inverter', 'off-grid', 
        'net metering', 'installation', 'roof', 'power', 'electricity',
        'solar panel', 'solar energy', 'solar power'
    ];
    
    const internetKeywords = [
        'internet', 'wifi', 'fiber', 'broadband', 'speed', 'connection', 
        'data', 'router', 'signal', 'bandwidth', 'latency', 'ping',
        'download', 'upload', 'streaming', 'gaming', 'lag'
    ];
    
    const isSolarRelated = solarKeywords.some(keyword => lowerMessage.includes(keyword));
    const isInternetRelated = internetKeywords.some(keyword => lowerMessage.includes(keyword));
    
    let response = '';
    
    if (!isSolarRelated && !isInternetRelated) {
        if (isTagalog) {
            response = "Paumanhin, ang tanong mo ay hindi related sa aming solar at internet topics. Gusto mo bang makausap ang aming human agent?";
        } else {
            response = "I'm sorry, your question is not related to our solar and internet topics. Would you like to speak with a human agent?";
        }
    } else if (lowerMessage.includes('ticket') || lowerMessage.includes('follow') || lowerMessage.includes('#')) {
        if (isTagalog) {
            response = "Para sa ticket concerns, maaari mong i-check ang iyong ticket status gamit ang 'Check Ticket' button sa ibaba.";
        } else {
            response = "For ticket concerns, you can check your ticket status using the 'Check Ticket' button below.";
        }
    } else if (isSolarRelated) {
        if (isTagalog) {
            response = "Salamat sa iyong mensahe tungkol sa solar. Maaari mo bang ilahad nang mas detalyado ang iyong concern tungkol sa solar panels o installation?";
        } else {
            response = "Thank you for your solar-related message. Could you please provide more details about your solar panel or installation concern?";
        }
    } else if (isInternetRelated) {
        if (isTagalog) {
            response = "Salamat sa iyong mensahe tungkol sa internet. Maaari mo bang ilahad nang mas detalyado ang iyong concern tungkol sa connection o speed?";
        } else {
            response = "Thank you for your internet-related message. Could you please provide more details about your connection or speed concern?";
        }
    } else {
        if (isTagalog) {
            response = "Salamat sa iyong mensahe. Maaari mo bang ilahad nang mas detalyado ang iyong concern?";
        } else {
            response = "Thank you for your message. Could you please provide more details about your concern?";
        }
    }
    
    conversationHistory.push({ role: 'user', content: userMessage });
    conversationHistory.push({ role: 'assistant', content: response });
    
    return { text: response };
}

async function logAIUsage(userMessage, aiResponse, source, responseTime) {
    try {
        await fetch('chat/log_ai_usage.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                session_id: chatSessionId,
                user_message: userMessage,
                ai_response: aiResponse,
                source: source,
                response_time: responseTime
            })
        });
    } catch (error) {
        console.error('Error logging AI usage:', error);
    }
}

function showTicketInputForm() {
    const texts = translations[currentLang];
    
    const formDiv = document.createElement("div");
    formDiv.className = "info-form";
    formDiv.id = "ticketForm";
    formDiv.innerHTML = `
        <div class="form-group">
            <input type="text" id="ticketNumberInput" placeholder="${texts.ticketPlaceholder}" class="form-input" required>
            <small class="form-hint">${texts.enterTicket}</small>
        </div>
        <button onclick="checkTicketNumber()" class="submit-btn btn-orange">Check Ticket</button>
        <button onclick="cancelTicketCheck()" class="submit-btn btn-secondary">Cancel</button>
    `;
    
    chatBody.appendChild(formDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
}

function checkTicketNumber() {
    const ticketInput = document.getElementById("ticketNumberInput");
    if (!ticketInput) return;
    
    let ticketNumber = ticketInput.value.trim().toUpperCase();
    const texts = translations[currentLang];
    
    document.getElementById("ticketForm")?.remove();
    
    if (!ticketNumber.startsWith('#')) {
        ticketNumber = '#' + ticketNumber;
    }
    
    const numPart = ticketNumber.replace('#', '');
    if (!/^\d+$/.test(numPart) || numPart.length > 4) {
        addMsg(texts.invalidTicket, "left");
        setTimeout(() => {
            askForMoreHelp();
        }, 2000);
        return;
    }
    
    addMsg(ticketNumber, "right");
    currentTicketNumber = ticketNumber;
    
    showTypingIndicator();
    
    sessionStorage.setItem('currentTicket', ticketNumber);
    
    setTimeout(() => {
        hideTypingIndicator();
        addMsg(texts.askTicketPhone, "left");
        showTicketPhoneInput(ticketNumber);
    }, 1000);
}

function showTicketPhoneInput(ticketNumber) {
    const texts = translations[currentLang];
    
    const formDiv = document.createElement("div");
    formDiv.className = "info-form";
    formDiv.id = "ticketPhoneForm";
    formDiv.innerHTML = `
        <div class="form-group">
            <input type="tel" id="ticketPhoneInput" placeholder="09123456789" class="form-input" required 
                   onkeypress="return event.charCode >= 48 && event.charCode <= 57" 
                   maxlength="11">
            <small class="form-hint">${texts.phoneLengthError}</small>
        </div>
        <button onclick="verifyTicketWithDatabase('${ticketNumber}')" class="submit-btn btn-orange">Verify & Check Status</button>
        <button onclick="cancelTicketCheck()" class="submit-btn btn-secondary">Cancel</button>
    `;
    
    chatBody.appendChild(formDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
}

async function verifyTicketWithDatabase(ticketNumber) {
    const phoneInput = document.getElementById("ticketPhoneInput");
    if (!phoneInput) return;
    
    const phone = phoneInput.value.trim();
    const texts = translations[currentLang];
    
    document.getElementById("ticketPhoneForm")?.remove();
    
    if (!/^\d+$/.test(phone)) {
        alert(texts.phoneNumberError);
        showTicketPhoneInput(ticketNumber);
        return;
    }
    
    if (phone.length !== 11) {
        alert(texts.phoneLengthError);
        showTicketPhoneInput(ticketNumber);
        return;
    }
    
    if (!/^09\d{9}$/.test(phone)) {
        alert(texts.invalidPhone);
        showTicketPhoneInput(ticketNumber);
        return;
    }
    
    addMsg(phone, "right");
    
    showTypingIndicator();
    
    try {
        const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
        const apiUrl = baseUrl + 'chat/get_ticket_status.php';
        
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                ticket_number: ticketNumber,
                phone: phone
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        hideTypingIndicator();
        
        if (data.success) {
            const ticket = data.ticket;
            
            addMsg(texts.ticketVerified, "left");
            
            const statusMsg = texts.ticketStatus
                .replace('{ticket}', ticket.ticket)
                .replace('{name}', ticket.name)
                .replace('{status}', ticket.status)
                .replace('{date}', ticket.date)
                .replace('{time}', ticket.time)
                .replace('{updated}', ticket.updated)
                .replace('{dept}', ticket.dept)
                .replace('{assigned}', ticket.assigned)
                .replace('{concern}', ticket.concern)
                .replace('{message}', ticket.message);
            
            addMsg(statusMsg, "left");
            
            conversationHistory.push({ 
                role: 'system', 
                content: `Ticket ${ticketNumber} status: ${ticket.status_raw}` 
            });
            
            setTimeout(() => {
                askForMoreHelp();
            }, 4000);
            
        } else {
            addMsg(data.message, "left");
            
            setTimeout(() => {
                const optionsDiv = document.createElement("div");
                optionsDiv.className = "options";
                optionsDiv.id = "ticketRetryOptions";
                optionsDiv.innerHTML = `
                    <button onclick="retryTicketCheck()" class="btn-green">🔄 ${texts.retryTicket}</button>
                    <button onclick="showCategoryOptions()" class="btn-green">📋 ${texts.mainMenu}</button>
                    <button onclick="requestHumanAgent()" class="btn-green">👤 ${texts.talkToAgent}</button>
                `;
                chatBody.appendChild(optionsDiv);
                chatBody.scrollTop = chatBody.scrollHeight;
            }, 1500);
        }
        
    } catch (error) {
        hideTypingIndicator();
        console.error('Detailed error:', error);
        
        let errorMsg = "❌ Error connecting to server. ";
        
        if (error.message.includes('Failed to fetch')) {
            errorMsg += "Please check if the server is running and the chat/get_ticket_status.php file exists.";
        } else {
            errorMsg += "Please try again.";
        }
        
        addMsg(errorMsg, "left");
        
        setTimeout(() => {
            askForMoreHelp();
        }, 3000);
    }
}

function retryTicketCheck() {
    document.getElementById("ticketRetryOptions")?.remove();
    const lastTicket = sessionStorage.getItem('currentTicket');
    if (lastTicket) {
        showTicketInputForm();
    } else {
        showCategoryOptions();
    }
}

function cancelTicketCheck() {
    document.getElementById("ticketForm")?.remove();
    document.getElementById("ticketPhoneForm")?.remove();
    document.getElementById("ticketRetryOptions")?.remove();
    
    addMsg("Ticket check cancelled", "right");
    setTimeout(() => {
        askForMoreHelp();
    }, 1000);
}

async function saveMessage(sender_type, message) {
    try {
        await fetch('chat/save_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                session_id: chatSessionId,
                sender_type: sender_type,
                message: message
            })
        });
    } catch (error) {
        console.error('Error saving message:', error);
    }
}

async function sendText() {
    if (currentLang === null) {
        addMsg(translations.en.selectLanguageFirst, "left");
        return;
    }
    
    const text = input.value.trim();
    if (!text) return;
    
    input.value = "";
    addMsg(text, "right");
    await saveMessage('user', text);
    
    // Check for human agent request (English & Tagalog)
    const lowerText = text.toLowerCase();
    const humanAgentKeywords = [
        'human agent', 'talk to agent', 'speak to agent', 'real person', 
        'live agent', 'customer service', 'talk to human', 'speak to human',
        'contact agent', 'reach agent', 'agent please', 'human please',
        'i want to talk to someone', 'can i talk to a person',
        'makausap', 'kausapin', 'tao', 'agent', 'staff',
        'gusto ko makausap', 'pwede makausap', 'makipag-usap',
        'gusto ko ng tao', 'kausapin ang agent', 'makipag usap sa agent',
        'pwede bang makausap', 'gusto ko sana makausap'
    ];
    
    const wantsHumanAgent = humanAgentKeywords.some(keyword => lowerText.includes(keyword));
    
    if (wantsHumanAgent && !staffJoined) {
        const texts = translations[currentLang];
        addMsg(texts.connectingToAgent || "Connecting you to a human agent. Please wait...", "left");
        setTimeout(() => {
            requestHumanAgent();
        }, 1000);
        return;
    }
    
    if (!staffJoined) {
        await processUserInput(text);
    }
}

async function processUserInput(text) {
    const lowerText = text.toLowerCase();
    
    // Check for human agent request (English & Tagalog)
    const humanAgentKeywords = [
        'human agent', 'talk to agent', 'speak to agent', 'real person', 
        'live agent', 'customer service', 'talk to human', 'speak to human',
        'contact agent', 'reach agent', 'agent please', 'human please',
        'i want to talk to someone', 'can i talk to a person', 'real human',
        'makausap', 'kausapin', 'tao', 'agent', 'staff', 'customer service',
        'gusto ko makausap', 'pwede makausap', 'makipag-usap',
        'gusto ko ng tao', 'kausapin ang agent', 'makipag usap sa agent',
        'pwede bang makausap', 'gusto ko sana makausap'
    ];
    
    const wantsHumanAgent = humanAgentKeywords.some(keyword => lowerText.includes(keyword));
    
    if (wantsHumanAgent) {
        hideTypingIndicator();
        const texts = translations[currentLang];
        addMsg(texts.connectingToAgent || "Connecting you to a human agent. Please wait...", "left");
        
        setTimeout(() => {
            requestHumanAgent();
        }, 1000);
        return;
    }
    
    showTypingIndicator();
    
    try {
        const aiResponse = await getAIResponse(text);
        hideTypingIndicator();
        
        addMsg(aiResponse.text, "left");
        
        const needsHumanAgent = aiResponse.text.toLowerCase().includes('human agent') || 
                                aiResponse.text.toLowerCase().includes('makausap ang aming human agent');
        
        if (needsHumanAgent) {
            setTimeout(() => {
                askForHumanAgentConfirmation();
            }, 1500);
        } else {
            setTimeout(() => {
                askForMoreHelp();
            }, 2000);
        }
        
    } catch (error) {
        hideTypingIndicator();
        const texts = translations[currentLang];
        addMsg(texts.notUnderstood, "left");
        
        setTimeout(() => {
            askForHumanAgentConfirmation();
        }, 1500);
    }
}

function askForHumanAgentConfirmation() {
    const texts = translations[currentLang];
    addMsg(texts.cantAnswer, "left");
    
    const optionsDiv = document.createElement("div");
    optionsDiv.className = "options";
    optionsDiv.id = "humanAgentOptions";
    optionsDiv.innerHTML = `
        <button onclick="requestHumanAgent()" class="btn-orange">👤 ${texts.humanAgent}</button>
        <button onclick="continueWithAI()" class="btn-blue">🤖 ${texts.continueAI}</button>
    `;
    
    chatBody.appendChild(optionsDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
}

function continueWithAI() {
    document.getElementById("humanAgentOptions")?.remove();
    addMsg(translations[currentLang].continueAI, "right");
    setTimeout(() => {
        askForMoreHelp();
    }, 1000);
}

function requestHumanAgent() {
    document.getElementById("humanAgentOptions")?.remove();
    document.getElementById("helpOptions")?.remove();
    document.getElementById("categoryOptions")?.remove();
    
    const texts = translations[currentLang];
    addMsg(texts.humanAgent, "right");
    
    setTimeout(() => {
        askForContactInfo();
    }, 1000);
}

function askForContactInfo() {
    const texts = translations[currentLang];
    
    document.getElementById("contactInfoForm")?.remove();
    
    addMsg(texts.askPhone, "left");
    
    const formDiv = document.createElement("div");
    formDiv.className = "info-form";
    formDiv.id = "contactInfoForm";
    formDiv.innerHTML = `
        <div class="form-group">
            <input type="tel" id="userPhoneInput" placeholder="09123456789" class="form-input" required 
                   onkeypress="return event.charCode >= 48 && event.charCode <= 57" 
                   maxlength="11">
            <small class="form-hint">${texts.phoneLengthError}</small>
        </div>
        <div class="form-group">
            <input type="text" id="userNameInput" placeholder="${texts.askName}" class="form-input">
            <small class="form-hint">${texts.nameOptional}</small>
        </div>
        <button onclick="submitContactInfo()" class="submit-btn btn-orange">Submit</button>
        <button onclick="cancelContactRequest()" class="submit-btn btn-secondary">${texts.cancelRequest || 'Cancel'}</button>
    `;
    
    chatBody.appendChild(formDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
}

function cancelContactRequest() {
    document.getElementById("contactInfoForm")?.remove();
    const texts = translations[currentLang];
    addMsg("Request cancelled. How else can I help you?", "left");
    setTimeout(() => {
        showHelpOptions();
    }, 1000);
}

async function submitContactInfo() {
    const phone = document.getElementById("userPhoneInput").value.trim();
    const name = document.getElementById("userNameInput").value.trim();
    const texts = translations[currentLang];

    const lastSubmitted = localStorage.getItem(`submitted_${phone}`);
    if (lastSubmitted) {
        const elapsed = Date.now() - parseInt(lastSubmitted);
        if (elapsed < 6 * 60 * 60 * 1000) {
            alert("You already submitted your issue. Please try again after 6 hours.");
            return;
        }
    }

    if (!/^\d+$/.test(phone)) {
        alert(texts.phoneNumberError);
        return;
    }

    if (phone.length !== 11) {
        alert(texts.phoneLengthError);
        return;
    }

    if (!/^09\d{9}$/.test(phone)) {
        alert(texts.invalidPhone);
        return;
    }

    document.getElementById("contactInfoForm")?.remove();

    if (name) {
        addMsg(name, "right");
        userName = name;
    }
    addMsg(phone, "right");
    userPhone = phone;

    showTypingIndicator();

    try {
        await fetch('chat/save_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                mobile_number: phone,
                name: name
            })
        });

        const callbackResponse = await fetch('chat/save_callback.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                session_id: chatSessionId,
                phone: phone,
                name: name,
                reason: "Customer requested human agent assistance"
            })
        });

        const callbackData = await callbackResponse.json();
        
        if (callbackData.success) {
            hideTypingIndicator();
            
            const ticketId = callbackData.request_id;
            const formattedTicket = formatTicketNumber(ticketId);
            
            const ticketMsg = texts.ticketGenerated.replace('{ticket}', formattedTicket);
            addMsg(ticketMsg, "left");
            
            setTimeout(() => {
                addMsg(`📝 Your ticket #${ticketId} has been recorded in our system. Use ${formattedTicket} for follow-ups.`, "left");
                addMsg(texts.ticketInfo, "left");
            }, 2000);
            
            staffRequested = true;

            localStorage.setItem(`submitted_${phone}`, Date.now());
            localStorage.setItem(`ticket_${phone}`, formattedTicket);
            localStorage.setItem(`ticket_id_${phone}`, ticketId);
            
            sessionStorage.setItem('lastTicket', formattedTicket);
            sessionStorage.setItem('lastTicketId', ticketId);

            input.disabled = false;
            sendBtn.disabled = false;
            input.placeholder = texts.inputPlaceholder;

            startStaffMessageCheck();

            setTimeout(() => askForMoreHelp(), 4000);
        } else {
            hideTypingIndicator();
            addMsg("Error submitting request. Please try again.", "left");
            setTimeout(() => askForMoreHelp(), 2000);
        }
    } catch (error) {
        hideTypingIndicator();
        console.error('Error:', error);
        addMsg(texts.errorServer, "left");
        setTimeout(() => askForMoreHelp(), 2000);
    }
}

function startStaffMessageCheck() {
    if (staffMessageInterval) {
        clearInterval(staffMessageInterval);
    }
    
    console.log("Started checking for staff/admin messages");
    staffMessageInterval = setInterval(checkForStaffMessages, 3000);
}

async function checkForStaffMessages() {
    if (!chatSessionId) return;
    
    try {
        const response = await fetch('chat/get_staff_messages.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                session_id: chatSessionId,
                last_check: lastMessageCheck
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.messages && data.messages.length > 0) {
            hideStaffTypingIndicator();
            
            if (!staffJoined && data.messages.length > 0) {
                staffJoined = true;
                const texts = translations[currentLang];
                
                const firstMsg = data.messages[0];
                if (firstMsg.sender_type === 'admin') {
                    addMsg(texts.adminJoined || "👨‍💼 An administrator has joined the chat.", "left", false, true);
                } else {
                    addMsg(texts.staffJoined || "👨‍💼 A staff member has joined the chat.", "left", true, false);
                }
            }
            
            data.messages.forEach(msg => {
                const messageId = `msg_${msg.id}`;
                
                if (!displayedMessageIds.has(messageId)) {
                    const time = new Date(msg.created_at).toLocaleTimeString();
                    
                    let senderLabel = '';
                    let isAdmin = false;
                    let isStaff = false;
                    let senderName = msg.display_name || (msg.sender_type === 'admin' ? 'Admin' : 'Staff');
                    
                    if (msg.sender_type === 'admin') {
                        senderLabel = `<strong>👨‍💼 ${senderName}</strong> <span class="admin-badge">Admin</span>`;
                        isAdmin = true;
                    } else if (msg.sender_type === 'staff') {
                        senderLabel = `<strong>👨‍💼 ${senderName}</strong> <span class="staff-badge">Staff</span>`;
                        isStaff = true;
                    }
                    
                    const messageText = `${senderLabel}<br>${escapeHtml(msg.message)}<br><small>${time}</small>`;
                    
                    addMsg(messageText, "left", isStaff, isAdmin);
                    
                    displayedMessageIds.add(messageId);
                    
                    conversationHistory.push({ 
                        role: msg.sender_type, 
                        content: msg.message,
                        name: senderName
                    });
                }
            });
            
            lastMessageCheck = data.last_check;
        }
    } catch (error) {
        console.error('Error checking for staff messages:', error);
    }
}

function showStaffTypingIndicator() {
    const texts = translations[currentLang];
    const typingDiv = document.createElement('div');
    typingDiv.className = 'typing-indicator';
    typingDiv.id = 'staffTypingIndicator';
    typingDiv.innerHTML = `
        <span>${texts.staffTyping}</span>
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
    `;
    chatBody.appendChild(typingDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
}

function hideStaffTypingIndicator() {
    const typingIndicator = document.getElementById('staffTypingIndicator');
    if (typingIndicator) typingIndicator.remove();
}

function askForMoreHelp() {
    if (helpShown) {
        return;
    }
    
    const texts = translations[currentLang];
    
    setTimeout(() => {
        addMsg(texts.thankYou, "left");
        showHelpOptions();
        helpShown = true;
    }, 1000);
}

function showHelpOptions() {
    const helpDiv = document.createElement("div");
    helpDiv.className = "options";
    helpDiv.id = "helpOptions";
    
    const lastTicket = sessionStorage.getItem('lastTicket') || localStorage.getItem('lastTicket');
    
    let buttons = `<button onclick="startNewTopic()" class="btn-blue">🆕 New Topic</button>`;
    buttons += `<button onclick="showCategoryOptions()" class="btn-green">📋 Main Menu</button>`;
    
    if (lastTicket) {
        buttons += `<button onclick="quickCheckTicket('${lastTicket}')" class="btn-orange">🎫 Check Ticket ${lastTicket}</button>`;
    } else {
        buttons += `<button onclick="showTicketInputForm()" class="btn-orange">🎫 Check My Ticket</button>`;
    }
    
    buttons += `<button onclick="requestHumanAgent()" class="btn-blue">👤 Talk to Human Agent</button>`;
    
    helpDiv.innerHTML = buttons;
    chatBody.appendChild(helpDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
}

function quickCheckTicket(ticketNumber) {
    document.getElementById("helpOptions")?.remove();
    currentTicketNumber = ticketNumber;
    addMsg(`📋 Checking ticket: ${ticketNumber}`, "right");
    setTimeout(() => {
        addMsg(translations[currentLang].askTicketPhone, "left");
        showTicketPhoneInput(ticketNumber);
    }, 1000);
}

function startNewTopic() {
    const helpOptions = document.getElementById("helpOptions");
    if (helpOptions) helpOptions.remove();
    
    helpShown = false;
    
    addMsg("--- New Conversation Topic ---", "left");
    
    setTimeout(() => {
        const texts = translations[currentLang];
        addMsg(texts.chooseIssue, "left");
        showCategoryOptions();
    }, 500);
}

function showTypingIndicator() {
    const texts = translations[currentLang];
    const typingDiv = document.createElement('div');
    typingDiv.className = 'typing-indicator';
    typingDiv.id = 'typingIndicator';
    typingDiv.innerHTML = `
        <span>${texts.aiThinking}</span>
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
    `;
    chatBody.appendChild(typingDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
}

function hideTypingIndicator() {
    const typingIndicator = document.getElementById('typingIndicator');
    if (typingIndicator) {
        typingIndicator.remove();
    }
}

function addMsg(text, side, isStaff = false, isAdmin = false) {
    const d = document.createElement("div");
    
    if (isAdmin) {
        d.className = "msg admin";
    } else if (isStaff) {
        d.className = "msg staff";
    } else {
        d.className = "msg " + side;
    }
    
    d.innerHTML = text.replace(/\n/g, '<br>');
    
    if (languageOptions && languageOptions.parentNode === chatBody && languageOptions.style.display !== 'none') {
        chatBody.insertBefore(d, languageOptions.nextSibling);
    } else {
        chatBody.appendChild(d);
    }
    
    chatBody.scrollTop = chatBody.scrollHeight;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function minimizeChat() {
    chatBox.style.display = "none";
    chatMini.style.display = "flex";
    chatBubble.style.display = "none";
}

function restoreChat() {
    chatBox.style.display = "flex";
    chatMini.style.display = "none";
    chatBubble.style.display = "none";
}

function toggleMax() {
    chatBox.classList.toggle("maximized");
}

function closeChat() {
    chatBox.style.display = "none";
    chatMini.style.display = "none";
    chatBubble.style.display = "flex";
    
    if (staffMessageInterval) {
        clearInterval(staffMessageInterval);
        staffMessageInterval = null;
    }
    
    staffJoined = false;
    displayedMessageIds.clear();
}

function openChat() {
    chatBox.style.display = "flex";
    chatBubble.style.display = "none";
    
    if (!currentLang) {
        initializeChat();
    }
}

window.onload = function() {
    console.log("Window loaded - setting initial state");
    
    chatBox.style.display = "none";
    chatMini.style.display = "none";
    chatBubble.style.display = "flex";
    
    input.addEventListener("keypress", function(event) {
        if (event.key === "Enter") {
            event.preventDefault();
            sendText();
        }
    });
};