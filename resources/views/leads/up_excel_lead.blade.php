@extends('layouts.app')

@section('page-css')
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> --}}
    <style>
        .import-suite {
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.78) 0%, rgba(245, 247, 251, 0) 100%);
        }

        .import-suite .hero-shell,
        .import-suite .import-shell {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 26px;
            background:
                radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 28%),
                radial-gradient(circle at left center, rgba(37, 99, 235, 0.12), transparent 30%),
                #ffffff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.07);
        }

        .import-suite .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.76);
            border: 1px solid #dbeafe;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .import-suite .summary-card {
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.84);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .import-suite .summary-card .label {
            display: block;
            margin-bottom: 8px;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .import-suite .summary-card h3 {
            margin: 0;
            font-size: 1.7rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #0f172a;
        }

        .import-suite .step-card {
            border: 1px solid #e2e8f0;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 12px 26px rgba(15, 23, 42, 0.04);
        }

        .import-suite .step-card .card-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 700;
            color: #0f172a;
        }

        .import-suite .table-wrap {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            overflow: hidden;
            background: #fff;
        }

        .import-suite .table-responsive {
            border-radius: 0 0 18px 18px;
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
            background-color: #f8fafc;
            border: 2px dashed #dee2e6;
            border-radius: 18px;
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

        .excel-loader-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.82);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            backdrop-filter: blur(2px);
        }

        .excel-loader-overlay.show {
            display: flex;
        }

        .excel-loader-card {
            min-width: 280px;
            max-width: 420px;
            background: #fff;
            border-radius: 14px;
            padding: 24px 28px;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.12);
            text-align: center;
        }

        .excel-loader-spinner {
            width: 52px;
            height: 52px;
            border: 4px solid #dcecff;
            border-top-color: #0095ff;
            border-radius: 50%;
            margin: 0 auto 14px;
            animation: excelSpin 0.8s linear infinite;
        }

        .excel-loader-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 6px;
        }

        .excel-loader-text {
            color: #6b7280;
            margin: 0;
        }

        @keyframes excelSpin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endsection

@section('content')
<div class="page-content import-suite">
    <div class="excel-loader-overlay" id="excelLoader">
        <div class="excel-loader-card">
            <div class="excel-loader-spinner"></div>
            <div class="excel-loader-title" id="excelLoaderTitle">Please wait</div>
            <p class="excel-loader-text" id="excelLoaderText">Processing your Excel file...</p>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="hero-shell mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="row align-items-center g-4">
                            <div class="col-lg-8">
                                <span class="hero-eyebrow">Lead Import</span>
                                <h1 class="mb-3">Import Leads</h1>
                                <p class="text-muted mb-0">Upload your lead sheet, map source columns, preview rows, and import contacts using the same refined workflow shell as the rest of the CRM.</p>
                            </div>
                            <div class="col-lg-4">
                                <div class="d-flex justify-content-lg-end">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('leads.list') }}">Leads</a></li>
                                        <li class="breadcrumb-item active">Import</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Import Flow</span>
                        <h3>3 Steps</h3>
                        <p class="text-muted mb-0 mt-2">Upload, map, and preview lead records before they enter the CRM.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card summary-card h-100">
                    <div class="card-body">
                        <span class="label">Required</span>
                        <h3>Name + Phone</h3>
                        <p class="text-muted mb-0 mt-2">Keep the lead pipeline clean by validating the most important contact fields first.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="import-shell p-4 p-lg-5">

                    <div class="container py-2">

                        <div class="card step-card">
                            <div class="card-header">
                                <span class="step-number">1</span>Upload Excel File
                            </div>
                            <div class="card-body">
                                <div class="instructions">
                                    <h5><i class="fas fa-info-circle me-2"></i>Instructions</h5>
                                    <ul class="mb-0">
                                        <li>Your Excel file should contain data with headers in the first row</li>
                                        <li>Supported formats: .xls, .xlsx</li>
                                        <li>Required fields: name, email, mobile, lead source,description</li>
                                        <li>After upload, map your Excel columns to the database fields</li>
                                    </ul>
                                    <div class="mt-3">
                                        <a href="{{ asset('public/sample/lead_sample.xlsx') }}" class="btn btn-sm btn-outline-primary" download>
                                            <i class="fas fa-download me-2"></i>Download Sample.xlsx
                                        </a>
                                    </div>
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

                        <div class="card step-card" id="mappingSection" style="display: none;">
                            <div class="card-header">
                                <span class="step-number">2</span>Map Columns
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Match your Excel columns to the given fields. Required fields
                                    are marked with *.</p>

                                <div class="table-responsive table-wrap">
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
                                                    <span class="text-muted" id="nameSample">John Doe</span>
                                                </td>
                                            </tr>
                                            <tr class="mapping-row">
                                                <td>
                                                    <span class="">Email</span> {{-- required-field --}}
                                                </td>
                                                <td>
                                                    <select class="form-select" name="mapping[1]" id="nameMapping">
                                                        <option value="">Select Excel Column</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="text-muted" id="emailSample">john@example.com</span>
                                                </td>
                                            </tr>
                                            <tr class="mapping-row">
                                                <td>
                                                    <span class="required-field">Phone</span>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="mapping[2]" id="nameMapping" required>
                                                        <option value="">Select Excel Column</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="text-muted" id="phoneSample">1234567895</span>
                                                </td>
                                            </tr>
                                            <tr class="mapping-row">
                                                <td>
                                                    <span>Lead Sources</span>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="mapping[3]" id="nameMapping">
                                                        <option value="">Select Excel Column</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="text-muted" id="lead_sourceSample">facebook / other</span>
                                                </td>
                                            </tr>
                                            <tr class="mapping-row">
                                                <td>
                                                    <span >Description</span>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="mapping[4]" id="nameMapping">
                                                        <option value="">Select Excel Column</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="text-muted" id="descriptionSample">description</span>
                                                </td>
                                            </tr>
                                            {{-- <tr class="mapping-row">
                                                <td>
                                                    <span>Company Name</span>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="mapping[5]" id="nameMapping">
                                                        <option value="">Select Excel Column</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="text-muted" id="company_nameSample">Company Name</span>
                                                </td>
                                            </tr> --}}

                                            <input type="hidden" name="mapping[5]">

                                            <tr class="mapping-row">
                                                <td>
                                                    <span >GST No</span>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="mapping[6]" id="nameMapping">
                                                        <option value="">Select Excel Column</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="text-muted" id="gst_noSample">12ABJFR4468B1##</span>
                                                </td>
                                            </tr>

                                            <tr class="mapping-row">
                                                <td>
                                                    <span >Adhar No</span>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="mapping[7]" id="nameMapping">
                                                        <option value="">Select Excel Column</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="text-muted" id="adhar_noSample">1234 5678 ####</span>
                                                </td>
                                            </tr>

                                            <tr class="mapping-row">
                                                <td>
                                                    <span >Udhyam No</span>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="mapping[8]" id="nameMapping">
                                                        <option value="">Select Excel Column</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="text-muted" id="udhyam_noSample">UDYAM-GJ-24-0037086</span>
                                                </td>
                                            </tr>

                                            <tr class="mapping-row">
                                                <td>
                                                    <span >State</span>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="mapping[9]" id="nameMapping">
                                                        <option value="">Select Excel Column</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="text-muted" id="stateSample">Gujarat</span>
                                                </td>
                                            </tr>

                                            <tr class="mapping-row">
                                                <td>
                                                    <span >City</span>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="mapping[10]" id="nameMapping">
                                                        <option value="">Select Excel Column</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="text-muted" id="CitySample">Rajkot</span>
                                                </td>
                                            </tr>

                                            <tr class="mapping-row">
                                                <td>
                                                    <span >Address</span>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="mapping[11]" id="nameMapping">
                                                        <option value="">Select Excel Column</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="text-muted" id="addressSample">Location </span>
                                                </td>
                                            </tr>

                                            <tr class="mapping-row">
                                                <td>
                                                    <span >ZipCode</span>
                                                </td>
                                                <td>
                                                    <select class="form-select" name="mapping[12]" id="nameMapping">
                                                        <option value="">Select Excel Column</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="text-muted" id="zipcodeSample">360005 </span>
                                                </td>
                                            </tr>



                                        </tbody>
                                    </table>
                                </div>


                            </div>
                        </div>

                        <div class="card step-card" id="previewSection" style="display: none;">
                            <div class="card-header">
                                <span class="step-number">3</span>Data Preview
                            </div>
                            <div class="card-body">

                                <div class="table-responsive table-wrap">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Lead Source</th>
                                                <th>Description</th>
                                                {{-- <th>Company Name</th> --}}
                                                <th>GST No</th>
                                                <th>Adhar No</th>
                                                <th>Udhyam No</th>
                                                <th>State</th>
                                                <th>City</th>
                                                <th>Address</th>
                                                <th>ZipCode</th>
                                                <th>Message</th>
                                            </tr>
                                        </thead>
                                        <tbody id="previewData">

                                        </tbody>
                                    </table>

                                    <input type="hidden" name="leads_data" value="">

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
        const excelLoader = document.getElementById('excelLoader');
        const excelLoaderTitle = document.getElementById('excelLoaderTitle');
        const excelLoaderText = document.getElementById('excelLoaderText');

        let currentStep = 1;
        let isBusy = false;

        function setLoadingState(loading, title = 'Please wait', message = 'Processing your Excel file...') {
            isBusy = loading;
            excelLoader.classList.toggle('show', loading);
            excelLoaderTitle.textContent = title;
            excelLoaderText.textContent = message;

            [nextBtn, backBtn, importBtn, cancelBtn].forEach(button => {
                button.disabled = loading;
            });
        }

        function normalizeHeader(value) {
            return String(value || '')
                .replace(/\uFEFF/g, '')
                .trim()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, ' ')
                .trim();
        }

        function autoMapColumns(columnArray) {
            const aliasesByMappingIndex = {
                0: ['name', 'full name', 'customer name', 'client name'],
                1: ['email', 'email id', 'mail', 'mail id'],
                2: ['phone', 'mobile', 'mobile no', 'mobile number', 'phone no', 'phone number', 'contact', 'contact no'],
                3: ['lead source', 'source', 'lead sources'],
                4: ['description', 'desc', 'remarks', 'note', 'notes'],
                6: ['gst no', 'gst number', 'gst', 'gstin', 'gst no.', 'gstin no', 'gstin number', 'gstno', 'gst_no', 'gstinno', 'gstin_no'],
                7: ['adhar no', 'aadhar no', 'aadhaar no', 'adhar number', 'aadhar number', 'aadhaar number'],
                8: ['udhyam no', 'udyam no', 'udhyam number', 'udyam number', 'udyam registration'],
                9: ['state'],
                10: ['city'],
                11: ['address', 'address line', 'location'],
                12: ['zipcode', 'zip code', 'pincode', 'pin code']
            };

            const normalizedHeaders = columnArray.map((header, index) => ({
                index,
                value: normalizeHeader(header)
            }));

            Object.entries(aliasesByMappingIndex).forEach(([mappingIndex, aliases]) => {
                const selectEl = document.querySelector(`select[name="mapping[${mappingIndex}]"]`);
                if (!selectEl) return;

                const matched = normalizedHeaders.find(item => aliases.includes(item.value));
                if (matched) {
                    selectEl.value = String(matched.index);
                }
            });
        }

        excelFile.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                fileName.textContent = e.target.files[0].name;
            }
        });

        nextBtn.addEventListener('click', function() {
            if (isBusy) {
                return;
            }

            if (currentStep === 1)
            {

                if (excelFile.files.length === 0) {
                    alert('Please select an Excel file first.');
                    return;
                }

                    let formData = new FormData();
                    formData.append("excel_file", excelFile.files[0]);
                    setLoadingState(true, 'Reading Excel File', 'Fetching column headers for mapping...');

                    fetch("{{ route('leads.get_header') }}", {
                        method: "POST",
                        body: formData,
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                        }
                    })
                    .then(async (res) => {
                        const contentType = res.headers.get("content-type") || "";
                        if (!contentType.includes("application/json")) {
                            const text = await res.text();
                            throw new Error(text || "Unexpected server response while fetching headers.");
                        }

                        const data = await res.json();
                        if (!res.ok) {
                            throw new Error(data.message || "Failed to fetch headers.");
                        }

                        return data;
                    })
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

                            autoMapColumns(columnArray);

                            mappingSection.style.display = "block";
                            nextBtn.textContent = "Preview Data";
                            backBtn.style.display = "block";
                            currentStep = 2;
                        } else {
                            alert(data.message || "Something went wrong.");
                        }
                    })
                    .catch(err => {
                        console.error("Error fetching headers:", err);
                        alert(err.message || "Error while fetching headers.");
                    })
                    .finally(() => {
                        setLoadingState(false);
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
                    alert("⚠ Please map all required fields (Name,Phone).");
                    return;
                }

                show_toastr('success', 'Excel preview in progress.. please wait....');
                setLoadingState(true, 'Generating Preview', 'Reading Excel rows and preparing preview data...');

                fetch("{{ route('leads.excel-preview') }}", {
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

                    document.querySelector("input[name=leads_data]").value = JSON.stringify(response.data);

                    response.data.forEach((row, rowIndex) => {
                        let errorMsg = "";
                        if (response.messages && response.messages[rowIndex]) {
                            errorMsg = response.messages[rowIndex];
                        }

                        previewContainer.innerHTML += `<tr>
                            <td>${row.name ?? ''}</td>
                            <td>${row.email ?? ''}</td>
                            <td>${row.phone ?? ''}</td>
                            <td>${row.lead_source ?? ''}</td>
                            <td>${row.description ?? ''}</td>

                            <td>${row.gst_numb ?? ''}</td>
                            <td>${row.adhar_numb ?? ''}</td>
                            <td>${row.udhyam_numb ?? ''}</td>
                            <td>${row.state ?? ''}</td>
                            <td>${row.city ?? ''}</td>
                            <td>${row.address ?? ''}</td>
                            <td>${row.zipcode ?? ''}</td>
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
                    // console.error("Error sending mapping:", err);
                    alert("Error while sending mapping.");
                })
                .finally(() => {
                    setLoadingState(false);
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

            if (isBusy) {
                return;
            }

            let formData = new FormData();
            formData.append(
                "_token",
                document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            );
            formData.append("leads_data", document.querySelector("input[name=leads_data]").value);
            setLoadingState(true, 'Importing Leads', 'Uploading data and creating leads. This may take a moment...');

            fetch("{{ route('leads.upload') }}", {
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
                            fetch("{{ route('leads.job.status') }}?job_key=" + jobKey)
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
                                        setLoadingState(false);
                                        if (jobRes.status === "completed") {
                                            show_toastr("success", "Leads uploaded successfully!");
                                            setTimeout(() => {
                                                location.reload();
                                            }, 2000);

                                        } else {
                                            show_toastr("error", "Upload failed: " + jobRes.status);
                                        }
                                    }
                                })
                                .catch((err) =>
                                {
                                    setLoadingState(false);
                                    console.error("Job Status Error:", err.message || err);
                                });
                        }, 3000);
                    } else {
                        setLoadingState(false);
                        show_toastr("error", response.message || "Upload could not be started");
                    }
                })
                .catch((err) => {
                    setLoadingState(false);
                    console.error("Upload Error:", err.message || err);
                    show_toastr("error", err.message || "Something went wrong while uploading leads");
                });


        });

        // Cancel button click
        cancelBtn.addEventListener('click', function() {
            if (isBusy) {
                return;
            }

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
