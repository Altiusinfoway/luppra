"use strict";

const device_id = $("#uuid").val();
const base_url = $("#base_url").val();
const whatsappicon = base_url + "/public/uploads/whatsapp.png";

let activePhone = null;
let activeJid = null;
let messagesPolling = null;
let chatsPolling = null;
let chatListRequest = null;
const prefilledPhone = getPrefilledPhone();
const mediaCache = new Map();
const defaultThreadSubtext = $(".selected-contact-sub").text();
const chatReadStorageKey = `wa_chat_read_state_${device_id}`;
const chatListPollingMs = 5000;
const chatScrollBottomThreshold = 120;

checkSession();

function whatsappStatusMessage(status) {
    const messages = {
        connected: "WhatsApp session is connected.",
        connecting: "WhatsApp is still connecting. Please try again in a moment.",
        disconnected: "WhatsApp session is disconnected. Please reconnect this device.",
        qr_required: "Please scan the QR code to connect this device.",
        not_ready: "WhatsApp session is not ready yet. Please reconnect this device.",
    };

    return messages[String(status || "").trim()] || messages.not_ready;
}

function checkSession() {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $.ajax({
        type: "POST",
        url: base_url + "/check-session/" + device_id,
        dataType: "json",
        success: function (response) {
            if (response.connected === true) {
                $(".server_disconnect").remove();
                $(".qr-area").remove();
                NotifyAlert("success", null, response.message);
                getChatList({ autoOpenPrefilled: true });
                startChatListPolling();
                return;
            }

            stopChatListPolling();
            NotifyAlert("error", null, response.message || whatsappStatusMessage(response.status));
        },
        error: function (xhr) {
            if (xhr.status === 500) {
                stopChatListPolling();
                const image = `<img src="${base_url}/public/uploads/disconnect.webp" class="w-50"><br>`;
                $(".qr-area").html(image);
                $(".server_disconnect").show();
            }
        },
    });
}

function getChatList(options = {}) {
    if (chatListRequest) {
        return;
    }

    chatListRequest = $.ajax({
        type: "POST",
        url: base_url + "/get-chats/" + device_id,
        dataType: "json",
        success: function (response) {
            const chats = sortByKey(response.chats || [], "timestamp");
            $(".qr-area").remove();
            $(".contact-list").empty();

            $.each(chats, function (key, item) {
                const number = item.number || "";
                const displayName = (item.display_name || "").trim() || (number ? `+${number}` : "");
                const preview = formatChatPreview(item, number);
                const chatJid = item.jid || item.pn_jid || item.alt_jid || "";
                const unread = visibleUnreadCount(item, number, chatJid);
                let time = "";

                if (item.timestamp > 0) {
                    time = formatTimestamp(item.timestamp);
                }

                const unreadBadge =
                    unread > 0 ? `<span class="wa-unread">${unread}</span>` : "";

                const subLabel = number
                    ? `+${escapeHtml(number)}`
                    : "";
                const html = `<div class="wa-contact contact${key}" data-active=".contact${key}" data-number="${escapeHtml(number)}" data-jid="${escapeHtml(chatJid)}" data-label="${escapeHtml(displayName)}" data-timestamp="${escapeHtml(normalizeTimestamp(item.timestamp))}">
                        <img alt="" src="${whatsappicon}" class="wa-avatar">
                        <div class="wa-contact-main">
                            <div class="wa-contact-row">
                                <div class="wa-contact-name">${escapeHtml(displayName)}</div>
                                <div class="wa-contact-time">${escapeHtml(time)}</div>
                            </div>
                            <div class="wa-contact-row">
                                <div class="wa-contact-preview">${escapeHtml(preview || subLabel)}</div>
                                ${unreadBadge}
                            </div>
                        </div>
                </div>`;

                $(".contact-list").append(html);
            });

            syncSearchFallback();
            refreshActiveContactHighlight();
            if (options.autoOpenPrefilled) {
                autoOpenPrefilledChat();
            }
        },
        complete: function () {
            chatListRequest = null;
        },
    });
}

function startChatListPolling() {
    if (chatsPolling) {
        return;
    }

    chatsPolling = setInterval(function () {
        getChatList();
    }, chatListPollingMs);
}

function stopChatListPolling() {
    if (chatsPolling) {
        clearInterval(chatsPolling);
        chatsPolling = null;
    }
}

function getChatMessages(phone, jid = "", shouldScroll = false) {
    $.ajax({
        type: "POST",
        url: base_url + "/get-chat-messages/" + device_id,
        dataType: "json",
        data: {
            number: phone,
            jid: jid,
            limit: 80,
        },
        success: function (response) {
            const messages = response.messages || [];
            renderMessages(messages, shouldScroll);

            const isActiveThread =
                (!!phone && phone === activePhone) ||
                (!!jid && jid === activeJid) ||
                (!phone && !jid && !activePhone && !activeJid);

            if (isActiveThread) {
                rememberChatRead(phone, jid, latestMessageTimestamp(messages));
                updateActiveChatSummary(messages);
                clearUnreadForActiveChat();
            }
        },
        error: function () {
            $("#message-history").html(
                '<p class="text-danger text-center mb-0">Unable to load messages</p>'
            );
        },
    });
}

function getMessageHistoryElement() {
    return document.getElementById("message-history");
}

function isNearBottom(element, threshold = chatScrollBottomThreshold) {
    if (!element) {
        return true;
    }

    return (element.scrollHeight - element.scrollTop - element.clientHeight) <= threshold;
}

function scrollMessageHistoryToBottom() {
    const element = getMessageHistoryElement();
    if (!element) {
        return;
    }

    element.scrollTop = element.scrollHeight;
}

function renderMessages(messages, shouldScroll) {
    const element = getMessageHistoryElement();
    const previousScrollHeight = element ? element.scrollHeight : 0;
    const previousScrollTop = element ? element.scrollTop : 0;
    const wasNearBottom = isNearBottom(element);

    if (!Array.isArray(messages) || messages.length === 0) {
        $("#message-history").html(
            '<p class="text-muted text-center mb-0">No messages found for this contact</p>'
        );
        if (shouldScroll) {
            scrollMessageHistoryToBottom();
        }
        return;
    }

    messages.sort(function (a, b) {
        return (a.timestamp || 0) - (b.timestamp || 0);
    });

    let html = "";
    $.each(messages, function (_, message) {
        const fromMe = !!message.from_me;
        const rowClass = fromMe ? "right" : "left";
        const bubbleClass = fromMe ? "right" : "left";
        const text = escapeHtml(message.text || "[Unsupported message]");
        const time = formatTime(message.timestamp || 0);
        const mediaHtml = renderMediaPlaceholder(message);
        const captionHtml = shouldRenderCaption(message)
            ? `<div class="wa-caption">${text}</div>`
            : (!message.media ? `<div>${text}</div>` : "");

        html += `<div class="wa-row ${rowClass}">
            <div class="wa-bubble ${bubbleClass}">
                ${mediaHtml}
                ${captionHtml}
                <small class="wa-bubble-time">${time}</small>
            </div>
        </div>`;
    });

    $("#message-history").html(html);
    hydrateVisibleMedia(messages);

    const nextElement = getMessageHistoryElement();
    if (!nextElement) {
        return;
    }

    if (shouldScroll || wasNearBottom) {
        nextElement.scrollTop = nextElement.scrollHeight;
        return;
    }

    const nextScrollHeight = nextElement.scrollHeight;
    const heightDelta = nextScrollHeight - previousScrollHeight;
    nextElement.scrollTop = Math.max(0, previousScrollTop + heightDelta);
}

function renderMediaPlaceholder(message) {
    if (!message || !message.media) {
        return "";
    }

    const media = message.media;
    const messageId = escapeHtml(message.id || "");
    const remoteJid = escapeHtml(message.remote_jid || "");
    const fileName = escapeHtml(media.file_name || `${media.kind || "file"}`);

    return `<div class="wa-media-box"
            data-message-id="${messageId}"
            data-remote-jid="${remoteJid}"
            data-media-kind="${escapeHtml(media.kind || "")}">
            <div class="wa-media-loading">Loading ${escapeHtml(media.kind || "media")}...</div>
            <div class="wa-media-fallback">${fileName}</div>
        </div>`;
}

function shouldRenderCaption(message) {
    if (!message || !message.media) {
        return false;
    }

    const text = String(message.text || "").trim();
    const placeholders = new Set(["[Image]", "[Video]", "[Audio]", "[Document]", "[Unsupported message]"]);
    return text !== "" && !placeholders.has(text);
}

function hydrateVisibleMedia(messages) {
    if (!Array.isArray(messages)) {
        return;
    }

    messages.forEach((message) => {
        if (!message || !message.media || !message.id || !message.remote_jid) {
            return;
        }

        const cacheKey = `${message.remote_jid}:${message.id}`;
        if (mediaCache.has(cacheKey)) {
            applyMediaToDom(message, mediaCache.get(cacheKey));
            return;
        }

        fetchMediaAttachment(message, cacheKey);
    });
}

function fetchMediaAttachment(message, cacheKey) {
    $.ajax({
        type: "POST",
        url: base_url + "/chats/download-media?id=" + encodeURIComponent(device_id),
        dataType: "json",
        global: false,
        data: {
            remoteJid: message.remote_jid,
            messageId: message.id,
        },
        success: function (response) {
            if (!response || !response.success || !response.data) {
                markMediaUnavailable(message);
                return;
            }

            mediaCache.set(cacheKey, response.data);
            applyMediaToDom(message, response.data);
        },
        error: function () {
            markMediaUnavailable(message);
        },
    });
}

function applyMediaToDom(message, mediaData) {
    const selector = `.wa-media-box[data-message-id="${cssEscape(message.id)}"][data-remote-jid="${cssEscape(message.remote_jid)}"]`;
    const element = document.querySelector(selector);
    if (!element || !mediaData) {
        return;
    }

    const container = getMessageHistoryElement();
    const previousScrollHeight = container ? container.scrollHeight : 0;
    const previousScrollTop = container ? container.scrollTop : 0;
    const wasNearBottom = isNearBottom(container);

    element.innerHTML = buildMediaContent(message, mediaData);

    if (!container) {
        return;
    }

    if (wasNearBottom) {
        container.scrollTop = container.scrollHeight;
        return;
    }

    const nextScrollHeight = container.scrollHeight;
    container.scrollTop = Math.max(0, previousScrollTop + (nextScrollHeight - previousScrollHeight));
}

function markMediaUnavailable(message) {
    const selector = `.wa-media-box[data-message-id="${cssEscape(message.id)}"][data-remote-jid="${cssEscape(message.remote_jid)}"]`;
    const element = document.querySelector(selector);
    if (!element) {
        return;
    }

    const fallback = element.querySelector(".wa-media-fallback");
    const fallbackText = fallback ? fallback.textContent : "Media";
    const container = getMessageHistoryElement();
    const previousScrollHeight = container ? container.scrollHeight : 0;
    const previousScrollTop = container ? container.scrollTop : 0;
    const wasNearBottom = isNearBottom(container);

    element.innerHTML = `<div class="wa-media-fallback">${escapeHtml(fallbackText)} unavailable</div>`;

    if (!container) {
        return;
    }

    if (wasNearBottom) {
        container.scrollTop = container.scrollHeight;
        return;
    }

    const nextScrollHeight = container.scrollHeight;
    container.scrollTop = Math.max(0, previousScrollTop + (nextScrollHeight - previousScrollHeight));
}

function buildMediaContent(message, mediaData) {
    const media = message.media || {};
    const mimeType = String(mediaData.mimetype || media.mime_type || "");
    const fileName = escapeHtml(mediaData.fileName || media.file_name || `${media.kind || "file"}`);
    const base64 = String(mediaData.base64 || "");
    const dataUrl = base64 ? `data:${mimeType || "application/octet-stream"};base64,${base64}` : "";
    const caption = escapeHtml(media.caption || "");

    if (!dataUrl) {
        return `<div class="wa-media-fallback">${fileName}</div>`;
    }

    if (media.kind === "image") {
        return `<a href="${dataUrl}" target="_blank" rel="noopener noreferrer">
                <img src="${dataUrl}" alt="${fileName}" class="wa-inline-image">
            </a>`;
    }

    if (media.kind === "video") {
        return `<video controls class="wa-inline-video">
                <source src="${dataUrl}" type="${escapeHtml(mimeType)}">
            </video>`;
    }

    if (media.kind === "audio") {
        return `<audio controls class="wa-inline-audio">
                <source src="${dataUrl}" type="${escapeHtml(mimeType)}">
            </audio>
            ${caption ? `<div class="wa-caption">${caption}</div>` : ""}`;
    }

    return `<a href="${dataUrl}" download="${fileName}" class="wa-doc-card">
            <div class="wa-doc-icon">PDF</div>
            <div class="wa-doc-meta">
                <div class="wa-doc-name">${fileName}</div>
                <div class="wa-doc-type">${escapeHtml(mimeType || "Document")}</div>
            </div>
        </a>`;
}

function startPolling(phone, jid) {
    if (messagesPolling) {
        clearInterval(messagesPolling);
    }

    messagesPolling = setInterval(function () {
        if (!activePhone && !activeJid) {
            return;
        }
        getChatMessages(phone, jid, false);
    }, 5000);
}

function stopPolling() {
    if (messagesPolling) {
        clearInterval(messagesPolling);
        messagesPolling = null;
    }
}

function successCallBack() {
    $("#plain-text").val("");
    $("#chat-file-input").val("");
    $("#selected-file-pill").hide();
    $("#selected-file-name").text("");
    if (activePhone || activeJid) {
        getChatMessages(activePhone, activeJid, true);
    }
}

$(document).on("click", ".wa-contact", function () {
    const phone = ($(this).data("number") || "").toString();
    const jid = ($(this).data("jid") || "").toString();
    const label = ($(this).data("label") || "").toString();
    const activeTarget = $(this).data("active");
    const timestamp = normalizeTimestamp($(this).data("timestamp") || 0);

    activePhone = phone;
    activeJid = jid;

    $(".wa-contact").removeClass("active");
    $(activeTarget).addClass("active");
    $(".initial-chat-image").addClass("none");
    $(".reciver-number").val(phone);
    $(".selected-contact-label").text(label || (phone ? "+" + phone : ""));
    $(".selected-contact-sub").text(phone ? "+" + phone : defaultThreadSubtext);

    if (phone) {
        $(".sendble-row").removeClass("none");
    } else {
        $(".sendble-row").addClass("none");
    }

    rememberChatRead(phone, jid, timestamp);
    clearUnreadForActiveChat();
    getChatMessages(phone, jid, true);
    startPolling(phone, jid);
});

$(document).on("click", "#refresh-messages", function () {
    if (activePhone || activeJid) {
        getChatMessages(activePhone, activeJid, true);
    }
});

$(document).on("click", "#open-file-picker", function () {
    $("#chat-file-input").trigger("click");
});

$(document).on("change", "#chat-file-input", function () {
    const file = this.files && this.files[0] ? this.files[0] : null;
    if (!file) {
        $("#selected-file-pill").hide();
        $("#selected-file-name").text("");
        return;
    }

    $("#selected-file-name").text(file.name);
    $("#selected-file-pill").show();
});

$(document).on("click", "#remove-selected-file", function () {
    $("#chat-file-input").val("");
    $("#selected-file-pill").hide();
    $("#selected-file-name").text("");
});

$(document).on("change", "#select-type", function () {
    const type = $(this).val();
    if (type === "plain-text") {
        $("#plain-text").show();
        $("#templates").hide();
        return;
    }

    $("#plain-text").hide();
    $("#templates").show();
});

$(window).on("beforeunload", function () {
    stopPolling();
    stopChatListPolling();
});

$(document).on("input", ".wa-search-input", function () {
    syncSearchFallback();
});

function autoOpenPrefilledChat() {
    if (!prefilledPhone) {
        return;
    }

    const target = $(`.wa-contact[data-number="${prefilledPhone}"]`);
    if (target.length) {
        target.first().trigger("click");
        return;
    }

    const html = `<div class="wa-contact contact-prefilled" data-active=".contact-prefilled" data-number="${escapeHtml(prefilledPhone)}" data-jid="" data-label="+${escapeHtml(prefilledPhone)}">
            <img alt="" src="${whatsappicon}" class="wa-avatar">
            <div class="wa-contact-main">
                <div class="wa-contact-row">
                    <div class="wa-contact-name">+${escapeHtml(prefilledPhone)}</div>
                    <div class="wa-contact-time">now</div>
                </div>
                <div class="wa-contact-row">
                    <div class="wa-contact-preview">New chat</div>
                </div>
            </div>
    </div>`;

    $(".contact-list").prepend(html);
    $(".contact-prefilled").first().trigger("click");
}

function syncSearchFallback() {
    $(".contact-search-fallback").remove();

    const input = $(".wa-search-input").first();
    if (!input.length) {
        return;
    }

    const rawSearch = String(input.val() || "").trim();
    const normalizedPhone = normalizeSearchPhone(rawSearch);
    if (!normalizedPhone) {
        return;
    }

    const existingMatch = $(`.wa-contact[data-number="${normalizedPhone}"]`).not(".contact-search-fallback");
    if (existingMatch.length) {
        return;
    }

    const html = `<div class="wa-contact contact-search-fallback" data-active=".contact-search-fallback" data-number="${escapeHtml(normalizedPhone)}" data-jid="" data-label="+${escapeHtml(normalizedPhone)}">
            <img alt="" src="${whatsappicon}" class="wa-avatar">
            <div class="wa-contact-main">
                <div class="wa-contact-row">
                    <div class="wa-contact-name">+${escapeHtml(normalizedPhone)}</div>
                    <div class="wa-contact-time">direct</div>
                </div>
                <div class="wa-contact-row">
                    <div class="wa-contact-preview">Search result</div>
                </div>
            </div>
    </div>`;

    $(".contact-list").prepend(html);
}

function refreshActiveContactHighlight() {
    $(".wa-contact").removeClass("active");

    if (!activePhone && !activeJid) {
        return;
    }

    const activeContact = $(".wa-contact").filter(function () {
        const phone = ($(this).data("number") || "").toString();
        const jid = ($(this).data("jid") || "").toString();

        return (!!activePhone && phone === activePhone) ||
            (!!activeJid && jid === activeJid);
    }).first();

    if (activeContact.length) {
        activeContact.addClass("active");
        activeContact.find(".wa-unread").remove();
    }
}

function sortByKey(array, key) {
    return array.sort(function (a, b) {
        const x = normalizeTimestamp(a[key]);
        const y = normalizeTimestamp(b[key]);
        return x > y ? -1 : x < y ? 1 : 0;
    });
}

function normalizeTimestamp(value) {
    if (typeof value === "number" && Number.isFinite(value)) {
        return value;
    }

    if (value && typeof value === "object" && typeof value.low === "number") {
        return value.low;
    }

    const casted = Number(value);
    return Number.isFinite(casted) ? casted : 0;
}

function getPrefilledPhone() {
    try {
        const params = new URLSearchParams(window.location.search);
        const raw = (params.get("phone") || "").trim();
        const digits = raw.replace(/\D/g, "");

        if (!digits) {
            return "";
        }

        if (digits.startsWith("91") && digits.length === 12) {
            return digits;
        }

        if (digits.length === 10) {
            return "91" + digits;
        }

        if (digits.startsWith("0") && digits.length === 11) {
            return "91" + digits.slice(1);
        }

        return digits;
    } catch {
        return "";
    }
}

function normalizeSearchPhone(raw) {
    const digits = String(raw || "").replace(/\D/g, "");
    if (!digits) {
        return "";
    }

    if (digits.startsWith("91") && digits.length === 12) {
        return digits;
    }

    if (digits.length === 10) {
        return "91" + digits;
    }

    if (digits.startsWith("0") && digits.length === 11) {
        return "91" + digits.slice(1);
    }

    if (digits.length >= 11 && digits.length <= 15) {
        return digits;
    }

    return "";
}

function jidLabel(jid) {
    const raw = String(jid || "").trim();
    if (!raw) {
        return "WhatsApp chat";
    }

    if (raw.endsWith("@lid")) {
        return "WhatsApp contact";
    }

    const digits = raw.replace(/\D/g, "");
    if (digits) {
        return digits;
    }

    return raw;
}

function formatChatPreview(item, number) {
    const latestMessage = normalizePreviewText(item && item.latest_message ? item.latest_message : "");
    if (latestMessage) {
        return latestMessage;
    }

    return number ? `+${number}` : "";
}

function formatMessagePreview(message) {
    if (!message) {
        return "";
    }

    return normalizePreviewText(message.text || "");
}

function normalizePreviewText(value) {
    return String(value || "").replace(/\s+/g, " ").trim();
}

function updateActiveChatSummary(messages) {
    if (!Array.isArray(messages) || messages.length === 0) {
        return;
    }

    const activeContact = $(".wa-contact.active").first();
    if (!activeContact.length) {
        return;
    }

    const sorted = [...messages].sort(function (a, b) {
        return normalizeTimestamp(a && a.timestamp) - normalizeTimestamp(b && b.timestamp);
    });
    const latestMessage = sorted[sorted.length - 1];
    if (!latestMessage) {
        return;
    }

    const preview = formatMessagePreview(latestMessage);
    if (preview) {
        activeContact.find(".wa-contact-preview").first().text(preview);
    }

    const timestamp = normalizeTimestamp(latestMessage.timestamp);
    if (timestamp > 0) {
        activeContact.find(".wa-contact-time").first().text(formatTimestamp(timestamp));
    }
}

function clearUnreadForActiveChat() {
    const activeContact = $(".wa-contact.active").first();
    if (!activeContact.length) {
        return;
    }

    activeContact.find(".wa-unread").remove();
}

function visibleUnreadCount(item, number, jid) {
    const unread = parseInt(item && item.unread ? item.unread : 0, 10);
    if (!Number.isFinite(unread) || unread <= 0) {
        return 0;
    }

    const timestamp = normalizeTimestamp(item && item.timestamp);
    const readAt = getChatReadAt(number, jid);

    if (readAt > 0 && (timestamp === 0 || timestamp <= readAt)) {
        return 0;
    }

    return unread;
}

function latestMessageTimestamp(messages) {
    if (!Array.isArray(messages) || messages.length === 0) {
        return 0;
    }

    return messages.reduce(function (latest, message) {
        return Math.max(latest, normalizeTimestamp(message && message.timestamp));
    }, 0);
}

function rememberChatRead(number, jid, timestamp) {
    const keys = chatReadKeys(number, jid);
    if (!keys.length) {
        return;
    }

    const state = loadChatReadState();
    const readAt = Math.max(
        normalizeTimestamp(timestamp),
        Math.floor(new Date().getTime() / 1000)
    );

    keys.forEach(function (key) {
        state[key] = Math.max(normalizeTimestamp(state[key]), readAt);
    });

    saveChatReadState(state);
}

function getChatReadAt(number, jid) {
    const state = loadChatReadState();
    return chatReadKeys(number, jid).reduce(function (readAt, key) {
        return Math.max(readAt, normalizeTimestamp(state[key]));
    }, 0);
}

function chatReadKeys(number, jid) {
    const keys = [];
    const normalizedNumber = String(number || "").replace(/\D/g, "");
    const normalizedJid = String(jid || "").trim();

    if (normalizedNumber) {
        keys.push(`number:${normalizedNumber}`);
    }

    if (normalizedJid) {
        keys.push(`jid:${normalizedJid}`);
    }

    return keys;
}

function loadChatReadState() {
    try {
        const parsed = JSON.parse(localStorage.getItem(chatReadStorageKey) || "{}");
        return parsed && typeof parsed === "object" ? parsed : {};
    } catch {
        return {};
    }
}

function saveChatReadState(state) {
    try {
        localStorage.setItem(chatReadStorageKey, JSON.stringify(state || {}));
    } catch {
    }
}

function formatTimestamp(unixTimestamp) {
    const nowTs = Math.floor(new Date().getTime() / 1000);
    const seconds = nowTs - unixTimestamp;

    if (seconds > 2 * 24 * 3600) return "few days ago";
    if (seconds > 24 * 3600) return "yesterday";
    if (seconds > 3600) return "few hours ago";
    if (seconds > 1800) return "30 min ago";
    if (seconds > 60) return Math.floor(seconds / 60) + " min ago";
    return "now";
}

function formatTime(unixTimestamp) {
    if (!unixTimestamp) {
        return "";
    }
    const date = new Date(unixTimestamp * 1000);
    return date.toLocaleString();
}

function escapeHtml(value) {
    const stringValue = String(value || "");
    return stringValue
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function cssEscape(value) {
    if (window.CSS && typeof window.CSS.escape === "function") {
        return window.CSS.escape(String(value || ""));
    }

    return String(value || "").replace(/"/g, '\\"');
}
