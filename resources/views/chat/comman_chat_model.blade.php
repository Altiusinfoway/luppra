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
     .wa-modal-shell {
         border: 1px solid rgba(255, 255, 255, 0.82);
         border-radius: 24px;
         overflow: hidden;
         box-shadow: 0 20px 48px rgba(15, 23, 42, 0.12);
     }

     .wa-modal-shell .modal-header {
         background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
         border-bottom: 1px solid #e2e8f0;
     }

     .wa-modal-shell .modal-body {
         background: #ffffff;
     }

     .wa-modal-shell .wa-form-intro {
         border: 1px solid #e2e8f0;
         border-radius: 18px;
         background: #f8fafc;
         padding: 14px 16px;
         margin-bottom: 16px;
     }

     .wa-mini-history {
         max-height: 280px;
         overflow-y: auto;
         background: #efeae2;
         border: 1px solid #d9dbdd;
         border-radius: 10px;
         padding: 12px;
         margin-bottom: 14px;
     }

     .wa-mini-empty,
     .wa-mini-status {
         text-align: center;
         font-size: 12px;
         color: #667781;
         padding: 16px 10px;
     }

     .wa-mini-row {
         display: flex;
         margin-bottom: 10px;
     }

     .wa-mini-row.out {
         justify-content: flex-end;
     }

     .wa-mini-bubble {
         max-width: 82%;
         background: #fff;
         border-radius: 10px;
         padding: 8px 10px 6px;
         box-shadow: 0 1px 0 rgba(11, 20, 26, 0.06);
     }

     .wa-mini-row.out .wa-mini-bubble {
         background: #d9fdd3;
     }

     .wa-mini-time {
         display: block;
         text-align: right;
         color: #667781;
         font-size: 10px;
         margin-top: 4px;
     }

     .wa-mini-image,
     .wa-mini-video {
         max-width: 220px;
         width: 100%;
         border-radius: 8px;
         display: block;
     }

     .wa-mini-audio {
         width: 220px;
         display: block;
     }

     .wa-mini-doc {
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

     .wa-mini-doc-badge {
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

     .wa-mini-doc-name,
     .wa-mini-caption,
     .wa-mini-fallback {
         word-break: break-word;
         font-size: 12px;
     }

     .wa-mini-caption {
         margin-top: 6px;
         color: #54656f;
     }

     .wa-mini-media-loading {
         color: #667781;
         font-size: 12px;
         margin-bottom: 6px;
     }
 </style>
 <div class="modal fade" id="whatsappModal" tabindex="-1" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content wa-modal-shell">

             <div class="modal-header">
                 <h5 class="modal-title">Send WhatsApp Message</h5>
                 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
             </div>

             <div class="modal-body">
                 <input type="hidden" id="customer_id">
                 {{-- <input type="text" id="phone_no" class="form-control mb-2" readonly> --}}


                 <!-- ----------new -->
                 <form method="POST" action="{{ route('sent.customtext', 'plain-text') }}" class="ajaxform_reset_form" data-preserve-fields="phone">
                     @csrf
                     <div class="wa-form-intro">
                         <strong class="d-block mb-1">Chat action</strong>
                         <span class="text-muted">Choose a device, confirm the phone number, review recent messages, and send a new WhatsApp reply from one cleaner modal flow.</span>
                     </div>
                     <div class="row">
                         <div class="col-sm-6">

                             <label>{{ __('Select Device') }}</label>
                             @if($devicesForModal->isNotEmpty())
                             <select class="form-control" name="device" required="" data-toggle="select">
                                     @foreach($devicesForModal as $device)
                                     <option value="{{ $device->id }}" data-uuid="{{ $device->uuid }}" data-status="{{ $device->status }}">{{ $device->name }} (+{{ $device->phone }})
                                     </option>
                                     @endforeach
                             </select>
                             @else
                             <p class="text-muted mb-0">{{ __('No WhatsApp device available.') }}</p>
                             @endif

                         </div>
                         <div class="col-sm-6">
                             <div class="form-group">
                                 <label>{{ __('Message To (Receiver)') }}</label>
                                 <input id="phone" type="text" class="form-control" name="phone"
                                     placeholder="{{ __('Enter phone number with country code') }}"
                                     required="" autofocus="" minlength="10" maxlength="15" />
                             </div>
                         </div>
                         <div class="col-sm-12 mt-3">
                             <div class="wa-mini-history" data-history-box>
                                 <div class="wa-mini-empty">Open a contact to view recent chat history.</div>
                             </div>
                         </div>
                         <div class="col-sm-12 mt-2">
                             <div class="form-group">
                                 <label>{{ __('Message') }}</label>
                                 <br>
                                 <button type="button" class="btn btn-outline-primary btn-sm"
                                     onclick="insertTag('myTextarea', '*', '*')">Bold</button>
                                 <button type="button" class="btn btn-outline-primary btn-sm"
                                     onclick="insertTag('myTextarea', '_', '_')">Italic</button>
                                 <button type="button" class="btn btn-outline-primary btn-sm"
                                     onclick="insertTag('myTextarea', '```', '```')">Monospace</button>
                                 <button type="button" class="btn btn-outline-primary btn-sm"
                                     onclick="insertTag('myTextarea', '~', '~')">Strike</button>
                                 <button type="button" class="emojipick btn btn-outline-primary btn-sm">Emoji</button>
                                 <textarea id="myTextarea" class="form-control h-200 one mt-2" name="message" required="" cols="10"
                                     rows="1"></textarea>
                             </div>
                         </div>



                         <div class="col-sm-12 mt-2">
                             <div class="row">

                                 <div class="col-sm-4 mb-6">
                                     <button type="submit"
                                         class="btn btn-outline-primary submit-button float-right">{{ __('Send Message') }}</button>
                                 </div>
                             </div>
                         </div>
                     </div>


                 </form>

             </div>
         </div>
     </div>


<script src="{{ asset('public/build/assets/js/pages/user/select2.full.min.js') }}"></script>
<script src="{{ asset('public/build/assets/js/pages/user/select2.min.js') }}"></script>

<script src="https://woody180.github.io/vanilla-javascript-emoji-picker/vanillaEmojiPicker.js"></script>
<script src="https://cdn.jsdelivr.net/npm/uikit@3.9.4/dist/js/uikit.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/uikit@3.9.4/dist/js/uikit-icons.min.js"></script>

<script type="text/javascript" src="{{ asset('public/build/assets/js/pages/user/template.js') }}"></script>

     <script>
         function normalizeWhatsappModalPhone(phoneNo) {
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

         function modalWhatsappDigits(phoneNo) {
             return String(phoneNo || '').replace(/\D/g, '');
         }

         function modalWhatsappEscape(value) {
             return String(value || '')
                 .replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
         }

         function modalWhatsappTime(timestamp) {
             if (!timestamp) {
                 return '';
             }

             return new Date(timestamp * 1000).toLocaleString();
         }

         function modalWhatsappCssEscape(value) {
             if (window.CSS && typeof window.CSS.escape === 'function') {
                 return window.CSS.escape(String(value || ''));
             }

             return String(value || '').replace(/[^a-zA-Z0-9_\u00A0-\uFFFF-]/g, '\\$&');
         }

         function modalWhatsappShouldRenderCaption(message) {
             if (!message || !message.media) {
                 return false;
             }

             const text = String(message.text || '').trim();
             const placeholders = new Set(['[Image]', '[Video]', '[Audio]', '[Document]', '[Unsupported message]']);
             return text !== '' && !placeholders.has(text);
         }

         function modalWhatsappBase64ToBlobUrl(base64, mimeType) {
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

         function modalWhatsappRenderMediaPlaceholder(message) {
             if (!message || !message.media) {
                 return `<div>${modalWhatsappEscape(message && message.text ? message.text : '')}</div>`;
             }

             const mediaKind = modalWhatsappEscape(message.media.kind || 'media');
             const fallbackName = modalWhatsappEscape(message.media.file_name || `${message.media.kind || 'file'}`);

             return `<div class="wa-mini-media-loading">Loading ${mediaKind}...</div>
                 <div class="wa-mini-doc">
                     <span class="wa-mini-doc-badge">${mediaKind.slice(0, 3).toUpperCase()}</span>
                     <span class="wa-mini-doc-name">${fallbackName}</span>
                 </div>`;
         }

         function modalWhatsappRenderHistory(modalEl, messages) {
             const historyBox = modalEl.querySelector('[data-history-box]');
             if (!historyBox) {
                 return;
             }

             if (!Array.isArray(messages) || messages.length === 0) {
                 historyBox.innerHTML = '<div class="wa-mini-empty">No recent messages found for this contact.</div>';
                 return;
             }

             const sorted = [...messages].sort((a, b) => (a.timestamp || 0) - (b.timestamp || 0));

             historyBox.innerHTML = sorted.map(function(message) {
                 const rowClass = message.from_me ? 'out' : 'in';
                 const caption = String(message.text || '').trim();
                 const showCaption = modalWhatsappShouldRenderCaption(message);

                 return `<div class="wa-mini-row ${rowClass}">
                     <div class="wa-mini-bubble">
                         <div class="wa-mini-media" data-message-id="${modalWhatsappEscape(message.id)}" data-remote-jid="${modalWhatsappEscape(message.remote_jid || '')}" data-kind="${modalWhatsappEscape((message.media && message.media.kind) || '')}">
                             ${modalWhatsappRenderMediaPlaceholder(message)}
                         </div>
                         ${message.media && showCaption ? `<div class="wa-mini-caption">${modalWhatsappEscape(caption)}</div>` : ''}
                         <small class="wa-mini-time">${modalWhatsappEscape(modalWhatsappTime(message.timestamp || 0))}</small>
                     </div>
                 </div>`;
             }).join('');

             const historyMedia = sorted.filter(message => message && message.media && message.id && message.remote_jid);
             historyMedia.forEach(function(message) {
                 modalWhatsappHydrateMedia(modalEl, message);
             });

             historyBox.scrollTop = historyBox.scrollHeight;
         }

         function modalWhatsappHydrateMedia(modalEl, message) {
             const deviceSelect = modalEl.querySelector('select[name="device"]');
             const activeOption = deviceSelect ? deviceSelect.options[deviceSelect.selectedIndex] : null;
             const deviceUuid = activeOption ? activeOption.dataset.uuid : '';
             const mediaKind = (message.media && message.media.kind) || 'file';
             const fallbackName = modalWhatsappEscape((message.media && message.media.file_name) || `${mediaKind}`);
             if (!deviceUuid) {
                 return;
             }

             const target = modalEl.querySelector(`.wa-mini-media[data-message-id="${modalWhatsappCssEscape(String(message.id || ''))}"][data-remote-jid="${modalWhatsappCssEscape(String(message.remote_jid || ''))}"]`);
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
                         target.innerHTML = `<div class="wa-mini-fallback">${fallbackName}</div>`;
                         return;
                     }

                     const mimeType = String(payload.data.mimetype || (message.media && message.media.mime_type) || '');
                     const base64 = String(payload.data.base64 || '');
                     const fileName = modalWhatsappEscape(payload.data.fileName || (message.media && message.media.file_name) || `${mediaKind}`);
                     const dataUrl = base64 ? `data:${mimeType || 'application/octet-stream'};base64,${base64}` : '';
                     const blobUrl = modalWhatsappBase64ToBlobUrl(base64, mimeType);

                     if (!dataUrl) {
                         target.innerHTML = `<div class="wa-mini-fallback">${fileName}</div>`;
                         return;
                     }

                     if (mediaKind === 'image') {
                         target.innerHTML = `<a href="${dataUrl}" target="_blank" rel="noopener noreferrer"><img src="${dataUrl}" alt="${fileName}" class="wa-mini-image"></a>`;
                         return;
                     }

                     if (mediaKind === 'video') {
                         target.innerHTML = `<video controls class="wa-mini-video"><source src="${dataUrl}" type="${modalWhatsappEscape(mimeType)}"></video>`;
                         return;
                     }

                     if (mediaKind === 'audio') {
                         target.innerHTML = `<audio controls class="wa-mini-audio"><source src="${dataUrl}" type="${modalWhatsappEscape(mimeType)}"></audio>`;
                         return;
                     }

                     target.innerHTML = `<a href="${blobUrl || dataUrl}" download="${fileName}" target="_blank" rel="noopener noreferrer" class="wa-mini-doc">
                         <span class="wa-mini-doc-badge">PDF</span>
                         <span class="wa-mini-doc-name">${fileName}</span>
                     </a>`;
                 },
                 error: function() {
                     target.innerHTML = `<div class="wa-mini-fallback">${fallbackName}</div>`;
                 }
             });
         }

         function modalWhatsappLoadHistory(modalEl) {
             const historyBox = modalEl.querySelector('[data-history-box]');
             const deviceSelect = modalEl.querySelector('select[name="device"]');
             const phoneInput = modalEl.querySelector('input[name="phone"]');
             if (!historyBox || !deviceSelect || !phoneInput) {
                 return;
             }

             const activeOption = deviceSelect.options[deviceSelect.selectedIndex];
             const deviceUuid = activeOption ? activeOption.dataset.uuid : '';
             const isActive = activeOption ? String(activeOption.dataset.status || '') === '1' : false;
             const digits = modalWhatsappDigits(phoneInput.value);

             if (!digits) {
                 historyBox.innerHTML = '<div class="wa-mini-empty">Enter a receiver number to load chat history.</div>';
                 return;
             }

             if (!deviceUuid || !isActive) {
                 historyBox.innerHTML = '<div class="wa-mini-empty">Chat history is available only with an active WhatsApp device.</div>';
                 return;
             }

             historyBox.innerHTML = '<div class="wa-mini-status">Loading chat history...</div>';
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
                 modalWhatsappRenderHistory(modalEl, payload.messages || []);
             })
             .catch(function() {
                 if (modalEl.dataset.historyRequestId !== requestId) {
                     return;
                 }
                 historyBox.innerHTML = '<div class="wa-mini-empty">Unable to load chat history right now.</div>';
             });
         }

         document.addEventListener("click", function(e) {
             let btn = e.target.closest(".open-whatsapp-modal");

             if (btn) {
                 let cust_id = btn.dataset.customer_id;
                 let phone_no = btn.dataset.phone;

                 // Set values inside modal
                 document.getElementById("customer_id").value = cust_id;
                 document.getElementById("phone").value = normalizeWhatsappModalPhone(phone_no);

                 let modal = new bootstrap.Modal(document.getElementById("whatsappModal"));
                 modal.show();
                 setTimeout(function () {
                     modalWhatsappLoadHistory(document.getElementById("whatsappModal"));
                 }, 150);
             }
         });

         document.getElementById("whatsappModal").querySelector('select[name="device"]')?.addEventListener('change', function () {
             modalWhatsappLoadHistory(document.getElementById("whatsappModal"));
         });

         document.getElementById("whatsappModal").querySelector('input[name="phone"]')?.addEventListener('change', function () {
             modalWhatsappLoadHistory(document.getElementById("whatsappModal"));
         });

         document.getElementById("whatsappModal").querySelector('form.ajaxform_reset_form')?.addEventListener('ajaxform:success', function () {
             setTimeout(function () {
                 modalWhatsappLoadHistory(document.getElementById("whatsappModal"));
             }, 300);
         });

         document.getElementById("whatsappModal").addEventListener("hidden.bs.modal", function () {
             const form = this.querySelector("form.ajaxform_reset_form");
             if (!form) {
                 return;
             }

             this.dataset.historyRequestId = '';
             form.reset();
             document.getElementById("phone").value = "";
             document.getElementById("customer_id").value = "";
             const historyBox = this.querySelector('[data-history-box]');
             if (historyBox) {
                 historyBox.innerHTML = '<div class="wa-mini-empty">Open a contact to view recent chat history.</div>';
             }
         });
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
