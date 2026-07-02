"use strict";

(function () {
    function resolveBaseUrl(anchor) {
        const configuredBaseUrl = document.querySelector('meta[name="app-base-url"]')?.getAttribute('content')
            || document.getElementById('base_url')?.value;

        if (configuredBaseUrl) {
            return configuredBaseUrl;
        }

        const qrUrl = anchor.getAttribute("data-qr-url") || "";
        if (qrUrl.includes("/device/")) {
            return qrUrl.split("/device/")[0];
        }

        return window.location.origin;
    }

    function notifyAndRedirect(message, destination) {
        if (typeof NotifyAlert === "function") {
            NotifyAlert("error", null, message, destination);
            return;
        }

        window.location.href = destination;
    }

    function handleChatEntryClick(event) {
        const anchor = event.currentTarget;
        const deviceUuid = anchor.getAttribute("data-device-uuid") || "";
        const chatUrl = anchor.getAttribute("data-chat-url") || anchor.href;
        const qrUrl = anchor.getAttribute("data-qr-url") || anchor.href;

        if (!deviceUuid || !chatUrl || !qrUrl) {
            return;
        }

        event.preventDefault();

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });

        $.ajax({
            type: "POST",
            url: resolveBaseUrl(anchor).replace(/\/$/, "") + "/check-session/" + encodeURIComponent(deviceUuid),
            dataType: "json",
            success: function (response) {
                if (response && response.connected === true) {
                    window.location.href = chatUrl;
                    return;
                }

                notifyAndRedirect(
                    (response && response.message) || "WhatsApp session is not ready. Please reconnect this device.",
                    qrUrl
                );
            },
            error: function () {
                notifyAndRedirect(
                    "WhatsApp session is not ready. Please reconnect this device.",
                    qrUrl
                );
            },
        });
    }

    $(document).on("click", ".js-wa-chat-entry", handleChatEntryClick);
})();
