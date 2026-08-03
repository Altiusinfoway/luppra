<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Payslip</title>

    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            margin: 18px;
            font-size: 13px;
            color: #0f172a;
            background: #f8fafc;
        }

        .payslip-shell {
            width: 100%;
            border: 1px solid #cbd5e1;
            padding: 24px;
            border-radius: 18px;
            background: #ffffff;
        }

        h2,
        h3 {
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .eyebrow {
            text-align: center;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #2563eb;
            margin-bottom: 10px;
        }

        .title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            font-size: 12px;
            margin-bottom: 18px;
        }

        .company-info {
            text-align: center;
            margin-bottom: 20px;
            line-height: 1.6;
            color: #475569;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: separate;
            border-spacing: 12px 0;
        }

        .meta-card {
            width: 50%;
            vertical-align: top;
            border: 1px solid #dbeafe;
            background: #f8fbff;
            border-radius: 12px;
            padding: 12px 14px;
        }

        .meta-title {
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 8px;
        }

        .meta-line {
            padding: 2px 0;
        }

        .earnings-table,
        .deductions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .earnings-table th,
        .deductions-table th {
            background: #eff6ff;
            color: #1e3a8a;
            padding: 10px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }

        .earnings-table td,
        .deductions-table td {
            padding: 9px;
            border: 1px solid #cbd5e1;
        }

        .total-row {
            font-weight: bold;
            background: #f8fafc;
        }

        .sign-section {
            margin-top: 40px;
            width: 100%;
        }

        .sign-box {
            width: 45%;
            display: inline-block;
            text-align: center;
        }

        .line {
            margin-top: 40px;
            border-top: 1px solid #94a3b8;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #64748b;
        }
    </style>

</head>

<body>

    <div class="payslip-shell">

        <div class="eyebrow">Payroll Document</div>
        <div class="title">Payslip</div>
        <div class="subtitle">Salary payment summary generated from the payroll workspace.</div>

        @php
            $website_name = \App\Models\Utility::getSetting('website_name');
            $company_address = \App\Models\Utility::getSetting('company_address_id');
            $address = null;
            if ($company_address) {
                $address = \App\Models\Address::find($company_address);
            }
        @endphp

        <div class="company-info">
            <div>{{ $website_name ?? '' }}</div>
            <div>{{ optional($address)->address_line_1 }}</div>
            <div>{{ optional($address)->address_line_2 }}</div>
            <div>{{ optional(optional($address)->get_city)->name }}, {{ optional(optional($address)->get_state)->name }}
                {{ optional($address)->zipcode }}</div>
            <div>Phone: {{ \App\Models\Utility::getSetting('phone') ?? '' }} | Email:
                {{ \App\Models\Utility::getSetting('email') ?? '' }} | GST No:{{ \App\Models\Utility::getSetting('gst_no') ?? '' }}
            </div>
        </div>

        <!-- Employee Details -->
        <table class="meta-table">
            <tr>
                <td class="meta-card">
                    <div class="meta-title">Payslip Info</div>
                    <div class="meta-line">Current Date: <strong>{{ $current_date ?? '' }}</strong></div>
                    <div class="meta-line">Date of Joining: <strong>{{ $employee_rcd->dob ?? '' }}</strong></div>
                </td>
                <td class="meta-card">
                    <div class="meta-title">Employee Info</div>
                    <div class="meta-line">Employee Name: <strong>{{ $employee_rcd->name ?? '' }}</strong></div>
                    <div class="meta-line">Department: <strong>{{ $employee_rcd->departments->name ?? '' }}</strong></div>
                    <div class="meta-line">Designation: <strong>{{ $employee_rcd->getDesignation->name ?? '' }}</strong></div>
                </td>
            </tr>
        </table>

    <!-- Earnings & Deductions Table -->
    <table class="earnings-table">
        <tr>
            <th>Earnings</th>
            <th>Amount</th>
        </tr>

        <tr>
            <td>Basic</td>
            <td>{{ $payment_rcd->getEmployeeSalaryDetail->final_salary - $payment_rcd->getEmployeeSalaryDetail->sales_bonus }}
            </td>
        </tr>

        <tr>
            <td>Bonus</td>
            <td>{{ $payment_rcd->getEmployeeSalaryDetail->sales_bonus ?? 0 }}</td>
        </tr>


        <tr class="total-row">
            <td colspan="1" style="text-align:right;">Net Pay</td>
            <td colspan="1" style="text-align:left;">{{ $payment_rcd->getPayment->amount ?? 0 }}</td>
        </tr>
    </table>


        <!-- Signatures -->
        <div class="sign-section">

            <div class="sign-box">
                Employer Signature <br><br>
                <div class="line"></div>
            </div>

            <div class="sign-box">
                Employee Signature <br><br>
                <div class="line"></div>
            </div>

        </div>

        <div class="footer-text">
            This is a system generated payslip
        </div>

    </div>

</body>

</html>
