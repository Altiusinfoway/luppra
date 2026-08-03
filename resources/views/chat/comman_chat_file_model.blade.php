@php
    $activeDevices = \App\Models\Device::where('user_id', Auth::id())
        ->where('status', 1)
        ->orderBy('id', 'desc')
        ->get();
    $fallbackDevice = \App\Models\Device::where('user_id', Auth::id())
        ->orderBy('id', 'desc')
        ->first();
    $devicesForModal = $activeDevices->isNotEmpty()
        ? $activeDevices
        : collect($fallbackDevice ? [$fallbackDevice] : []);
@endphp

<style>
    .wa-file-modal-shell {
        border: 1px solid rgba(255, 255, 255, 0.82);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 20px 48px rgba(15, 23, 42, 0.12);
    }

    .wa-file-modal-shell .modal-header {
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
        border-bottom: 1px solid #e2e8f0;
    }

    .wa-file-modal-shell .modal-body,
    .wa-file-modal-shell .modal-footer {
        background: #ffffff;
    }

    .wa-file-modal-shell .wa-form-intro {
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        background: #f8fafc;
        padding: 14px 16px;
        margin-bottom: 16px;
    }

    .wa-file-modal-shell .form-control {
        border: 1px solid #cbd5e1 !important;
    }

    .file-preview-box {
        width: 100%;
        height: 300px;
        border: 1px solid #ccc;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #f9f9f9;
    }

.preview-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;   /* 🔥 key line */
}
.wa-file-history {
    max-height: 280px;
    overflow-y: auto;
    background: #efeae2;
    border: 1px solid #d9dbdd;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 14px;
}

.wa-file-status,
.wa-file-empty {
    text-align: center;
    font-size: 12px;
    color: #667781;
    padding: 16px 10px;
}

.wa-file-row {
    display: flex;
    margin-bottom: 10px;
}

.wa-file-row.out {
    justify-content: flex-end;
}

.wa-file-bubble {
    max-width: 82%;
    background: #fff;
    border-radius: 10px;
    padding: 8px 10px 6px;
}

.wa-file-row.out .wa-file-bubble {
    background: #d9fdd3;
}

.wa-file-time {
    display: block;
    text-align: right;
    color: #667781;
    font-size: 10px;
    margin-top: 4px;
}

.wa-file-image,
.wa-file-video {
    max-width: 220px;
    width: 100%;
    border-radius: 8px;
    display: block;
}

.wa-file-audio {
    width: 220px;
    display: block;
}

.wa-file-doc {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 210px;
    max-width: 260px;
    background: rgba(255, 255, 255, 0.75);
    border-radius: 8px;
    padding: 8px 10px;
    color: #111b21;
    text-decoration: none;
}

.wa-file-doc-badge {
    min-width: 40px;
    height: 40px;
    border-radius: 8px;
    background: #ef4444;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
}

.wa-file-doc-name,
.wa-file-caption,
.wa-file-fallback {
    word-break: break-word;
    font-size: 12px;
}

.wa-file-caption {
    margin-top: 6px;
    color: #54656f;
}

.wa-file-media-loading {
    color: #667781;
    font-size: 12px;
    margin-bottom: 6px;
}
</style>

<div class="modal fade" id="whatsappFileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content wa-file-modal-shell">

            <!-- HEADER -->
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-whatsapp-line me-1"></i> Send WhatsApp Message
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body">
                <form method="POST"
                      action="{{ route('sent.customtext','text-with-media') }}"
                      enctype="multipart/form-data"
                      class="ajaxform_reset_form">

                    @csrf
                    <div class="wa-form-intro">
                        <strong class="d-block mb-1">Media message action</strong>
                        <span class="text-muted">Choose a device, confirm the receiver, review recent messages, and send media with a caption from one cleaner modal flow.</span>
                    </div>

                    <input type="hidden" id="customer_id_file" name="customer_id" class="form-control mb-3">

                    <div class="row g-3">

                        <!-- DEVICE -->
                        <div class="col-md-6 mt-1">
                            <label class="form-label">Select Device</label>
                            <select class="form-control" name="device" required>
                                @foreach($devicesForModal as $device)
                                    <option value="{{ $device->id }}" data-uuid="{{ $device->uuid }}" data-status="{{ $device->status }}">
                                        {{ $device->name }} (+{{ $device->phone }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- PHONE -->
                        <div class="col-md-6 mt-1">
                            <label class="form-label">Message To</label>
                            <input type="text"
                                   id="phone_file"
                                   class="form-control"
                                   name="phone"
                                   placeholder="Enter phone number with country code"
                                   required>
                        </div>

                        <div class="col-md-12 mt-1">
                            <div class="wa-file-history" data-history-box>
                                <div class="wa-file-empty">Open a contact to view recent chat history.</div>
                            </div>
                        </div>

                        <!-- FILE -->
                        {{-- <div class="col-md-12 mt-1">
                            <label class="form-label">Select File</label>
                            <input type="file"
                                   class="form-control"
                                   name="file"
                                   required>
                            <small class="text-muted">
                                Supported: jpg, jpeg, png, webp, pdf, docx, xlsx, csv, txt
                            </small>
                        </div> --}}

                        <input type="hidden" name="file" id="fileInput" >
                        <input type="hidden" name="attachment_name" id="attachmentNameInput">

                        <!-- MESSAGE -->
                        <div class="col-md-12 mt-1">
                            <label class="form-label">Message</label>

                            <div class="mb-2">
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                        onclick="insertTag('myTextarea1','*','*')">Bold</button>
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                        onclick="insertTag('myTextarea1','_','_')">Italic</button>
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                        onclick="insertTag('myTextarea1','```','```')">Mono</button>
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                        onclick="insertTag('myTextarea1','~','~')">Strike</button>
                                <button type="button" class="btn btn-outline-primary btn-sm emojipick">
                                    Emoji
                                </button>
                            </div>

                            <textarea id="myTextarea1"
                                      class="form-control mt-1"
                                      rows="4"
                                      name="message"
                                      maxlength="1000"
                                      required placeholder="Enter Message"></textarea>
                        </div>

                    </div>

                    <!-- FOOTER -->
                    <div class="modal-footer mt-3">
                        <button type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit"
                                class="btn btn-success">
                            <i class="ri-send-plane-fill me-1"></i> Send Message
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://woody180.github.io/vanilla-javascript-emoji-picker/vanillaEmojiPicker.js"></script>

<script src="{{ asset('public/build/assets/js/pages/user/template.js') }}"></script>

<script>
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-right",
    timeOut: "3000"
};
</script>
     <script>
        function normalizeWhatsappFileModalPhone(phoneNo) {
            const digits = String(phoneNo || '').replace(/\D/g, '');

            if (!digits) {
                return '';
            }

            if (digits.startsWith('91') && digits.length === 12) {
                return '+' + digits;
            }

            if (digits.length === 10) {
                return '+91' + digits;
            }

            if (digits.startsWith('0') && digits.length === 11) {
                return '+91' + digits.slice(1);
            }

            return '+' + digits;
        }
     </script>


       <script>

        new EmojiPicker({
            trigger: [
                {
                    selector: '.emojipick',
                    insertInto: ['.one'] // '.selector' can be used without array
                }
            ],
            closeButton: true,
            //specialButtons: green
        });


        function insertTag(textareaId, openTag, closeTag) {
            var textarea = document.getElementById(textareaId);
            var startPos = textarea.selectionStart;
            var endPos = textarea.selectionEnd;
            var selectedText = textarea.value.substring(startPos, endPos);
            var newText = openTag + selectedText + closeTag;
            textarea.value = textarea.value.substring(0, startPos) + newText + textarea.value.substring(endPos, textarea.value.length);
            textarea.focus();
            textarea.setSelectionRange(endPos + openTag.length + closeTag.length, endPos + openTag.length + closeTag.length);
        }
    </script>
    <script>
document.addEventListener("click", function (e) {
    const btn = e.target.closest(".open-whatsappFile-modal");
    if (!btn) return;

    const customerId = btn.dataset.customer_id || '';
    const phone = btn.dataset.phone || '';
    const file = btn.dataset.file || '';
    const attachmentName = btn.dataset.filename || '';
    const modalEl = document.getElementById('whatsappFileModal');

    $('#customer_id_file').val(customerId);
    $('#phone_file').val(normalizeWhatsappFileModalPhone(phone));

    $('#fileInput').val(file || '');
    $('#attachmentNameInput').val(attachmentName || '');

    new bootstrap.Modal(modalEl).show();
});
    </script>
    <script>
        function fileModalDigits(phoneNo) {
            return String(phoneNo || '').replace(/\D/g, '');
        }

        function fileModalEscape(value) {
            return String(value || '')
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function fileModalTime(timestamp) {
            if (!timestamp) {
                return '';
            }

            return new Date(timestamp * 1000).toLocaleString();
        }

        function fileModalCssEscape(value) {
            if (window.CSS && typeof window.CSS.escape === 'function') {
                return window.CSS.escape(String(value || ''));
            }

            return String(value || '').replace(/[^a-zA-Z0-9_\u00A0-\uFFFF-]/g, '\\$&');
        }

        function fileModalShouldRenderCaption(message) {
            if (!message || !message.media) {
                return false;
            }

            const text = String(message.text || '').trim();
            const placeholders = new Set(['[Image]', '[Video]', '[Audio]', '[Document]', '[Unsupported message]']);
            return text !== '' && !placeholders.has(text);
        }

        function fileModalBase64ToBlobUrl(base64, mimeType) {
            if (!base64) {
                return '';
            }

            try {
                const binary = atob(base64);
                const length = binary.length;
                const bytes = new Uint8Array(length);

                for (let index = 0; index < length; index += 1) {
                    bytes[index] = binary.charCodeAt(index);
                }

                const blob = new Blob([bytes], { type: mimeType || 'application/octet-stream' });
                return URL.createObjectURL(blob);
            } catch (error) {
                return '';
            }
        }

        function renderWhatsappFileMediaPlaceholder(message) {
            if (!message || !message.media) {
                return `<div>${fileModalEscape(message && message.text ? message.text : '')}</div>`;
            }

            const mediaKind = fileModalEscape(message.media.kind || 'media');
            const fallbackName = fileModalEscape(message.media.file_name || `${message.media.kind || 'file'}`);

            return `<div class="wa-file-media-loading">Loading ${mediaKind}...</div>
                <div class="wa-file-doc">
                    <span class="wa-file-doc-badge">${mediaKind.slice(0, 3).toUpperCase()}</span>
                    <span class="wa-file-doc-name">${fallbackName}</span>
                </div>`;
        }

        function renderWhatsappFileHistory(messages) {
            const modalEl = document.getElementById('whatsappFileModal');
            const historyBox = modalEl.querySelector('[data-history-box]');
            if (!historyBox) {
                return;
            }

            if (!Array.isArray(messages) || messages.length === 0) {
                historyBox.innerHTML = '<div class="wa-file-empty">No recent messages found for this contact.</div>';
                return;
            }

            const sorted = [...messages].sort((a, b) => (a.timestamp || 0) - (b.timestamp || 0));

            historyBox.innerHTML = sorted.map(function(message) {
                const rowClass = message.from_me ? 'out' : 'in';
                const caption = String(message.text || '').trim();
                const showCaption = fileModalShouldRenderCaption(message);

                return `<div class="wa-file-row ${rowClass}">
                    <div class="wa-file-bubble">
                        <div class="wa-file-media" data-message-id="${fileModalEscape(message.id)}" data-remote-jid="${fileModalEscape(message.remote_jid || '')}">
                            ${renderWhatsappFileMediaPlaceholder(message)}
                        </div>
                        ${message.media && showCaption ? `<div class="wa-file-caption">${fileModalEscape(caption)}</div>` : ''}
                        <small class="wa-file-time">${fileModalEscape(fileModalTime(message.timestamp || 0))}</small>
                    </div>
                </div>`;
            }).join('');

            sorted.filter(message => message && message.media && message.id && message.remote_jid).forEach(function(message) {
                hydrateWhatsappFileMedia(message);
            });

            historyBox.scrollTop = historyBox.scrollHeight;
        }

        function hydrateWhatsappFileMedia(message) {
            const modalEl = document.getElementById('whatsappFileModal');
            const deviceSelect = modalEl.querySelector('select[name="device"]');
            const activeOption = deviceSelect ? deviceSelect.options[deviceSelect.selectedIndex] : null;
            const deviceUuid = activeOption ? activeOption.dataset.uuid : '';
            const mediaKind = (message.media && message.media.kind) || 'file';
            const fallbackName = fileModalEscape((message.media && message.media.file_name) || `${mediaKind}`);
            if (!deviceUuid) {
                return;
            }

            const target = modalEl.querySelector(`.wa-file-media[data-message-id="${fileModalCssEscape(String(message.id || ''))}"][data-remote-jid="${fileModalCssEscape(String(message.remote_jid || ''))}"]`);
            if (!target) {
                return;
            }

            $.ajax({
                type: 'POST',
                url: `{{ url('/chats/download-media') }}?id=${encodeURIComponent(deviceUuid)}`,
                dataType: 'json',
                data: {
                    remoteJid: String(message.remote_jid || ''),
                    messageId: String(message.id || '')
                },
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                success: function(payload) {
                    if (!payload || !payload.success || !payload.data) {
                        target.innerHTML = `<div class="wa-file-fallback">${fallbackName}</div>`;
                        return;
                    }

                    const mimeType = String(payload.data.mimetype || (message.media && message.media.mime_type) || '');
                    const base64 = String(payload.data.base64 || '');
                    const fileName = fileModalEscape(payload.data.fileName || (message.media && message.media.file_name) || `${mediaKind}`);
                    const dataUrl = base64 ? `data:${mimeType || 'application/octet-stream'};base64,${base64}` : '';
                    const blobUrl = fileModalBase64ToBlobUrl(base64, mimeType);

                    if (!dataUrl) {
                        target.innerHTML = `<div class="wa-file-fallback">${fileName}</div>`;
                        return;
                    }

                    if (mediaKind === 'image') {
                        target.innerHTML = `<a href="${dataUrl}" target="_blank" rel="noopener noreferrer"><img src="${dataUrl}" alt="${fileName}" class="wa-file-image"></a>`;
                        return;
                    }

                    if (mediaKind === 'video') {
                        target.innerHTML = `<video controls class="wa-file-video"><source src="${dataUrl}" type="${fileModalEscape(mimeType)}"></video>`;
                        return;
                    }

                    if (mediaKind === 'audio') {
                        target.innerHTML = `<audio controls class="wa-file-audio"><source src="${dataUrl}" type="${fileModalEscape(mimeType)}"></audio>`;
                        return;
                    }

                    target.innerHTML = `<a href="${blobUrl || dataUrl}" download="${fileName}" target="_blank" rel="noopener noreferrer" class="wa-file-doc">
                        <span class="wa-file-doc-badge">PDF</span>
                        <span class="wa-file-doc-name">${fileName}</span>
                    </a>`;
                },
                error: function() {
                    target.innerHTML = `<div class="wa-file-fallback">${fallbackName}</div>`;
                }
            });
        }

        function loadWhatsappFileHistory() {
            const modalEl = document.getElementById('whatsappFileModal');
            const historyBox = modalEl.querySelector('[data-history-box]');
            const deviceSelect = modalEl.querySelector('select[name="device"]');
            const phoneInput = modalEl.querySelector('input[name="phone"]');
            if (!historyBox || !deviceSelect || !phoneInput) {
                return;
            }

            const activeOption = deviceSelect.options[deviceSelect.selectedIndex];
            const deviceUuid = activeOption ? activeOption.dataset.uuid : '';
            const isActive = activeOption ? String(activeOption.dataset.status || '') === '1' : false;
            const digits = fileModalDigits(phoneInput.value);

            if (!digits) {
                historyBox.innerHTML = '<div class="wa-file-empty">Enter a receiver number to load chat history.</div>';
                return;
            }

            if (!deviceUuid || !isActive) {
                historyBox.innerHTML = '<div class="wa-file-empty">Chat history is available only with an active WhatsApp device.</div>';
                return;
            }

            historyBox.innerHTML = '<div class="wa-file-status">Loading chat history...</div>';
            const requestId = String(Date.now()) + Math.random().toString(16).slice(2);
            modalEl.dataset.historyRequestId = requestId;

            fetch(`{{ url('/get-chat-messages') }}/${encodeURIComponent(deviceUuid)}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: new URLSearchParams({
                    number: digits,
                    limit: '25'
                })
            })
            .then(function(response) { return response.json(); })
            .then(function(payload) {
                if (modalEl.dataset.historyRequestId !== requestId) {
                    return;
                }
                renderWhatsappFileHistory(payload.messages || []);
            })
            .catch(function() {
                if (modalEl.dataset.historyRequestId !== requestId) {
                    return;
                }
                historyBox.innerHTML = '<div class="wa-file-empty">Unable to load chat history right now.</div>';
            });
        }

        document.getElementById('whatsappFileModal').querySelector('select[name="device"]')?.addEventListener('change', loadWhatsappFileHistory);
        document.getElementById('whatsappFileModal').querySelector('input[name="phone"]')?.addEventListener('change', loadWhatsappFileHistory);
        document.getElementById('whatsappFileModal').addEventListener('shown.bs.modal', function () {
            setTimeout(loadWhatsappFileHistory, 150);
        });
        document.getElementById('whatsappFileModal').addEventListener('hidden.bs.modal', function () {
            const form = this.querySelector('form.ajaxform_reset_form');
            const historyBox = this.querySelector('[data-history-box]');
            this.dataset.historyRequestId = '';
            if (form) {
                form.reset();
            }
            $('#customer_id_file').val('');
            $('#phone_file').val('');
            $('#fileInput').val('');
            $('#attachmentNameInput').val('');
            if (historyBox) {
                historyBox.innerHTML = '<div class="wa-file-empty">Open a contact to view recent chat history.</div>';
            }
        });
    </script>
    <script>
        $(document).ready(function() {
    $(document).on('submit', '#whatsappFileModal .ajaxform_reset_form', function(e) {
        e.preventDefault();

        let form = this;
        let formData = new FormData(form);

        $.ajax({
            url: form.action,
            method: form.method,
            data: formData,
            processData: false,
            contentType: false,

            success: function(res) {
                $(form).trigger('ajaxform:success', [res]);
                toastr.success(res.message ?? 'Message sent successfully');

                let modalEl = document.getElementById('whatsappFileModal');
                let modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.hide();
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
            },

            error: function(xhr) {
                let msg = 'Something went wrong';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    msg = xhr.responseJSON.error;
                }
                toastr.error(msg);

            }
        });
    });
});
    </script>
