<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accountant User Guide - {{ $settings->school_name ?? 'St. Francis of Assisi Private School' }}</title>
    <style>
        /* Page Setup - Standard A4 with proper margins */
        @page {
            size: A4;
            margin: 2.5cm 2cm 2.5cm 2.5cm;
        }

        @page :first {
            margin: 0;
        }

        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333333;
        }

        /* Cover Page */
        .cover-page {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #2563eb 100%);
            padding: 0;
            margin: 0;
            page-break-after: always;
            position: relative;
        }

        .cover-content {
            padding: 80px 60px;
            text-align: center;
            color: white;
        }

        .cover-logo-container {
            background: white;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .cover-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .cover-school-name {
            font-size: 28pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 8px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .cover-motto {
            font-size: 14pt;
            font-style: italic;
            opacity: 0.9;
            margin-bottom: 60px;
        }

        .cover-title-box {
            background: rgba(255,255,255,0.15);
            padding: 40px 50px;
            margin: 40px 0;
            border-radius: 8px;
        }

        .cover-title {
            font-size: 32pt;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }

        .cover-subtitle {
            font-size: 16pt;
            font-weight: 300;
            opacity: 0.9;
        }

        .cover-meta {
            position: absolute;
            bottom: 60px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10pt;
            opacity: 0.8;
        }

        .cover-meta p {
            margin: 5px 0;
        }

        /* Content Pages */
        .page {
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: avoid;
        }

        /* Page Header */
        .page-header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .page-header-left {
            display: table-cell;
            vertical-align: middle;
            width: 60px;
        }

        .page-header-logo {
            width: 45px;
            height: 45px;
            object-fit: contain;
        }

        .page-header-center {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }

        .page-header-school {
            font-size: 12pt;
            font-weight: 600;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .page-header-doc {
            font-size: 9pt;
            color: #666;
        }

        .page-header-right {
            display: table-cell;
            vertical-align: middle;
            width: 60px;
            text-align: right;
        }

        /* Table of Contents */
        .toc-title {
            font-size: 20pt;
            font-weight: 700;
            color: #1e40af;
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #1e40af;
        }

        .toc-section {
            display: table;
            width: 100%;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .toc-number {
            display: table-cell;
            width: 40px;
            font-weight: 700;
            color: #1e40af;
            font-size: 12pt;
        }

        .toc-text {
            display: table-cell;
            font-size: 12pt;
            color: #333;
        }

        .toc-subsection {
            display: table;
            width: 100%;
            padding: 8px 0 8px 40px;
        }

        .toc-sub-number {
            display: table-cell;
            width: 50px;
            color: #666;
            font-size: 10pt;
        }

        .toc-sub-text {
            display: table-cell;
            font-size: 10pt;
            color: #666;
        }

        /* Section Styling */
        .section-number {
            display: inline-block;
            background: #1e40af;
            color: white;
            width: 35px;
            height: 35px;
            text-align: center;
            line-height: 35px;
            font-weight: 700;
            font-size: 14pt;
            margin-right: 15px;
            border-radius: 4px;
        }

        .section-title {
            font-size: 18pt;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
        }

        .subsection-title {
            font-size: 13pt;
            font-weight: 600;
            color: #1e40af;
            margin: 25px 0 12px 0;
            padding-left: 15px;
            border-left: 4px solid #1e40af;
        }

        /* Content */
        p {
            margin-bottom: 12px;
            text-align: justify;
        }

        /* Lists */
        ul, ol {
            margin: 12px 0 12px 25px;
        }

        li {
            margin-bottom: 8px;
        }

        /* Step List */
        .steps {
            counter-reset: step;
            list-style: none;
            margin-left: 0;
            padding-left: 0;
        }

        .steps li {
            counter-increment: step;
            padding: 10px 15px 10px 50px;
            position: relative;
            background: #f8fafc;
            margin-bottom: 8px;
            border-radius: 4px;
            border-left: 3px solid #1e40af;
        }

        .steps li::before {
            content: counter(step);
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: #1e40af;
            color: white;
            width: 24px;
            height: 24px;
            text-align: center;
            line-height: 24px;
            font-size: 11pt;
            font-weight: 600;
            border-radius: 50%;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10pt;
        }

        th {
            background: #1e40af;
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 9pt;
            letter-spacing: 0.5px;
        }

        td {
            padding: 10px 15px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background: #f8fafc;
        }

        /* Info Boxes */
        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-left: 4px solid #1e40af;
            padding: 15px 20px;
            margin: 15px 0;
            border-radius: 0 6px 6px 0;
        }

        .info-box-title {
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 8px;
            font-size: 11pt;
        }

        .warning-box {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-left: 4px solid #f59e0b;
            padding: 15px 20px;
            margin: 15px 0;
            border-radius: 0 6px 6px 0;
        }

        .warning-box::before {
            content: "IMPORTANT: ";
            font-weight: 700;
            color: #b45309;
        }

        .tip-box {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-left: 4px solid #10b981;
            padding: 15px 20px;
            margin: 15px 0;
            border-radius: 0 6px 6px 0;
        }

        .tip-box::before {
            content: "TIP: ";
            font-weight: 700;
            color: #047857;
        }

        /* Checklist */
        .checklist {
            list-style: none;
            margin-left: 0;
            padding-left: 0;
        }

        .checklist li {
            padding: 8px 0 8px 35px;
            position: relative;
            border-bottom: 1px solid #e5e7eb;
        }

        .checklist li::before {
            content: "";
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            border: 2px solid #1e40af;
            border-radius: 3px;
        }

        /* Code/Menu References */
        .menu-path {
            background: #f1f5f9;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 10pt;
            color: #1e40af;
        }

        .btn {
            display: inline-block;
            background: #1e40af;
            color: white;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 10pt;
            font-weight: 500;
        }

        /* Page Break */
        .page-break {
            page-break-after: always;
        }

        /* Footer */
        .page-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }

        .footer-left {
            float: left;
        }

        .footer-right {
            float: right;
        }

        /* Two Column Layout */
        .two-column {
            display: table;
            width: 100%;
        }

        .column {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding-right: 2%;
        }

        .column:last-child {
            padding-right: 0;
            padding-left: 2%;
        }

        /* Highlight Box */
        .highlight-box {
            background: #1e40af;
            color: white;
            padding: 20px 25px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }

        .highlight-box .big-text {
            font-size: 16pt;
            font-weight: 700;
            margin-bottom: 5px;
        }

        /* End Page */
        .end-page {
            text-align: center;
            padding: 60px 40px;
        }

        .end-logo {
            width: 60px;
            height: 60px;
            margin-bottom: 20px;
        }

        .end-school {
            font-size: 14pt;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 5px;
        }

        .end-contact {
            font-size: 10pt;
            color: #666;
            margin-bottom: 20px;
        }

        .end-motto {
            font-style: italic;
            color: #1e40af;
            font-size: 12pt;
        }
    </style>
</head>
<body>
    {{-- Cover Page --}}
    <div class="cover-page">
        <div class="cover-content">
            <div class="cover-logo-container">
                @if($settings && $settings->school_logo)
                <img src="{{ public_path('storage/' . $settings->school_logo) }}" class="cover-logo" alt="Logo">
                @endif
            </div>
            <div class="cover-school-name">{{ $settings->school_name ?? 'St. Francis of Assisi Private School' }}</div>
            <div class="cover-motto">"{{ $settings->school_motto ?? 'For God and Country' }}"</div>

            <div class="cover-title-box">
                <div class="cover-title">ACCOUNTANT</div>
                <div class="cover-title">USER GUIDE</div>
                <div class="cover-subtitle">Financial Management System</div>
            </div>

            <div class="cover-meta">
                <p><strong>Version 1.0</strong> | {{ $generatedAt->format('F Y') }}</p>
                <p>Finance & Accounts Department</p>
                <p>CONFIDENTIAL - Internal Use Only</p>
            </div>
        </div>
    </div>

    {{-- Table of Contents --}}
    <div class="page">
        <div class="page-header">
            <div class="page-header-left">
                @if($settings && $settings->school_logo)
                <img src="{{ public_path('storage/' . $settings->school_logo) }}" class="page-header-logo" alt="Logo">
                @endif
            </div>
            <div class="page-header-center">
                <div class="page-header-school">{{ $settings->school_name ?? 'St. Francis of Assisi Private School' }}</div>
                <div class="page-header-doc">Accountant User Guide</div>
            </div>
            <div class="page-header-right"></div>
        </div>

        <div class="toc-title">Table of Contents</div>

        <div class="toc-section"><span class="toc-number">1</span><span class="toc-text">Introduction & System Overview</span></div>
        <div class="toc-subsection"><span class="toc-sub-number">1.1</span><span class="toc-sub-text">Purpose of This Guide</span></div>
        <div class="toc-subsection"><span class="toc-sub-number">1.2</span><span class="toc-sub-text">System Modules</span></div>

        <div class="toc-section"><span class="toc-number">2</span><span class="toc-text">Getting Started</span></div>
        <div class="toc-subsection"><span class="toc-sub-number">2.1</span><span class="toc-sub-text">Logging In</span></div>
        <div class="toc-subsection"><span class="toc-sub-number">2.2</span><span class="toc-sub-text">Navigation Guide</span></div>

        <div class="toc-section"><span class="toc-number">3</span><span class="toc-text">Chart of Accounts</span></div>
        <div class="toc-subsection"><span class="toc-sub-number">3.1</span><span class="toc-sub-text">Account Structure</span></div>
        <div class="toc-subsection"><span class="toc-sub-number">3.2</span><span class="toc-sub-text">Creating Accounts</span></div>

        <div class="toc-section"><span class="toc-number">4</span><span class="toc-text">Bank Account Management</span></div>
        <div class="toc-subsection"><span class="toc-sub-number">4.1</span><span class="toc-sub-text">Adding Bank Accounts</span></div>
        <div class="toc-subsection"><span class="toc-sub-number">4.2</span><span class="toc-sub-text">Bank Reconciliation</span></div>

        <div class="toc-section"><span class="toc-number">5</span><span class="toc-text">Expense Management</span></div>
        <div class="toc-subsection"><span class="toc-sub-number">5.1</span><span class="toc-sub-text">Recording Expenses</span></div>
        <div class="toc-subsection"><span class="toc-sub-number">5.2</span><span class="toc-sub-text">Vendor Management</span></div>

        <div class="toc-section"><span class="toc-number">6</span><span class="toc-text">Income Recording</span></div>

        <div class="toc-section"><span class="toc-number">7</span><span class="toc-text">Payment Vouchers</span></div>

        <div class="toc-section"><span class="toc-number">8</span><span class="toc-text">Journal Entries</span></div>

        <div class="toc-section"><span class="toc-number">9</span><span class="toc-text">Financial Reports</span></div>

        <div class="toc-section"><span class="toc-number">10</span><span class="toc-text">Daily & Monthly Procedures</span></div>

        <div class="toc-section"><span class="toc-number">11</span><span class="toc-text">Troubleshooting & FAQs</span></div>
    </div>

    {{-- Section 1: Introduction --}}
    <div class="page">
        <div class="page-header">
            <div class="page-header-left">
                @if($settings && $settings->school_logo)
                <img src="{{ public_path('storage/' . $settings->school_logo) }}" class="page-header-logo" alt="Logo">
                @endif
            </div>
            <div class="page-header-center">
                <div class="page-header-school">{{ $settings->school_name ?? 'St. Francis of Assisi Private School' }}</div>
                <div class="page-header-doc">Accountant User Guide</div>
            </div>
            <div class="page-header-right"></div>
        </div>

        <div class="section-title"><span class="section-number">1</span>Introduction & System Overview</div>

        <p>Welcome to the {{ $settings->school_name ?? 'St. Francis of Assisi Private School' }} Financial Management System. This comprehensive guide provides step-by-step instructions for managing all accounting operations within the school portal.</p>

        <div class="subsection-title">1.1 Purpose of This Guide</div>
        <p>This manual serves as your complete reference for:</p>
        <ul>
            <li>Recording and tracking daily financial transactions</li>
            <li>Managing expenses, income, and bank accounts</li>
            <li>Generating accurate financial reports</li>
            <li>Maintaining proper accounting records</li>
            <li>Following established financial procedures</li>
        </ul>

        <div class="subsection-title">1.2 System Modules</div>
        <p>The accounting system comprises the following integrated modules:</p>

        <table>
            <tr>
                <th style="width: 30%;">Module</th>
                <th>Description</th>
            </tr>
            <tr>
                <td><strong>Chart of Accounts</strong></td>
                <td>Complete listing of all financial accounts organized by category</td>
            </tr>
            <tr>
                <td><strong>Bank Accounts</strong></td>
                <td>Management of school bank accounts, balances, and transactions</td>
            </tr>
            <tr>
                <td><strong>Expenses</strong></td>
                <td>Recording and tracking all school expenditures</td>
            </tr>
            <tr>
                <td><strong>Income Records</strong></td>
                <td>Non-fee income recording (donations, grants, rentals)</td>
            </tr>
            <tr>
                <td><strong>Payment Vouchers</strong></td>
                <td>Formal payment authorization documents</td>
            </tr>
            <tr>
                <td><strong>Journal Entries</strong></td>
                <td>Double-entry bookkeeping transactions</td>
            </tr>
            <tr>
                <td><strong>Financial Reports</strong></td>
                <td>Income statements, cash flow, and analysis reports</td>
            </tr>
        </table>

        <div class="highlight-box">
            <div class="big-text">Assets = Liabilities + Equity</div>
            <div>The Fundamental Accounting Equation</div>
        </div>

        <div class="info-box">
            <div class="info-box-title">Double-Entry Accounting</div>
            <p>Every transaction in this system affects at least two accounts. Each debit must have an equal credit to maintain balance in the accounting equation.</p>
        </div>
    </div>

    {{-- Section 2: Getting Started --}}
    <div class="page">
        <div class="page-header">
            <div class="page-header-left">
                @if($settings && $settings->school_logo)
                <img src="{{ public_path('storage/' . $settings->school_logo) }}" class="page-header-logo" alt="Logo">
                @endif
            </div>
            <div class="page-header-center">
                <div class="page-header-school">{{ $settings->school_name ?? 'St. Francis of Assisi Private School' }}</div>
                <div class="page-header-doc">Accountant User Guide</div>
            </div>
            <div class="page-header-right"></div>
        </div>

        <div class="section-title"><span class="section-number">2</span>Getting Started</div>

        <div class="subsection-title">2.1 Logging In</div>
        <ol class="steps">
            <li>Open your web browser and navigate to the school portal URL</li>
            <li>Enter your email address in the Email field</li>
            <li>Enter your password in the Password field</li>
            <li>Click the <span class="btn">Sign In</span> button</li>
            <li>You will be directed to the main dashboard</li>
        </ol>

        <div class="warning-box">
            Never share your login credentials with anyone. Always log out when leaving your workstation unattended.
        </div>

        <div class="subsection-title">2.2 Navigating to Accounts Module</div>
        <p>Access the accounting features through the sidebar menu:</p>

        <table>
            <tr>
                <th style="width: 35%;">Menu Item</th>
                <th>Function</th>
            </tr>
            <tr>
                <td><span class="menu-path">Accounts Dashboard</span></td>
                <td>Financial overview, quick stats, and summaries</td>
            </tr>
            <tr>
                <td><span class="menu-path">Chart of Accounts</span></td>
                <td>View and manage all financial accounts</td>
            </tr>
            <tr>
                <td><span class="menu-path">Bank Accounts</span></td>
                <td>Manage bank accounts and record transactions</td>
            </tr>
            <tr>
                <td><span class="menu-path">Expenses</span></td>
                <td>Record and track all expenses</td>
            </tr>
            <tr>
                <td><span class="menu-path">Income Records</span></td>
                <td>Record non-fee income</td>
            </tr>
            <tr>
                <td><span class="menu-path">Payment Vouchers</span></td>
                <td>Create and manage payment authorizations</td>
            </tr>
            <tr>
                <td><span class="menu-path">Journal Entries</span></td>
                <td>Manual double-entry transactions</td>
            </tr>
            <tr>
                <td><span class="menu-path">Vendors</span></td>
                <td>Manage supplier information</td>
            </tr>
            <tr>
                <td><span class="menu-path">Financial Reports</span></td>
                <td>Generate and export reports</td>
            </tr>
        </table>

        <div class="tip-box">
            The Accounts Dashboard provides a quick overview of your financial position. Check it daily to monitor income, expenses, and bank balances.
        </div>
    </div>

    {{-- Section 3: Chart of Accounts --}}
    <div class="page">
        <div class="page-header">
            <div class="page-header-left">
                @if($settings && $settings->school_logo)
                <img src="{{ public_path('storage/' . $settings->school_logo) }}" class="page-header-logo" alt="Logo">
                @endif
            </div>
            <div class="page-header-center">
                <div class="page-header-school">{{ $settings->school_name ?? 'St. Francis of Assisi Private School' }}</div>
                <div class="page-header-doc">Accountant User Guide</div>
            </div>
            <div class="page-header-right"></div>
        </div>

        <div class="section-title"><span class="section-number">3</span>Chart of Accounts</div>

        <div class="subsection-title">3.1 Account Structure</div>
        <p>Accounts are organized into five main categories following standard accounting principles:</p>

        <table>
            <tr>
                <th>Category</th>
                <th>Code Range</th>
                <th>Normal Balance</th>
                <th>Examples</th>
            </tr>
            <tr>
                <td><strong>Assets</strong></td>
                <td>1000-1999</td>
                <td>Debit</td>
                <td>Cash, Bank, Equipment</td>
            </tr>
            <tr>
                <td><strong>Liabilities</strong></td>
                <td>2000-2999</td>
                <td>Credit</td>
                <td>Accounts Payable, Loans</td>
            </tr>
            <tr>
                <td><strong>Equity</strong></td>
                <td>3000-3999</td>
                <td>Credit</td>
                <td>Capital, Retained Earnings</td>
            </tr>
            <tr>
                <td><strong>Revenue</strong></td>
                <td>4000-4999</td>
                <td>Credit</td>
                <td>Tuition Fees, Donations</td>
            </tr>
            <tr>
                <td><strong>Expenses</strong></td>
                <td>5000-5999</td>
                <td>Debit</td>
                <td>Salaries, Utilities, Supplies</td>
            </tr>
        </table>

        <div class="subsection-title">3.2 Creating a New Account</div>
        <ol class="steps">
            <li>Navigate to <span class="menu-path">Chart of Accounts</span> from the sidebar</li>
            <li>Click the <span class="btn">New Account</span> button</li>
            <li>Select the appropriate Account Category from the dropdown</li>
            <li>Enter a unique Account Code following the numbering convention</li>
            <li>Enter a descriptive Account Name</li>
            <li>Add a Description to clarify the account's purpose</li>
            <li>Ensure the Active toggle is enabled</li>
            <li>Click <span class="btn">Create</span> to save the new account</li>
        </ol>

        <div class="tip-box">
            Use meaningful account codes that group related accounts. For example, all salary expenses could use codes 5100-5199.
        </div>
    </div>

    {{-- Section 4: Bank Account Management --}}
    <div class="page">
        <div class="page-header">
            <div class="page-header-left">
                @if($settings && $settings->school_logo)
                <img src="{{ public_path('storage/' . $settings->school_logo) }}" class="page-header-logo" alt="Logo">
                @endif
            </div>
            <div class="page-header-center">
                <div class="page-header-school">{{ $settings->school_name ?? 'St. Francis of Assisi Private School' }}</div>
                <div class="page-header-doc">Accountant User Guide</div>
            </div>
            <div class="page-header-right"></div>
        </div>

        <div class="section-title"><span class="section-number">4</span>Bank Account Management</div>

        <div class="subsection-title">4.1 Adding a Bank Account</div>
        <ol class="steps">
            <li>Navigate to <span class="menu-path">Bank Accounts</span></li>
            <li>Click <span class="btn">New Bank Account</span></li>
            <li>Enter the Account Name (e.g., "Main Operating Account")</li>
            <li>Enter the Bank Name and Branch</li>
            <li>Enter the Account Number</li>
            <li>Select the Account Type (Checking, Savings, etc.)</li>
            <li>Enter the Opening Balance</li>
            <li>Link to the corresponding Chart of Account</li>
            <li>Click <span class="btn">Create</span> to save</li>
        </ol>

        <div class="subsection-title">4.2 Bank Transactions</div>
        <table>
            <tr>
                <th>Transaction Type</th>
                <th>Effect</th>
                <th>Examples</th>
            </tr>
            <tr>
                <td><strong>Deposit</strong></td>
                <td>Increases balance</td>
                <td>Fee payments, donations received</td>
            </tr>
            <tr>
                <td><strong>Withdrawal</strong></td>
                <td>Decreases balance</td>
                <td>Cash withdrawals, payments</td>
            </tr>
            <tr>
                <td><strong>Transfer</strong></td>
                <td>Moves between accounts</td>
                <td>Savings to checking</td>
            </tr>
            <tr>
                <td><strong>Bank Charges</strong></td>
                <td>Decreases balance</td>
                <td>Service fees, transaction fees</td>
            </tr>
        </table>

        <div class="subsection-title">4.3 Bank Reconciliation Process</div>
        <ol class="steps">
            <li>Obtain the bank statement for the reconciliation period</li>
            <li>Compare the closing balance with the system balance</li>
            <li>Identify outstanding checks not yet cleared</li>
            <li>Identify deposits in transit</li>
            <li>Record any bank charges or interest not in the system</li>
            <li>Document the reconciliation with explanations for differences</li>
        </ol>

        <div class="warning-box">
            Perform bank reconciliation monthly. Report any unexplained discrepancies to management immediately.
        </div>
    </div>

    {{-- Section 5: Expense Management --}}
    <div class="page">
        <div class="page-header">
            <div class="page-header-left">
                @if($settings && $settings->school_logo)
                <img src="{{ public_path('storage/' . $settings->school_logo) }}" class="page-header-logo" alt="Logo">
                @endif
            </div>
            <div class="page-header-center">
                <div class="page-header-school">{{ $settings->school_name ?? 'St. Francis of Assisi Private School' }}</div>
                <div class="page-header-doc">Accountant User Guide</div>
            </div>
            <div class="page-header-right"></div>
        </div>

        <div class="section-title"><span class="section-number">5</span>Expense Management</div>

        <div class="subsection-title">5.1 Recording an Expense</div>
        <ol class="steps">
            <li>Navigate to <span class="menu-path">Expenses</span> and click <span class="btn">New Expense</span></li>
            <li>The Expense Number is generated automatically</li>
            <li>Select the Expense Date</li>
            <li>Select or add the Vendor</li>
            <li>Choose the appropriate Expense Category</li>
            <li>Enter a clear Description of the expense</li>
            <li>Enter the Amount</li>
            <li>Set the Payment Status (Paid, Unpaid, or Partial)</li>
            <li>If paid, select Payment Method and Bank Account</li>
            <li>Attach supporting documents (receipts, invoices)</li>
            <li>Click <span class="btn">Create</span> to save</li>
        </ol>

        <div class="subsection-title">5.2 Expense Categories</div>
        <table>
            <tr>
                <th>Category</th>
                <th>Includes</th>
            </tr>
            <tr>
                <td><strong>Personnel</strong></td>
                <td>Salaries, NAPSA, NHIMA, Staff Welfare</td>
            </tr>
            <tr>
                <td><strong>Academic</strong></td>
                <td>Textbooks, Teaching Materials, Lab Supplies</td>
            </tr>
            <tr>
                <td><strong>Administrative</strong></td>
                <td>Stationery, Printing, Communication</td>
            </tr>
            <tr>
                <td><strong>Facilities</strong></td>
                <td>Utilities, Maintenance, Cleaning, Security</td>
            </tr>
            <tr>
                <td><strong>Transport</strong></td>
                <td>Fuel, Vehicle Maintenance, Insurance</td>
            </tr>
            <tr>
                <td><strong>Capital</strong></td>
                <td>Equipment, Furniture, Construction</td>
            </tr>
        </table>

        <div class="subsection-title">5.3 Vendor Management</div>
        <p>Maintain accurate vendor records for efficient expense tracking:</p>
        <ol class="steps">
            <li>Navigate to <span class="menu-path">Vendors</span></li>
            <li>Click <span class="btn">New Vendor</span></li>
            <li>Enter vendor details: Name, Contact Person, Phone, Email</li>
            <li>Add physical and postal addresses</li>
            <li>Enter Tax ID (TPIN) if applicable</li>
            <li>Set payment terms</li>
            <li>Click <span class="btn">Create</span> to save</li>
        </ol>
    </div>

    {{-- Section 6 & 7: Income and Payment Vouchers --}}
    <div class="page">
        <div class="page-header">
            <div class="page-header-left">
                @if($settings && $settings->school_logo)
                <img src="{{ public_path('storage/' . $settings->school_logo) }}" class="page-header-logo" alt="Logo">
                @endif
            </div>
            <div class="page-header-center">
                <div class="page-header-school">{{ $settings->school_name ?? 'St. Francis of Assisi Private School' }}</div>
                <div class="page-header-doc">Accountant User Guide</div>
            </div>
            <div class="page-header-right"></div>
        </div>

        <div class="section-title"><span class="section-number">6</span>Income Recording</div>

        <p>Record all non-fee income received by the school:</p>

        <ol class="steps">
            <li>Navigate to <span class="menu-path">Income Records</span></li>
            <li>Click <span class="btn">New Income Record</span></li>
            <li>Select the Income Date</li>
            <li>Choose the Income Category/Account</li>
            <li>Enter the Source (donor name, grant provider, etc.)</li>
            <li>Enter the Amount received</li>
            <li>Select the Bank Account where funds were deposited</li>
            <li>Add Reference Number (receipt, check number)</li>
            <li>Attach supporting documentation</li>
            <li>Click <span class="btn">Create</span> to save</li>
        </ol>

        <div class="info-box">
            <div class="info-box-title">School Fees Integration</div>
            <p>School fee payments are automatically recorded through the Fee Management module. You do not need to manually record fee payments in the accounting module.</p>
        </div>

        <div class="section-title" style="margin-top: 30px;"><span class="section-number">7</span>Payment Vouchers</div>

        <p>Payment vouchers provide formal authorization for payments and create an audit trail.</p>

        <div class="subsection-title">7.1 Creating a Payment Voucher</div>
        <ol class="steps">
            <li>Navigate to <span class="menu-path">Payment Vouchers</span></li>
            <li>Click <span class="btn">New Payment Voucher</span></li>
            <li>Voucher Number is auto-generated</li>
            <li>Select the Voucher Date and Payee</li>
            <li>Enter Payment Description and Amount</li>
            <li>Select Expense Account and Bank Account</li>
            <li>Choose Payment Method</li>
            <li>Attach supporting documents</li>
            <li>Submit for approval</li>
        </ol>

        <div class="subsection-title">7.2 Voucher Approval Status</div>
        <table>
            <tr>
                <th>Status</th>
                <th>Description</th>
            </tr>
            <tr>
                <td><strong>Draft</strong></td>
                <td>Newly created, not yet submitted</td>
            </tr>
            <tr>
                <td><strong>Pending</strong></td>
                <td>Awaiting approval</td>
            </tr>
            <tr>
                <td><strong>Approved</strong></td>
                <td>Authorized for payment</td>
            </tr>
            <tr>
                <td><strong>Paid</strong></td>
                <td>Payment completed</td>
            </tr>
            <tr>
                <td><strong>Rejected</strong></td>
                <td>Not approved - review required</td>
            </tr>
        </table>
    </div>

    {{-- Section 8: Journal Entries --}}
    <div class="page">
        <div class="page-header">
            <div class="page-header-left">
                @if($settings && $settings->school_logo)
                <img src="{{ public_path('storage/' . $settings->school_logo) }}" class="page-header-logo" alt="Logo">
                @endif
            </div>
            <div class="page-header-center">
                <div class="page-header-school">{{ $settings->school_name ?? 'St. Francis of Assisi Private School' }}</div>
                <div class="page-header-doc">Accountant User Guide</div>
            </div>
            <div class="page-header-right"></div>
        </div>

        <div class="section-title"><span class="section-number">8</span>Journal Entries</div>

        <p>Journal entries are the foundation of double-entry bookkeeping. Each entry must have equal debits and credits.</p>

        <div class="subsection-title">8.1 Creating a Journal Entry</div>
        <ol class="steps">
            <li>Navigate to <span class="menu-path">Journal Entries</span></li>
            <li>Click <span class="btn">New Journal Entry</span></li>
            <li>Entry Number is auto-generated</li>
            <li>Select the Entry Date</li>
            <li>Enter the Description/Narration</li>
            <li>Add line items with Account, Debit OR Credit amount</li>
            <li>Verify Total Debits = Total Credits</li>
            <li>Click <span class="btn">Create</span> to post</li>
        </ol>

        <div class="subsection-title">8.2 Example: Recording a Donation</div>
        <table>
            <tr>
                <th>Account</th>
                <th style="text-align: right;">Debit (ZMW)</th>
                <th style="text-align: right;">Credit (ZMW)</th>
            </tr>
            <tr>
                <td>1010 Bank - Main Account</td>
                <td style="text-align: right;">5,000.00</td>
                <td style="text-align: right;">-</td>
            </tr>
            <tr>
                <td>4200 Donations Received</td>
                <td style="text-align: right;">-</td>
                <td style="text-align: right;">5,000.00</td>
            </tr>
            <tr style="background: #1e40af; color: white;">
                <td><strong>TOTALS</strong></td>
                <td style="text-align: right;"><strong>5,000.00</strong></td>
                <td style="text-align: right;"><strong>5,000.00</strong></td>
            </tr>
        </table>

        <div class="subsection-title">8.3 Example: Recording Salary Payment</div>
        <table>
            <tr>
                <th>Account</th>
                <th style="text-align: right;">Debit (ZMW)</th>
                <th style="text-align: right;">Credit (ZMW)</th>
            </tr>
            <tr>
                <td>5101 Teacher Salaries</td>
                <td style="text-align: right;">50,000.00</td>
                <td style="text-align: right;">-</td>
            </tr>
            <tr>
                <td>2101 NAPSA Payable</td>
                <td style="text-align: right;">-</td>
                <td style="text-align: right;">2,500.00</td>
            </tr>
            <tr>
                <td>2102 PAYE Payable</td>
                <td style="text-align: right;">-</td>
                <td style="text-align: right;">5,000.00</td>
            </tr>
            <tr>
                <td>1010 Bank - Main Account</td>
                <td style="text-align: right;">-</td>
                <td style="text-align: right;">42,500.00</td>
            </tr>
            <tr style="background: #1e40af; color: white;">
                <td><strong>TOTALS</strong></td>
                <td style="text-align: right;"><strong>50,000.00</strong></td>
                <td style="text-align: right;"><strong>50,000.00</strong></td>
            </tr>
        </table>

        <div class="warning-box">
            Journal entries cannot be deleted once posted. To correct errors, create a reversing entry.
        </div>
    </div>

    {{-- Section 9: Financial Reports --}}
    <div class="page">
        <div class="page-header">
            <div class="page-header-left">
                @if($settings && $settings->school_logo)
                <img src="{{ public_path('storage/' . $settings->school_logo) }}" class="page-header-logo" alt="Logo">
                @endif
            </div>
            <div class="page-header-center">
                <div class="page-header-school">{{ $settings->school_name ?? 'St. Francis of Assisi Private School' }}</div>
                <div class="page-header-doc">Accountant User Guide</div>
            </div>
            <div class="page-header-right"></div>
        </div>

        <div class="section-title"><span class="section-number">9</span>Financial Reports</div>

        <div class="subsection-title">9.1 Available Reports</div>
        <table>
            <tr>
                <th style="width: 30%;">Report</th>
                <th>Description</th>
                <th style="width: 15%;">Frequency</th>
            </tr>
            <tr>
                <td><strong>Income & Expense Summary</strong></td>
                <td>Overview of income vs expenses with net income</td>
                <td>Monthly</td>
            </tr>
            <tr>
                <td><strong>Expense Detail</strong></td>
                <td>Breakdown of expenses by category</td>
                <td>Monthly</td>
            </tr>
            <tr>
                <td><strong>Income Detail</strong></td>
                <td>Breakdown of income by source</td>
                <td>Monthly</td>
            </tr>
            <tr>
                <td><strong>Cash Flow</strong></td>
                <td>Bank balances and monthly trends</td>
                <td>Weekly</td>
            </tr>
            <tr>
                <td><strong>Outstanding Payables</strong></td>
                <td>Unpaid vendor invoices</td>
                <td>Weekly</td>
            </tr>
        </table>

        <div class="subsection-title">9.2 Generating Reports</div>
        <ol class="steps">
            <li>Navigate to <span class="menu-path">Financial Reports</span></li>
            <li>Select the Report Type from the dropdown</li>
            <li>Choose the Date Range (Start and End Date)</li>
            <li>Click <span class="btn">Generate Report</span></li>
            <li>Review the report on screen</li>
        </ol>

        <div class="subsection-title">9.3 Exporting to PDF</div>
        <ol class="steps">
            <li>Generate the desired report</li>
            <li>Click the <span class="btn">Export PDF</span> button</li>
            <li>The PDF opens in a new tab or downloads</li>
            <li>Save or print as needed</li>
        </ol>

        <div class="tip-box">
            All PDF reports include the school logo and professional formatting suitable for management presentations and filing.
        </div>
    </div>

    {{-- Section 10: Daily & Monthly Procedures --}}
    <div class="page">
        <div class="page-header">
            <div class="page-header-left">
                @if($settings && $settings->school_logo)
                <img src="{{ public_path('storage/' . $settings->school_logo) }}" class="page-header-logo" alt="Logo">
                @endif
            </div>
            <div class="page-header-center">
                <div class="page-header-school">{{ $settings->school_name ?? 'St. Francis of Assisi Private School' }}</div>
                <div class="page-header-doc">Accountant User Guide</div>
            </div>
            <div class="page-header-right"></div>
        </div>

        <div class="section-title"><span class="section-number">10</span>Daily & Monthly Procedures</div>

        <div class="subsection-title">10.1 Daily Checklist</div>
        <ul class="checklist">
            <li>Log in and review the Accounts Dashboard</li>
            <li>Check pending payment vouchers requiring action</li>
            <li>Review new fee payments processed</li>
            <li>Record all expenses with supporting documents</li>
            <li>Process approved payment vouchers</li>
            <li>Record any non-fee income received</li>
            <li>File all receipts and documents</li>
            <li>Verify cash on hand matches records</li>
            <li>Log out securely at end of day</li>
        </ul>

        <div class="subsection-title">10.2 Monthly Checklist</div>
        <table>
            <tr>
                <th>Week</th>
                <th>Tasks</th>
            </tr>
            <tr>
                <td><strong>Week 1</strong></td>
                <td>
                    <ul style="margin: 0; padding-left: 15px;">
                        <li>Obtain bank statements</li>
                        <li>Perform bank reconciliation</li>
                        <li>Generate previous month reports</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td><strong>Week 2</strong></td>
                <td>
                    <ul style="margin: 0; padding-left: 15px;">
                        <li>Process payroll</li>
                        <li>Submit statutory deductions (NAPSA, PAYE)</li>
                        <li>Review expense classifications</li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td><strong>Week 3-4</strong></td>
                <td>
                    <ul style="margin: 0; padding-left: 15px;">
                        <li>Review outstanding payables</li>
                        <li>Prepare financial summary for management</li>
                        <li>Archive and backup records</li>
                    </ul>
                </td>
            </tr>
        </table>
    </div>

    {{-- Section 11: Troubleshooting --}}
    <div class="page">
        <div class="page-header">
            <div class="page-header-left">
                @if($settings && $settings->school_logo)
                <img src="{{ public_path('storage/' . $settings->school_logo) }}" class="page-header-logo" alt="Logo">
                @endif
            </div>
            <div class="page-header-center">
                <div class="page-header-school">{{ $settings->school_name ?? 'St. Francis of Assisi Private School' }}</div>
                <div class="page-header-doc">Accountant User Guide</div>
            </div>
            <div class="page-header-right"></div>
        </div>

        <div class="section-title"><span class="section-number">11</span>Troubleshooting & FAQs</div>

        <div class="subsection-title">11.1 Common Issues</div>
        <table>
            <tr>
                <th style="width: 35%;">Problem</th>
                <th>Solution</th>
            </tr>
            <tr>
                <td>Journal entry won't save</td>
                <td>Ensure total debits equal total credits and all required fields are completed</td>
            </tr>
            <tr>
                <td>Can't find expense category</td>
                <td>Check the Expense Categories list or request administrator to add a new category</td>
            </tr>
            <tr>
                <td>Bank balance doesn't match</td>
                <td>Perform reconciliation to identify outstanding items or unrecorded transactions</td>
            </tr>
            <tr>
                <td>Report shows wrong figures</td>
                <td>Verify date range selected and ensure all transactions are recorded</td>
            </tr>
            <tr>
                <td>Cannot delete a transaction</td>
                <td>Posted transactions cannot be deleted; create a reversing entry instead</td>
            </tr>
        </table>

        <div class="subsection-title">11.2 Frequently Asked Questions</div>

        <p><strong>Q: How do I correct a mistake in a posted entry?</strong></p>
        <p style="margin-bottom: 15px;">A: Create a reversing journal entry to cancel the original, then create a new correct entry.</p>

        <p><strong>Q: Can I backdate transactions?</strong></p>
        <p style="margin-bottom: 15px;">A: Yes, but use sparingly as it affects previously generated reports. Document the reason.</p>

        <p><strong>Q: How are school fees tracked?</strong></p>
        <p style="margin-bottom: 15px;">A: School fees are managed in the Fee Management module and automatically integrate with accounting.</p>

        <p><strong>Q: How long should financial records be kept?</strong></p>
        <p style="margin-bottom: 15px;">A: Maintain all financial records for a minimum of 7 years as per regulatory requirements.</p>

        <div class="subsection-title">11.3 Getting Help</div>
        <p>If you encounter issues not covered in this guide:</p>
        <ol>
            <li>Check the system help documentation</li>
            <li>Contact the IT Administrator</li>
            <li>Consult with the Bursar or Finance Manager</li>
        </ol>

        {{-- End Section --}}
        <div class="end-page" style="margin-top: 40px;">
            @if($settings && $settings->school_logo)
            <img src="{{ public_path('storage/' . $settings->school_logo) }}" class="end-logo" alt="Logo">
            @endif
            <div class="end-school">{{ $settings->school_name ?? 'St. Francis of Assisi Private School' }}</div>
            <div class="end-contact">
                {{ $settings->address ?? '' }}<br>
                Tel: {{ $settings->phone ?? '' }} | Email: {{ $settings->email ?? '' }}
            </div>
            <div class="end-motto">"{{ $settings->school_motto ?? 'For God and Country' }}"</div>
        </div>
    </div>
</body>
</html>
