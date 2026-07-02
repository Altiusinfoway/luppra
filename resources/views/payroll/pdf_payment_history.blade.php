<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Payslip</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 14px;
        }

        .container {
            width: 100%;
            border: 2px solid #d8d8d8;
            padding: 20px;
            border-radius: 10px;
        }

        h2,
        h3 {
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 15px;
        }

        .info-table td {
            padding: 3px 0;
        }

        .earnings-table,
        .deductions-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .earnings-table th,
        .deductions-table th {
            background: #e6e6e6;
            padding: 8px;
            border: 1px solid #000;
            text-align: center;
        }

        .earnings-table td,
        .deductions-table td {
            padding: 6px;
            border: 1px solid #000;
        }

        .total-row {
            font-weight: bold;
            background: #f2f2f2;
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
            border-top: 1px solid #000;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        .footer-text {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
        }
    </style>

</head>

<body>

    <div class="container">

        <h2>Payslip</h2>

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

    </div>

    <!-- Employee Details -->
    <table class="info-table">

        <tr>
            <td>Current Date : <strong>{{ $current_date ?? '' }} </strong></td>
            <td></td>
        </tr>

        <tr>
            <td>Date of Joining : <strong>{{ $employee_rcd->dob ?? '' }} </strong></td>
            <td>Employee Name : <strong>{{ $employee_rcd->name ?? '' }}</strong></td>
        </tr>
        <tr>
            <td>Department : <strong>{{ $employee_rcd->departments->name ?? '' }}</strong></td>
            <td>Designation : <strong>{{ $employee_rcd->getDesignation->name ?? '' }}</strong></td>
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
