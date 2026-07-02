@extends('layouts.app')

@section('content')

@section('page-css')
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            max-width: 1200px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e3e6f0;
            font-weight: 600;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0 !important;
        }

        .table-responsive {
            border-radius: 0 0 10px 10px;
        }

        .table th {
            background-color: #f8f9fa;
        }

        .required-field::after {
            content: " *";
            color: #dc3545;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0062cc, #0095ff);
            border: none;
            padding: 10px 20px;
        }

        .btn-outline-secondary {
            padding: 10px 20px;
        }

        .preview-box {
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .preview-box:hover {
            border-color: #0095ff;
        }

        .mapping-row {
            transition: all 0.3s;
        }

        .mapping-row:hover {
            background-color: #f1f8ff;
        }

        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            text-align: center;
            background-color: #0095ff;
            color: white;
            border-radius: 50%;
            margin-right: 10px;
        }

        .instructions {
            background-color: #e8f4ff;
            border-left: 4px solid #0095ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
@endsection
<div class="page-content">

    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Import Section</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">User Operation</a></li>
                            <li class="breadcrumb-item active">List</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <!-- Varying Modal Content -->
            <div class="col-lg-12">
                <div class="card">

                    <div class="container py-5">

                        <div class="card">
                            <div class="card-header">
                                <span class="step-number">1</span>Upload Excel File
                            </div>
                            <div class="card-body">
                                <div class="instructions">
                                    <h5><i class="fas fa-info-circle me-2"></i>Instructions</h5>
                                    <ul class="mb-0">
                                        <li>Your Excel file should contain data with headers in the first row</li>
                                        <li>Supported formats: .xls, .xlsx</li>
                                        <li>Required fields: name, sku_code, price, unit_type,unit</li>
                                        <li>After upload, map your Excel columns to the database fields</li>
                                    </ul>
                                </div>

                                <div class="preview-box">
                                    <i class="fas fa-cloud-upload-alt display-4 text-muted mb-3"></i>
                                    <h5>Drag & Drop your Excel file here</h5>
                                    <p class="text-muted">or</p>
                                    <div class="mb-3">
                                        <input type="file" class="form-control d-none" id="excelFile"
                                            accept=".xls,.xlsx">
                                        <button class="btn btn-primary"
                                            onclick="document.getElementById('excelFile').click()">
                                            <i class="fas fa-upload me-2"></i>Browse Files
                                        </button>
                                    </div>
                                    <small class="text-muted" id="fileName">No file selected</small>
                                </div>
                            </div>
                        </div>

                        <div class="card" id="mappingSection" style="display: none;">
                            <div class="card-header">
                                <span class="step-number">2</span>Map Columns
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Match your Excel columns to the given fields. Required fields
                                    are marked with *.</p>

                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Platform Field</th>
                                                <th>Excel Column</th>
                                                <th>Sample Data</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="mapping-row">
                                                <td>
                                                    <span class="required-field">Name</span>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="mapping[0]" id="nameMapping" required>
                                                        <option value="">Select Excel Column</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="text-muted" id="nameSample">Product-Name</span>
                                                </td>
                                            </tr>
                                            <tr class="mapping-row">
                                                <td>
                                                    <span class="">SKU Code</span>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="mapping[1]" id="nameMapping" >
                                                        <option value="">Select Excel Column</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="text-muted" id="sku_codeSample">Product-Code</span>
                                                </td>
                                            </tr>
                                            <tr class="mapping-row">
                                                <td>
                                                    <span class="required-field">Price</span>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="mapping[2]" id="nameMapping" required>
                                                        <option value="">Select Excel Column</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="text-muted" id="priceSample">150</span>
                                                </td>
                                            </tr>
                                            <tr class="mapping-row">
                                                <td>
                                                    <span class="required-field">Unit Type</span>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="mapping[3]" id="nameMapping" required>
                                                        <option value="">Select Excel Column</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="text-muted" id="unit_typeSample">Weight</span>
                                                </td>
                                            </tr>
                                            <tr class="mapping-row">
                                                <td>
                                                    <span class="required-field">Unit</span>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="mapping[4]" id="nameMapping" required>
                                                        <option value="">Select Excel Column</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="text-muted" id="unitSample">Gram</span>
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>


                            </div>
                        </div>

                        <div class="card" id="previewSection" style="display: none;">
                            <div class="card-header">
                                <span class="step-number">3</span>Data Preview
                            </div>
                            <div class="card-body">

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>SKU Code</th>
                                                <th>Price</th>
                                                <th>Unit Type</th>
                                                <th>Unit</th>
                                                <th>Message</th>
                                            </tr>
                                        </thead>
                                        <tbody id="previewData">

                                        </tbody>
                                    </table>

                                    <input type="hidden" name="product_data" value="">

                                </div>
                            </div>
                        </div>


                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <button class="btn btn-outline-secondary" id="backBtn" style="display:none;">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </button>
                            </div>

                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary" id="cancelBtn">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </button>
                                <button class="btn btn-primary" id="importBtn" style="display:none;">
                                    <i class="fas fa-database me-2"></i>Import Leads
                                </button>
                                <button class="btn btn-primary" id="nextBtn">
                                    <i class="fas fa-arrow-right me-2"></i>Next
                                </button>
                            </div>
                        </div>


                    </div>



                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const excelFile = document.getElementById('excelFile');
        const fileName = document.getElementById('fileName');
        const mappingSection = document.getElementById('mappingSection');
        const previewSection = document.getElementById('previewSection');
        const nextBtn = document.getElementById('nextBtn');
        const backBtn = document.getElementById('backBtn');
        const importBtn = document.getElementById('importBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        let currentStep = 1;

        excelFile.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                fileName.textContent = e.target.files[0].name;
            }
        });

        nextBtn.addEventListener('click', function() {
            if (currentStep === 1)
            {

                if (excelFile.files.length === 0) {
                    alert('Please select an Excel file first.');
                    return;
                }

                    let formData = new FormData();
                    formData.append("excel_file", excelFile.files[0]);

                    fetch("{{ route('products.get_header') }}", {
                        method: "POST",
                        body: formData,
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status)
                        {

                            let columnArray = data.headers_list;
                            console.log("Excel Headers:", columnArray);

                            // populate dropdowns with headers
                            document.querySelectorAll(".mapping-row select").forEach(function (selectEl) {
                                selectEl.innerHTML = '<option value="">Select Excel Column</option>';
                                columnArray.forEach((header, index) => {
                                    let opt = document.createElement("option");
                                    opt.value = index;
                                    opt.textContent = header;
                                    selectEl.appendChild(opt);
                                });
                            });

                            mappingSection.style.display = "block";
                            nextBtn.textContent = "Preview Data";
                            backBtn.style.display = "block";
                            currentStep = 2;
                        } else {
                             console.error(data.message);
                            // alert(data.message || "Something went wrong.");
                        }
                    })
                    .catch(err => {
                        console.error("Error fetching headers:", err);
                        // alert("Error while fetching headers.");
                    });



            }
            else if (currentStep === 2)
            {
                // ----------------------------------- Step-2 --------------------------------
                let isValid = true;
                let formData = new FormData();
                formData.append("excel_file", excelFile.files[0]);


                document.querySelectorAll(".mapping-row select").forEach(select => {
                    if (select.hasAttribute("required") && !select.value) {
                        isValid = false;
                        select.classList.add("is-invalid");
                    } else {
                        select.classList.remove("is-invalid");
                    }

                    if (select.name) {
                        formData.append(select.name, select.value);
                    }
                });

                if (!isValid) {
                    alert("⚠ Please map all required fields .");
                    return;
                }

                show_toastr('success', 'Excel preview in progress.. please wait....');

                fetch("{{ route('products.excel-preview') }}", {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                    }
                })
                .then(res => res.json())
                .then(response => {
                    console.log("Preview Response:", response);

                    const previewContainer = document.getElementById("previewData");
                    previewContainer.innerHTML = "";

                    document.getElementById("previewSection").style.display = 'block';

                    if (!response.status)
                    {
                        previewContainer.innerHTML = `<tr>
                            <td colspan="6" style="color:red; text-align:center;">
                                ${response.messages || "Failed to load preview"}
                            </td>
                        </tr>`;
                        return;
                    }

                    document.querySelector("input[name=product_data]").value = JSON.stringify(response.data);

                    response.data.forEach((row, rowIndex) => {
                        let errorMsg = "";
                        if (response.messages && response.messages[rowIndex]) {
                            errorMsg = response.messages[rowIndex];
                        }

                        previewContainer.innerHTML += `<tr>
                            <td>${row.name ?? ''}</td>
                            <td>${row.sku_code ?? ''}</td>
                            <td>${row.price ?? ''}</td>
                            <td>${row.unit_type ?? ''}</td>
                            <td>${row.unit ?? ''}</td>
                            <td style="color:${errorMsg ? 'red' : 'green'}">
                                ${errorMsg || ''}
                            </td>
                        </tr>`;
                    });



                    nextBtn.style.display = 'none';
                    importBtn.style.display = 'block';
                    currentStep = 3;

                })
                .catch(err => {
                    console.error("Error sending mapping:", err);
                    // alert("Error while sending mapping.");
                });

            }


        });

        backBtn.addEventListener('click', function() {
            if (currentStep === 2) {
                mappingSection.style.display = 'none';
                nextBtn.textContent = 'Next';
                backBtn.style.display = 'none';
                currentStep = 1;
            } else if (currentStep === 3) {
                previewSection.style.display = 'none';
                nextBtn.style.display = 'block';
                nextBtn.textContent = 'Preview Data';
                importBtn.style.display = 'none';
                currentStep = 2;
            }
        });

        importBtn.addEventListener('click', function(e) {

            e.preventDefault();

            let formData = new FormData();
            formData.append(
                "_token",
                document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            );
            formData.append("product_data", document.querySelector("input[name=product_data]").value);

            fetch("{{ route('products.upload') }}", {
                method: "POST",
                body: formData,
            })
                .then(async (res) => {
                    const text = await res.text();
                    try {
                        return JSON.parse(text);
                    } catch (err) {
                        console.error("Server returned non-JSON response:", text);
                        throw new Error("Unexpected server response. Check logs.");
                    }
                })
                .then((response) => {
                    if (response.status) {
                        show_toastr("success", "Upload started. Please wait...");

                        let jobKey = response.job_key;

                        let interval = setInterval(() => {
                            fetch("{{ route('products.job.status') }}?job_key=" + jobKey)
                                .then(async (res) => {
                                    const text = await res.text();
                                    try {
                                        return JSON.parse(text);
                                    } catch (err) {
                                        console.error("Non-JSON in job status:", text);
                                        throw new Error("Invalid job status response");
                                    }
                                })
                                .then((jobRes) => {
                                    if (jobRes.status !== "pending") {
                                        clearInterval(interval);
                                        if (jobRes.status === "completed") {
                                            show_toastr("success", "Products uploaded successfully!");
                                            setTimeout(() => {
                                                location.reload();
                                            }, 2000);

                                        } else {
                                            show_toastr("error", "Upload failed: " + jobRes.status);
                                        }
                                    }
                                })
                                .catch((err) =>
                                    console.error("Job Status Error:", err.message || err)
                                );
                        }, 3000);
                    } else {
                        show_toastr("error", response.message || "Upload could not be started");
                    }
                })
                .catch((err) => {
                    console.error("Upload Error:", err.message || err);
                    show_toastr("error", err.message || "Something went wrong while uploading leads");
                });


        });

        // Cancel button click
        cancelBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to cancel the import?')) {

                excelFile.value = '';
                fileName.textContent = 'No file selected';
                mappingSection.style.display = 'none';
                previewSection.style.display = 'none';
                nextBtn.style.display = 'block';
                nextBtn.textContent = 'Next';
                backBtn.style.display = 'none';
                importBtn.style.display = 'none';
                currentStep = 1;
            }
        });

    });
</script>
@endsection
