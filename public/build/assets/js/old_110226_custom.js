/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 *
 */

"use strict";
function show_toastr(type, message) {
    var f = document.getElementById("liveToast");
    var a = new bootstrap.Toast(f).show();
    if (type == "success") {
        $("#liveToast").removeClass("bg-danger");
        $("#liveToast").addClass("bg-success");
    } else {
        $("#liveToast").removeClass("bg-success");
        $("#liveToast").addClass("bg-danger");
    }
    $("#liveToast .toast-body").html(message);
}

$(document).on(
    "click",
    'a[data-ajax-popup="true"], button[data-ajax-popup="true"], div[data-ajax-popup="true"]',
    function () {
        var data = {};
        var title1 = $(this).data("title");

        var title2 = $(this).data("bs-original-title");
        var title3 = $(this).data("original-title");
        var title = title1 != undefined ? title1 : title2;
        var title = title != undefined ? title : title3;

        $(".modal-dialog").removeClass("modal-xl");
        var size = $(this).data("size") == "" ? "md" : $(this).data("size");

        var url = $(this).data("url");
        $("#commonModal .modal-title").html(title);
        $("#commonModal .modal-dialog").addClass("modal-" + size);

        $.ajax({
            url: url,
            data: data,
            success: function (data) {
                $("#commonModal .common-modal-body").html(data);
                $("#commonModal").modal("show");

                common_bind(document.getElementById("commonModal"));
                validation();
            },
            error: function (data) {
                data = data.responseJSON;
                show_toastr("Error", data.error, "error");
            },
        });
    },
);

function common_bind(modal) {
    initChoicesInModal(modal);
    select2();
    flatpickr_init(modal);
    initProductRowEvents();
    bindVariationToggle(modal);
}

function select2() {
    if ($(".select2").length > 0) {
        $($(".select2")).each(function (index, element) {
            var id = $(element).attr("id");
            var multipleCancelButton = new Choices("#" + id, {
                removeItemButton: true,
            });
        });
    }
}

let flatpickrInstances = [];

function flatpickr_init(modal) {
    const datepicker = modal.querySelectorAll(".datepicker-range");

    if (datepicker.length === 0) return;

    flatpickrInstances.forEach((instance) => instance.destroy());
    flatpickrInstances = [];

    datepicker.forEach((select) => {
        const instance = new flatpickr(select, {});

        flatpickrInstances.push(instance);
    });
}

let choicesInstances = [];

function initChoicesInModal(modal) {
    // Find all selects that need Choices.js inside the modal
    const selects = modal.querySelectorAll("select.choices-select");

    // If none found, just return
    if (selects.length === 0) return;

    console.log(selects.length);

    // Destroy previous Choices instances
    choicesInstances.forEach((instance) => instance.destroy());
    choicesInstances = [];

    // Initialize Choices.js for each found select
    selects.forEach((select) => {
        const instance = new Choices(select, {
            removeItemButton: true,
            placeholder: true,
            placeholderValue: "Select option",
        });
        choicesInstances.push(instance);
    });
}

function validation() {
    var forms = document.querySelectorAll(".needs-validation");

    Array.prototype.forEach.call(forms, function (form) {
        form.addEventListener(
            "submit",
            function (event) {
                var submitButton = form.querySelector(
                    'button[type="submit"], input[type="submit"]',
                );

                if (submitButton) {
                    submitButton.disabled = true;
                }
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                    if (submitButton) {
                        submitButton.disabled = false;
                    }
                }

                form.classList.add("was-validated");
            },
            false,
        );
    });
}

function bindVariationToggle(modalElement) {
    const varaitions = modalElement.querySelectorAll(".has-varaitions");

    // If none found, just return
    if (varaitions.length === 0) return;

    modalElement
        .querySelectorAll(".has-varaitions")
        .forEach(function (checkbox) {
            checkbox.addEventListener("change", function () {
                const card = checkbox.closest(".card");
                const preview = card.querySelector(".live-preview");
                if (checkbox.checked) {
                    preview.classList.remove("d-none");
                } else {
                    preview.classList.add("d-none");
                }
            });

            // Initialize visibility on load
            checkbox.dispatchEvent(new Event("change"));
        });

    feather.replace(); // if you're using Feather icons
}

function postAjax(url, data, cb) {
    var token = $('meta[name="csrf-token"]').attr("content");
    var jdata = { _token: token };

    for (var k in data) {
        jdata[k] = data[k];
    }

    $.ajax({
        type: "POST",
        url: url,
        data: jdata,
        success: function (data) {
            if (typeof data === "object") {
                cb(data);
            } else {
                cb(data);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $.each(errors, function (key, value) {
                    if (document.getElementById("error-" + key)) {
                        $("#error-" + key).text(value[0]);
                    } else {
                        show_toastr(
                            "error",
                            "Please fill all required fields.",
                        );
                    }
                });
            } else {
                show_toastr("error", "Something went wrong.");
            }
        },
    });
}

function getAjax(url, cb) {
    var token = $('meta[name="csrf-token"]').attr("content");
    var jdata = { _token: token };

    //return new Promise((resolve, reject) => {
    $.ajax({
        type: "GET",
        url: url,
        data: jdata,
        success: function (data) {
            if (typeof cb === "function") {
                cb(data);
            } else {
                //resolve(data);
                return data;
            }
        },
        error: function (xhr, status, error) {
            //reject(error);
        },
    });
    //});
}

const DeleteCallbacks = {
    afterDelete: function (response, $triggerElement) {
        show_toastr("success", response.success);

        $triggerElement.closest(".main-row").remove();
    },
};

let deleteRequestOptions = {};

$(document).on(
    "click",
    'a[data-delete-popup="true"], button[data-delete-popup="true"], div[data-delete-popup="true"]',
    function () {
        var data = {};
        const $this = $(this);
        var title1 = $this.data("title");

        var title2 = $this.data("bs-original-title");
        var title3 = $this.data("original-title");
        var title = title1 != undefined ? title1 : title2;
        var title = title != undefined ? title : title3;

        var description = $this.data("bs-original-description");

        $(".modal-dialog").removeClass("modal-xl");
        var size = $this.data("size") == "" ? "md" : $this.data("size");

        // var url = $this.data('url');
        $("#deleteRecordModal .del-modal-title").html(title);
        $("#deleteRecordModal .del-modal-description").html(description);
        $("#deleteRecordModal .modal-dialog").addClass("modal-" + size);

        const cbString = $this.data("cb");
        const functionName = cbString?.replace("()", "");

        $("#delete-record").off("click");

        if (functionName && typeof window[functionName] === "function") {
            document
                .getElementById("delete-record")
                .addEventListener("click", window[functionName]);
        }

        deleteRequestOptions = {
            url: $this.data("url"),
            method: $this.data("method") || "POST",
            cb: $this.data("cb"),
            additionalData: $this.data("params") || {}, // optional
            $triggerElement: $this,
        };

        $("#delete-record").on("click", function () {
            var token = $('meta[name="csrf-token"]').attr("content");
            var jdata = { _token: token };
            const data = deleteRequestOptions.additionalData;

            for (var k in data) {
                jdata[k] = data[k];
            }

            $.ajax({
                url: deleteRequestOptions.url,
                method: deleteRequestOptions.method,
                data: jdata,
                success: function (response) {
                    $("#deleteRecordModal").modal("hide");

                    if (
                        deleteRequestOptions.cb &&
                        typeof DeleteCallbacks[deleteRequestOptions.cb] ===
                            "function"
                    ) {
                        DeleteCallbacks[deleteRequestOptions.cb](
                            response,
                            deleteRequestOptions.$triggerElement,
                        );
                    }
                },
                error: function (xhr) {
                    show_toastr("error", "Delete failed. Please try again.");
                },
            });
        });

        $("#deleteRecordModal").modal("show");
    },
);

// On Lead view -> add more product
function initProductRowEvents() {
    const productRows = document.getElementById("product-rows");

    if (!productRows) return;

    productRows.addEventListener("click", function (e) {
        if (e.target.closest(".add-more-product")) {
            const html = getAjax(productRows.dataset.url, function (res) {
                const tempDiv = document.createElement("div");
                tempDiv.innerHTML = res;

                const newRow = tempDiv.firstElementChild;

                if (newRow) {
                    productRows.appendChild(newRow);
                }
            });
        }

        if (e.target.closest(".remove-row")) {
            e.target.closest(".product-row").remove();
        }
    });
}

function enableConfirmationOnChange(selectId, message, callback) {
    var select = document.getElementById(selectId);
    select.dataset.previousValue = select.value;

    // Track which select triggered the modal
    var currentSelect = null;
    var newValue = null;
    var modalMessage = message;
    var callbackFn = callback;

    // Change event on select
    select.addEventListener("change", function () {
        currentSelect = select;
        newValue = currentSelect.value;

        // Set modal content
        $("#confirmationModal .confirm-modal-title").html("Are You Sure?");
        $("#confirmationModal .confirm-modal-description").html(modalMessage);
        $("#confirmationModal .modal-dialog").addClass("modal-md");

        $("#confirmationModal").modal("show");
    });

    // Only bind click events ONCE
    if (!window.confirmationModalEventsBound) {
        $("#confirm-yes").on("click", function () {
            if (currentSelect) {
                currentSelect.dataset.previousValue = newValue;
                if (typeof callbackFn === "function") {
                    callbackFn(newValue);
                }
            }
            $("#confirmationModal").modal("hide");
        });

        $("#confirmation-close").on("click", function () {
            if (currentSelect) {
                currentSelect.value = currentSelect.dataset.previousValue;
            }
            $("#confirmationModal").modal("hide");
        });

        window.confirmationModalEventsBound = true;
    }
}

function enableConfirmationOnClick(className, message, callback) {
    var elements = document.querySelectorAll(`.${className}`);

    if (!elements || elements.length === 0) {
        return;
    }

    var currentElement = null;
    var url = null;
    var data = null;
    var modalMessage = message;
    var callbackFn = callback;

    // Add click event to all elements with the class
    elements.forEach(function (el) {
        el.addEventListener("click", function () {
            const $this = $(this);

            currentElement = this;
            // method = $this.data("method");
            url = $this.data("url");
            data = $this.data("data");

            // Set modal content
            $("#confirmationModal .confirm-modal-title").html("Are You Sure?");
            $("#confirmationModal .confirm-modal-description").html(
                modalMessage,
            );
            $("#confirmationModal .modal-dialog").addClass("modal-md");

            $("#confirmationModal").modal("show");
        });
    });

    // Only bind modal buttons once
    if (!window.confirmationModalClickBound) {
        $("#confirm-yes").on("click", function () {
            if (currentElement && typeof callbackFn === "function") {
                callbackFn(url, data);
            }
            $("#confirmationModal").modal("hide");
        });

        $("#confirmation-close").on("click", function () {
            $("#confirmationModal").modal("hide");
        });

        window.confirmationModalClickBound = true;
    }
}

function enableConfirmationOn(eventName, className, message, callback) {
    let currentElement = null;
    let url = null;
    let data = null;
    let previousValue = null;

    $(document).on("focus", "." + className, function () {
        previousValue = this.value;
    });

    $(document).on(eventName, "." + className, function () {
        const $this = $(this);
        currentElement = this;

        url = $this.data("url");

        if ($this.val() !== undefined) {
            url = url.replace("#sticky", $this.val());
        }

        data = $this.data("data");

        $("#confirmationModal .confirm-modal-title").text("Are You Sure?");
        $("#confirmationModal .confirm-modal-description").text(message);
        $("#confirmationModal .modal-dialog").addClass("modal-md");

        $("#confirmationModal").modal("show");
    });

    $("#confirm-yes")
        .off("click")
        .on("click", function () {
            $("#confirmationModal").modal("hide");
            document.activeElement.blur();

            if (currentElement && typeof callback === "function") {
                callback(url, data);
            }
        });

    $("#confirmation-close")
        .off("click")
        .on("click", function () {
            $("#confirmationModal").modal("hide");

            if (currentElement) {
                currentElement.value = previousValue;
            }
        });
}

function doConfirmation($this, message, callback) {
    // Set modal content
    $("#confirmationModal .confirm-modal-title").html("Are You Sure?");
    $("#confirmationModal .confirm-modal-description").html(message);
    $("#confirmationModal .modal-dialog").addClass("modal-md");

    $("#confirmationModal").modal("show");

    var callbackFn = callback;
    // Only bind click events ONCE
    if (!window.confirmationModalEventsBound) {
        $("#confirm-yes").on("click", function () {
            if (typeof callbackFn === "function") {
                callbackFn(true, $this);
            }
            $("#confirmationModal").modal("hide");
        });

        $("#confirmation-close").on("click", function () {
            if (typeof callbackFn === "function") {
                callbackFn(false, $this);
            }
            $("#confirmationModal").modal("hide");
        });

        window.confirmationModalEventsBound = true;
    }
}

function hideCommanModal() {
    $("#commonModal").modal("hide");
}

function reloadTable(){

    const lastDt = new $.fn.dataTable.Api($.fn.dataTable.tables().pop());
    lastDt.ajax.reload(null, false);
}

document.getElementById("billtModal") &&
    document
        .getElementById("billtModal")
        .addEventListener("click", function () {
            var f = document.getElementById("liveToast");
            var a = new bootstrap.Toast(f).show();

            var message =
                '<div class="mt-3 text-start"><label for="input-bill-number" class="form-label fs-13">Bill Number</label><input type="text" class="form-control" id="bill-number" placeholder="Enter Bill Number"></div><div class="mt-3 text-start"><label for="input-lr-number" class="form-label fs-13">LR Number</label><input type="text" class="form-control" id="lr-number" name="lr_number" placeholder="Enter LR Number"></div><div class="mt-3 text-start"><label for="input-article-number" class="form-label fs-13">No of Articles</label><input type="text" class="form-control" id="no_article" name="no_article" placeholder="Enter No of Articles"></div><div class="mt-3 text-start"><label for="input-transport-charge" class="form-label fs-13">Transport Charge</label><input type="number" class="form-control" id="transport_charge" name="transport_charge" placeholder="Enter Transport Charge"></div><div class="mt-3 text-start"><button class="btn btn-success btn-sm" id="generate-invoice-btn"><i class="fas fa-plus-circle"></i> Generate Invoice</button></div>';

            $("#liveToast").addClass("bg-primary");
            $("#liveToast .toast-body").html(message);
        });


        function initAddressCardEvents() {
    document.querySelectorAll('.address-card').forEach(card => {
        card.addEventListener('click', function () {

            const radio = this.querySelector('.address-card-radio');
            radio.checked = true;

            const groupName = radio.name;

            document.querySelectorAll(`input[name="${groupName}"]`)
                .forEach(r => r.closest('.address-card')?.classList.remove('selected'));

            this.classList.add('selected');
        });
    });
}
