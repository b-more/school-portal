<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Accounts Module User Guide</title>
    <style>
        @page {
            margin: 2.5cm 2cm 2.5cm 3cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #333;
        }

        .cover-page {
            text-align: center;
            padding-top: 100px;
        }

        .school-name {
            font-size: 22pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 40px;
        }

        .doc-title {
            font-size: 32pt;
            font-weight: bold;
            color: #333;
        }

        .doc-title-blue {
            font-size: 32pt;
            font-weight: bold;
            color: #1e40af;
        }

        .subtitle {
            font-size: 14pt;
            color: #666;
            font-style: italic;
            margin-top: 20px;
        }

        .info-box {
            background: #e3f2fd;
            border: 2px solid #1e40af;
            padding: 15px;
            margin: 40px auto;
            width: 300px;
            text-align: center;
        }

        .confidential {
            color: #cc0000;
            font-weight: bold;
            margin-top: 60px;
        }

        .page-break {
            page-break-after: always;
        }

        h1 {
            font-size: 18pt;
            color: #1e40af;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 5px;
            margin-top: 30px;
        }

        h2 {
            font-size: 14pt;
            color: #1e40af;
            margin-top: 20px;
        }

        h3 {
            font-size: 12pt;
            color: #333;
            margin-top: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 10pt;
        }

        th {
            background: #1e40af;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        td {
            padding: 8px;
            border: 1px solid #ccc;
        }

        tr:nth-child(even) td {
            background: #f5f5f5;
        }

        .step-num {
            color: #1e40af;
            font-weight: bold;
        }

        .note-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px 15px;
            margin: 15px 0;
        }

        .note-box-title {
            font-weight: bold;
            color: #856404;
        }

        .tip-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 10px 15px;
            margin: 15px 0;
        }

        .tip-box-title {
            font-weight: bold;
            color: #155724;
        }

        .info-box-blue {
            background: #e3f2fd;
            border-left: 4px solid #1e40af;
            padding: 10px 15px;
            margin: 15px 0;
        }

        .diagram-box {
            background: #e3f2fd;
            border: 2px solid #1e40af;
            padding: 20px;
            text-align: center;
            margin: 15px 0;
        }

        .diagram-title {
            font-weight: bold;
            color: #1e40af;
            font-size: 12pt;
        }

        ul, ol {
            margin: 10px 0;
            padding-left: 25px;
        }

        li {
            margin-bottom: 5px;
        }

        .checkbox {
            color: #666;
        }

        .toc-item {
            margin: 8px 0;
        }

        .toc-num {
            display: inline-block;
            width: 30px;
            font-weight: bold;
            color: #1e40af;
        }

        .footer {
            text-align: center;
            color: #999;
            font-size: 9pt;
            margin-top: 40px;
        }

        .faq-q {
            font-weight: bold;
            color: #1e40af;
            margin-top: 15px;
        }

        .faq-a {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

{{-- COVER PAGE --}}
<div class="cover-page">
    <div class="school-name">{{ strtoupper($settings->school_name ?? 'ST. FRANCIS OF ASSISI PRIVATE SCHOOL') }}</div>

    <div class="doc-title">ACCOUNTS MODULE</div>
    <div class="doc-title-blue">USER GUIDE</div>

    <div class="subtitle">Including Student Fees Integration</div>

    <div class="info-box">
        <div>Document Version: 1.0</div>
        <div>Date: {{ $generatedAt->format('d F Y') }}</div>
        <div><strong>Status: OFFICIAL</strong></div>
    </div>

    <div class="confidential">CONFIDENTIAL - For Authorized Personnel Only</div>
</div>

<div class="page-break"></div>

{{-- TABLE OF CONTENTS --}}
<h1>Table of Contents</h1>

<div class="toc-item"><span class="toc-num">1.</span> Introduction to the Accounts Module</div>
<div class="toc-item"><span class="toc-num">2.</span> System Overview and Architecture</div>
<div class="toc-item"><span class="toc-num">3.</span> Student Fees and Accounts Integration</div>
<div class="toc-item"><span class="toc-num">4.</span> Chart of Accounts</div>
<div class="toc-item"><span class="toc-num">5.</span> Bank Account Management</div>
<div class="toc-item"><span class="toc-num">6.</span> Expense Management</div>
<div class="toc-item"><span class="toc-num">7.</span> Income Recording</div>
<div class="toc-item"><span class="toc-num">8.</span> Payment Vouchers</div>
<div class="toc-item"><span class="toc-num">9.</span> Bank Reconciliation</div>
<div class="toc-item"><span class="toc-num">10.</span> Financial Reports</div>
<div class="toc-item"><span class="toc-num">11.</span> Security and Access Control</div>
<div class="toc-item"><span class="toc-num">12.</span> Daily and Monthly Procedures</div>
<div class="toc-item"><span class="toc-num">13.</span> Frequently Asked Questions (FAQ)</div>
<div class="toc-item"><span class="toc-num">14.</span> Troubleshooting Guide</div>
<div class="toc-item"><span class="toc-num">15.</span> Quick Reference</div>

<div class="page-break"></div>

{{-- SECTION 1: INTRODUCTION --}}
<h1>1. Introduction to the Accounts Module</h1>

<p>The Accounts Module is the central financial management system for {{ $settings->school_name ?? 'St. Francis of Assisi Private School' }}. It provides comprehensive tools for managing all school finances, from daily transactions to complex financial reporting.</p>

<h2>1.1 Purpose</h2>
<p>The Accounts Module serves the following purposes:</p>
<ul>
    <li>Record and track all financial transactions</li>
    <li>Integrate with Student Fees for automatic income recording</li>
    <li>Manage multiple bank accounts</li>
    <li>Process and approve payment vouchers</li>
    <li>Generate financial reports for management and auditors</li>
    <li>Maintain complete audit trails for compliance</li>
</ul>

<h2>1.2 Key Benefits</h2>
<table>
    <tr><th>Benefit</th><th>Description</th></tr>
    <tr><td><strong>Automation</strong></td><td>Fee payments automatically create income records and journal entries</td></tr>
    <tr><td><strong>Accuracy</strong></td><td>Double-entry accounting ensures balanced books</td></tr>
    <tr><td><strong>Transparency</strong></td><td>Complete audit trail for every transaction</td></tr>
    <tr><td><strong>Efficiency</strong></td><td>Streamlined workflows reduce manual work</td></tr>
    <tr><td><strong>Reporting</strong></td><td>Real-time financial reports at your fingertips</td></tr>
</table>

<div class="page-break"></div>

{{-- SECTION 2: SYSTEM OVERVIEW --}}
<h1>2. System Overview and Architecture</h1>

<p>The Accounts Module is built on a modular architecture that integrates seamlessly with other school management modules, particularly the Student Fees system.</p>

<h2>2.1 System Components</h2>

<div class="diagram-box">
    <div class="diagram-title">SYSTEM ARCHITECTURE</div>
    <br>
    <strong>Student Fees Module</strong><br>
    ↓<br>
    <strong>Accounts Module (Core)</strong><br>
    ↙ &nbsp;&nbsp;&nbsp;&nbsp; ↓ &nbsp;&nbsp;&nbsp;&nbsp; ↘<br>
    Expenses &nbsp;&nbsp; Bank Accounts &nbsp;&nbsp; Income<br>
    ↓<br>
    <strong>Financial Reports</strong>
</div>

<h2>2.2 Module Integration Points</h2>
<table>
    <tr><th>Module</th><th>Integration Type</th><th>Data Flow</th></tr>
    <tr><td><strong>Student Fees</strong></td><td>Automatic</td><td>Fee payments → Income records → Journal entries</td></tr>
    <tr><td><strong>HR/Payroll</strong></td><td>Semi-automatic</td><td>Salary data → Expense records → Payment vouchers</td></tr>
    <tr><td><strong>Inventory</strong></td><td>Manual</td><td>Purchase orders → Expenses → Payments</td></tr>
    <tr><td><strong>Reporting</strong></td><td>Real-time</td><td>All transactions → Financial statements</td></tr>
</table>

<div class="page-break"></div>

{{-- SECTION 3: STUDENT FEES INTEGRATION --}}
<h1>3. Student Fees and Accounts Integration</h1>

<div class="note-box">
    <div class="note-box-title">IMPORTANT: This is a key integration point</div>
    Student fee payments are the primary source of school income. Understanding how fees flow into the accounts system is essential for accurate financial management.
</div>

<h2>3.1 How Fee Payments Flow to Accounts</h2>
<p>When a student fee payment is recorded, the following automatic processes occur:</p>

<table>
    <tr><th>Step</th><th>Action</th><th>Description</th></tr>
    <tr><td><strong>Step 1</strong></td><td>Payment Entry</td><td>Bursar/Cashier enters fee payment in Student Fees module</td></tr>
    <tr><td><strong>Step 2</strong></td><td>Validation</td><td>System validates student, fee type, and amount</td></tr>
    <tr><td><strong>Step 3</strong></td><td>Receipt Generation</td><td>Fee receipt is generated for the parent/student</td></tr>
    <tr><td><strong>Step 4</strong></td><td>Income Record</td><td>AUTOMATIC: Income record created in Accounts module</td></tr>
    <tr><td><strong>Step 5</strong></td><td>Journal Entry</td><td>AUTOMATIC: Double-entry journal created (Debit: Bank, Credit: Fee Income)</td></tr>
    <tr><td><strong>Step 6</strong></td><td>Bank Balance Update</td><td>Bank account balance updated in real-time</td></tr>
    <tr><td><strong>Step 7</strong></td><td>Reports Updated</td><td>All financial reports reflect the new payment</td></tr>
</table>

<h2>3.2 Fee Categories and Account Mapping</h2>
<p>Each fee category is mapped to a specific income account in the Chart of Accounts:</p>

<table>
    <tr><th>Fee Category</th><th>Account Code</th><th>Account Name</th><th>Type</th></tr>
    <tr><td>Tuition Fees</td><td><strong>4010</strong></td><td>Tuition Income</td><td>Revenue</td></tr>
    <tr><td>Boarding Fees</td><td><strong>4020</strong></td><td>Boarding Income</td><td>Revenue</td></tr>
    <tr><td>Transport Fees</td><td><strong>4030</strong></td><td>Transport Income</td><td>Revenue</td></tr>
    <tr><td>Lunch Fees</td><td><strong>4040</strong></td><td>Lunch Program Income</td><td>Revenue</td></tr>
    <tr><td>Uniform Sales</td><td><strong>4050</strong></td><td>Uniform Sales</td><td>Revenue</td></tr>
    <tr><td>Exam Fees</td><td><strong>4060</strong></td><td>Examination Fees</td><td>Revenue</td></tr>
    <tr><td>Registration</td><td><strong>4070</strong></td><td>Registration Income</td><td>Revenue</td></tr>
    <tr><td>Other Fees</td><td><strong>4090</strong></td><td>Miscellaneous Income</td><td>Revenue</td></tr>
</table>

<h2>3.3 Journal Entry Example</h2>
<p>When a student pays K5,000 tuition via bank deposit:</p>

<table>
    <tr><th>Account</th><th>Debit (ZMW)</th><th>Credit (ZMW)</th></tr>
    <tr><td>1020 - Bank Account</td><td>5,000.00</td><td>-</td></tr>
    <tr><td>4010 - Tuition Income</td><td>-</td><td>5,000.00</td></tr>
    <tr style="background: #e3f2fd;"><td><strong>TOTAL</strong></td><td><strong>5,000.00</strong></td><td><strong>5,000.00</strong></td></tr>
</table>

<div class="tip-box">
    <div class="tip-box-title">TIP: Automatic vs Manual</div>
    Fee payments create automatic journal entries. Manual income (donations, grants) must be recorded separately through the Income module. Both appear in financial reports.
</div>

<div class="page-break"></div>

{{-- SECTION 4: CHART OF ACCOUNTS --}}
<h1>4. Chart of Accounts</h1>

<p>The Chart of Accounts is the foundation of the accounting system. It organizes all financial accounts into categories following standard accounting principles.</p>

<h2>4.1 Account Categories</h2>
<table>
    <tr><th>Code Range</th><th>Category</th><th>Normal Balance</th><th>Examples</th></tr>
    <tr><td><strong>1000s</strong></td><td>Assets</td><td>Debit</td><td>Cash, Bank, Receivables, Equipment</td></tr>
    <tr><td><strong>2000s</strong></td><td>Liabilities</td><td>Credit</td><td>Payables, Loans, Accrued Expenses</td></tr>
    <tr><td><strong>3000s</strong></td><td>Equity</td><td>Credit</td><td>Capital, Retained Earnings</td></tr>
    <tr><td><strong>4000s</strong></td><td>Revenue</td><td>Credit</td><td>Fees, Donations, Grants</td></tr>
    <tr><td><strong>5000s</strong></td><td>Expenses</td><td>Debit</td><td>Salaries, Utilities, Supplies</td></tr>
</table>

<h2>4.2 Standard Account List</h2>
<table>
    <tr><th>Code</th><th>Account Name</th><th>Type</th><th>Balance</th></tr>
    <tr><td><strong>1010</strong></td><td>Petty Cash</td><td>Asset</td><td>Debit</td></tr>
    <tr><td><strong>1020</strong></td><td>Main Bank Account</td><td>Asset</td><td>Debit</td></tr>
    <tr><td><strong>1030</strong></td><td>Payroll Bank Account</td><td>Asset</td><td>Debit</td></tr>
    <tr><td><strong>1100</strong></td><td>Accounts Receivable - Students</td><td>Asset</td><td>Debit</td></tr>
    <tr><td><strong>1500</strong></td><td>Fixed Assets - Buildings</td><td>Asset</td><td>Debit</td></tr>
    <tr><td><strong>2010</strong></td><td>Accounts Payable</td><td>Liability</td><td>Credit</td></tr>
    <tr><td><strong>2100</strong></td><td>PAYE Payable</td><td>Liability</td><td>Credit</td></tr>
    <tr><td><strong>2110</strong></td><td>NAPSA Payable</td><td>Liability</td><td>Credit</td></tr>
    <tr><td><strong>3000</strong></td><td>School Capital</td><td>Equity</td><td>Credit</td></tr>
    <tr><td><strong>4010</strong></td><td>Tuition Income</td><td>Revenue</td><td>Credit</td></tr>
    <tr><td><strong>4020</strong></td><td>Boarding Income</td><td>Revenue</td><td>Credit</td></tr>
    <tr><td><strong>5010</strong></td><td>Teacher Salaries</td><td>Expense</td><td>Debit</td></tr>
    <tr><td><strong>5100</strong></td><td>Electricity Expense</td><td>Expense</td><td>Debit</td></tr>
    <tr><td><strong>5110</strong></td><td>Water Expense</td><td>Expense</td><td>Debit</td></tr>
</table>

<div class="page-break"></div>

{{-- SECTION 5: BANK ACCOUNT MANAGEMENT --}}
<h1>5. Bank Account Management</h1>

<p>The system supports multiple bank accounts, allowing you to track all financial activities across different banks and account types.</p>

<h2>5.1 Setting Up a Bank Account</h2>
<ol>
    <li>Navigate to Accounts → Bank Accounts</li>
    <li>Click "New Bank Account"</li>
    <li>Enter bank name (e.g., "Zanaco", "Stanbic")</li>
    <li>Enter account number</li>
    <li>Select account type (Current/Savings)</li>
    <li>Enter opening balance</li>
    <li>Check "Default" if this is the primary operating account</li>
    <li>Link to Chart of Accounts entry (e.g., 1020 - Main Bank Account)</li>
    <li>Click "Save"</li>
</ol>

<h2>5.2 Bank Transaction Types</h2>
<table>
    <tr><th>Transaction</th><th>Description</th><th>Balance Effect</th></tr>
    <tr><td><strong>Deposit</strong></td><td>Money received into account</td><td>Increases balance (+)</td></tr>
    <tr><td><strong>Withdrawal</strong></td><td>Money taken from account</td><td>Decreases balance (-)</td></tr>
    <tr><td><strong>Transfer Out</strong></td><td>Money sent to another account</td><td>Decreases balance (-)</td></tr>
    <tr><td><strong>Transfer In</strong></td><td>Money received from another account</td><td>Increases balance (+)</td></tr>
    <tr><td><strong>Bank Charge</strong></td><td>Fees deducted by bank</td><td>Decreases balance (-)</td></tr>
    <tr><td><strong>Interest</strong></td><td>Interest earned on account</td><td>Increases balance (+)</td></tr>
</table>

<div class="page-break"></div>

{{-- SECTION 6: EXPENSE MANAGEMENT --}}
<h1>6. Expense Management</h1>

<h2>6.1 Recording an Expense</h2>
<ol>
    <li>Navigate to Accounts → Expenses</li>
    <li>Click "New Expense"</li>
    <li>Enter or accept auto-generated expense number</li>
    <li>Select expense date</li>
    <li>Choose expense category (Utilities, Supplies, etc.)</li>
    <li>Select vendor or add new</li>
    <li>Enter description of goods/services</li>
    <li>Enter amount</li>
    <li>Attach supporting documents (invoice, receipt)</li>
    <li>Set payment status (Paid/Partially Paid/Unpaid)</li>
    <li>Click "Save"</li>
</ol>

<h2>6.2 Expense Categories</h2>
<table>
    <tr><th>Category</th><th>Examples</th></tr>
    <tr><td><strong>Personnel</strong></td><td>Salaries, allowances, benefits, training</td></tr>
    <tr><td><strong>Facilities</strong></td><td>Rent, electricity, water, maintenance, repairs</td></tr>
    <tr><td><strong>Academic</strong></td><td>Textbooks, lab supplies, teaching materials</td></tr>
    <tr><td><strong>Administrative</strong></td><td>Office supplies, printing, postage, communication</td></tr>
    <tr><td><strong>Transport</strong></td><td>Fuel, vehicle maintenance, driver allowances</td></tr>
    <tr><td><strong>Food Services</strong></td><td>Food supplies, kitchen equipment, catering</td></tr>
    <tr><td><strong>Professional</strong></td><td>Audit fees, legal fees, consulting</td></tr>
    <tr><td><strong>Capital</strong></td><td>Furniture, computers, building improvements</td></tr>
</table>

<h2>6.3 Expense Approval Limits</h2>
<table>
    <tr><th>Amount (ZMW)</th><th>Approval Authority</th><th>Documentation</th></tr>
    <tr><td><strong>0 - 1,000</strong></td><td>Accountant</td><td>Receipt only</td></tr>
    <tr><td><strong>1,001 - 5,000</strong></td><td>Senior Accountant</td><td>Invoice + Receipt</td></tr>
    <tr><td><strong>5,001 - 20,000</strong></td><td>Finance Manager</td><td>2 Quotations + Invoice</td></tr>
    <tr><td><strong>20,001 - 50,000</strong></td><td>School Director</td><td>3 Quotations + Invoice</td></tr>
    <tr><td><strong>Above 50,000</strong></td><td>Board of Directors</td><td>Tender process required</td></tr>
</table>

<div class="page-break"></div>

{{-- SECTION 7: INCOME RECORDING --}}
<h1>7. Income Recording</h1>

<p>Income is recorded from two sources: automatic fee payments and manual income entries.</p>

<h2>7.1 Automatic Income (From Fees)</h2>
<p>Student fee payments are automatically recorded. No manual entry required. The system:</p>
<ul>
    <li>Creates income record linked to the student</li>
    <li>Generates journal entry (Debit: Bank, Credit: Income)</li>
    <li>Updates bank balance in real-time</li>
    <li>Tags income with fee category for reporting</li>
</ul>

<h2>7.2 Manual Income Entry</h2>
<p>For non-fee income (donations, grants, facility rentals):</p>
<ol>
    <li>Navigate to Accounts → Income Records</li>
    <li>Click "New Income"</li>
    <li>Select income category</li>
    <li>Enter received date</li>
    <li>Enter amount</li>
    <li>Select payment method (Cash/Bank/Mobile Money)</li>
    <li>Enter reference number</li>
    <li>Add description or notes</li>
    <li>Attach supporting documents if applicable</li>
    <li>Click "Save"</li>
</ol>

<div class="page-break"></div>

{{-- SECTION 8: PAYMENT VOUCHERS --}}
<h1>8. Payment Vouchers</h1>

<p>Payment vouchers provide formal authorization for payments. They ensure proper approval, documentation, and audit trail for every financial outflow.</p>

<h2>8.1 Creating a Payment Voucher</h2>
<ol>
    <li>Navigate to Accounts → Payment Vouchers</li>
    <li>Click "New Payment Voucher"</li>
    <li>System auto-generates voucher number (e.g., PV-2024-0001)</li>
    <li>Select payee (vendor or individual)</li>
    <li>Enter payment purpose/description</li>
    <li>Add line items with amounts</li>
    <li>Attach supporting documents (invoice, quotations)</li>
    <li>Select bank account for payment</li>
    <li>Choose payment method (Cheque/Bank Transfer/Cash)</li>
    <li>Submit for approval</li>
</ol>

<h2>8.2 Voucher Approval Workflow</h2>
<div class="diagram-box">
    <div class="diagram-title">PAYMENT VOUCHER WORKFLOW</div>
    <br>
    Create → Submit → Verify → Approve → Process Payment → Mark Paid<br>
    <small>(Accountant) → (Sr. Accountant) → (Finance Mgr) → (Cashier) → (Accountant)</small>
</div>

<h2>8.3 Voucher Status</h2>
<table>
    <tr><th>Status</th><th>Description</th><th>Next Action</th></tr>
    <tr><td><strong>Draft</strong></td><td>Voucher being prepared</td><td>Complete and submit</td></tr>
    <tr><td><strong>Pending</strong></td><td>Awaiting approval</td><td>Approver reviews</td></tr>
    <tr><td><strong>Approved</strong></td><td>Ready for payment</td><td>Process payment</td></tr>
    <tr><td><strong>Paid</strong></td><td>Payment completed</td><td>Archive</td></tr>
    <tr><td><strong>Rejected</strong></td><td>Approval denied</td><td>Review and revise</td></tr>
</table>

<div class="page-break"></div>

{{-- SECTION 9: BANK RECONCILIATION --}}
<h1>9. Bank Reconciliation</h1>

<p>Bank reconciliation ensures your system records match the bank statement. This critical process should be performed monthly for each bank account.</p>

<h2>9.1 Reconciliation Steps</h2>
<ol>
    <li>Obtain bank statement for the period</li>
    <li>Navigate to Accounts → Bank Reconciliation</li>
    <li>Select bank account to reconcile</li>
    <li>Enter statement ending date</li>
    <li>Enter statement ending balance</li>
    <li>Review system transactions for the period</li>
    <li>Match each transaction with statement entries</li>
    <li>Identify deposits in transit (in system, not on statement)</li>
    <li>Identify outstanding cheques (issued but not cleared)</li>
    <li>Record any bank charges or interest not in system</li>
    <li>Verify reconciled balance matches statement</li>
    <li>Complete and save reconciliation</li>
    <li>Print reconciliation report</li>
</ol>

<h2>9.2 Common Reconciling Items</h2>
<table>
    <tr><th>Item</th><th>Description</th><th>Action</th></tr>
    <tr><td><strong>Deposits in Transit</strong></td><td>Recorded but not yet in bank</td><td>Will clear next period</td></tr>
    <tr><td><strong>Outstanding Cheques</strong></td><td>Issued but not yet cashed</td><td>Monitor for stale cheques</td></tr>
    <tr><td><strong>Bank Charges</strong></td><td>Fees deducted by bank</td><td>Record as expense</td></tr>
    <tr><td><strong>Interest Earned</strong></td><td>Interest credited by bank</td><td>Record as income</td></tr>
    <tr><td><strong>Direct Debits</strong></td><td>Automatic payments</td><td>Verify and record</td></tr>
    <tr><td><strong>Errors</strong></td><td>Recording mistakes</td><td>Correct immediately</td></tr>
</table>

<div class="page-break"></div>

{{-- SECTION 10: FINANCIAL REPORTS --}}
<h1>10. Financial Reports</h1>

<h2>10.1 Available Reports</h2>
<table>
    <tr><th>Report</th><th>Purpose</th><th>Frequency</th></tr>
    <tr><td><strong>Income & Expense</strong></td><td>Summary of all revenue and costs</td><td>Monthly</td></tr>
    <tr><td><strong>Cash Flow Statement</strong></td><td>Money movements in/out</td><td>Weekly/Monthly</td></tr>
    <tr><td><strong>Expense by Category</strong></td><td>Detailed expense breakdown</td><td>Monthly</td></tr>
    <tr><td><strong>Income by Category</strong></td><td>Detailed income breakdown</td><td>Monthly</td></tr>
    <tr><td><strong>Outstanding Payables</strong></td><td>Unpaid vendor amounts</td><td>Weekly</td></tr>
    <tr><td><strong>Trial Balance</strong></td><td>Account balances verification</td><td>Monthly</td></tr>
    <tr><td><strong>Fee Collection</strong></td><td>Student fee payments summary</td><td>Daily/Weekly</td></tr>
</table>

<h2>10.2 Generating Reports</h2>
<ol>
    <li>Navigate to Accounts → Financial Reports</li>
    <li>Select report type from dropdown</li>
    <li>Choose date range (start and end dates)</li>
    <li>Apply additional filters if needed</li>
    <li>Click "Generate Report"</li>
    <li>Review on screen</li>
    <li>Click "Export PDF" to download</li>
</ol>

<div class="page-break"></div>

{{-- SECTION 11: SECURITY --}}
<h1>11. Security and Access Control</h1>

<h2>11.1 User Roles</h2>
<table>
    <tr><th>Role</th><th>Permissions</th></tr>
    <tr><td><strong>Accountant</strong></td><td>Record transactions, create vouchers, generate reports, bank reconciliation</td></tr>
    <tr><td><strong>Sr. Accountant</strong></td><td>All accountant permissions + approve vouchers up to K10,000 + manage vendors</td></tr>
    <tr><td><strong>Finance Manager</strong></td><td>All permissions + approve all vouchers + modify Chart of Accounts + user management</td></tr>
    <tr><td><strong>Auditor</strong></td><td>View-only access to all records, reports, and audit logs</td></tr>
    <tr><td><strong>Director</strong></td><td>High-level approval authority + dashboard access + all reports</td></tr>
</table>

<h2>11.2 Security Best Practices</h2>
<ul>
    <li><span class="checkbox">&#9744;</span> Never share your login credentials with anyone</li>
    <li><span class="checkbox">&#9744;</span> Change your password every 90 days</li>
    <li><span class="checkbox">&#9744;</span> Use strong passwords (12+ characters, mixed case, numbers, symbols)</li>
    <li><span class="checkbox">&#9744;</span> Log out when leaving your workstation</li>
    <li><span class="checkbox">&#9744;</span> Report suspicious activity immediately</li>
    <li><span class="checkbox">&#9744;</span> Do not access the system from public computers</li>
    <li><span class="checkbox">&#9744;</span> Verify requests for payment changes via phone call</li>
</ul>

<div class="page-break"></div>

{{-- SECTION 12: PROCEDURES --}}
<h1>12. Daily and Monthly Procedures</h1>

<h2>12.1 Daily Checklist</h2>
<ul>
    <li><span class="checkbox">&#9744;</span> Review dashboard for financial overview</li>
    <li><span class="checkbox">&#9744;</span> Process pending payment vouchers</li>
    <li><span class="checkbox">&#9744;</span> Record all cash receipts</li>
    <li><span class="checkbox">&#9744;</span> Verify fee payments have been recorded</li>
    <li><span class="checkbox">&#9744;</span> Check bank account balances</li>
    <li><span class="checkbox">&#9744;</span> Respond to any approval requests</li>
    <li><span class="checkbox">&#9744;</span> Back up critical documents</li>
</ul>

<h2>12.2 Weekly Checklist</h2>
<ul>
    <li><span class="checkbox">&#9744;</span> Generate cash flow report</li>
    <li><span class="checkbox">&#9744;</span> Review outstanding payables</li>
    <li><span class="checkbox">&#9744;</span> Follow up on pending collections</li>
    <li><span class="checkbox">&#9744;</span> Verify petty cash balance</li>
    <li><span class="checkbox">&#9744;</span> Review expense documentation</li>
    <li><span class="checkbox">&#9744;</span> Check for any reconciliation items</li>
</ul>

<h2>12.3 Monthly Checklist</h2>
<ul>
    <li><span class="checkbox">&#9744;</span> Complete bank reconciliation for all accounts</li>
    <li><span class="checkbox">&#9744;</span> Generate monthly financial reports</li>
    <li><span class="checkbox">&#9744;</span> Review budget vs actual spending</li>
    <li><span class="checkbox">&#9744;</span> Process payroll-related expenses</li>
    <li><span class="checkbox">&#9744;</span> Submit statutory returns (PAYE, NAPSA)</li>
    <li><span class="checkbox">&#9744;</span> Archive monthly records</li>
    <li><span class="checkbox">&#9744;</span> Submit reports to management</li>
</ul>

<div class="page-break"></div>

{{-- SECTION 13: FAQ --}}
<h1>13. Frequently Asked Questions (FAQ)</h1>

<div class="faq-q">Q: How do I correct a wrongly recorded transaction?</div>
<div class="faq-a">A: Do not delete the transaction. Instead, create a reversing entry with the same amount but opposite effect (debit becomes credit). Then record the correct transaction. This maintains audit trail integrity.</div>

<div class="faq-q">Q: Why are fee payments not showing in my income reports?</div>
<div class="faq-a">A: Fee payments are automatically recorded when processed through the Student Fees module. Check: (1) The payment was saved successfully, (2) The date range of your report includes the payment date, (3) The fee category is included in your filter.</div>

<div class="faq-q">Q: Can I edit an approved payment voucher?</div>
<div class="faq-a">A: No. Once approved, vouchers cannot be edited to maintain audit integrity. If changes are needed, the voucher must be cancelled and a new one created with correct details.</div>

<div class="faq-q">Q: How do I handle a bounced cheque?</div>
<div class="faq-a">A: Record a bank transaction for the reversal (reduces bank balance). Create an expense for any bank charges. Update the original income record status to "Bounced". Contact the payer for replacement payment.</div>

<div class="faq-q">Q: What if my bank reconciliation does not balance?</div>
<div class="faq-a">A: Check for: (1) Missing transactions, (2) Transactions recorded twice, (3) Wrong amounts, (4) Bank charges not recorded, (5) Deposits in transit, (6) Outstanding cheques. Document all differences and investigate each one.</div>

<div class="faq-q">Q: How do I handle cash payments for expenses?</div>
<div class="faq-a">A: Cash payments should be made from petty cash. Record the expense and link it to the petty cash account instead of the main bank account. Replenish petty cash when it runs low.</div>

<div class="faq-q">Q: Can I generate reports for previous years?</div>
<div class="faq-a">A: Yes. Select the appropriate date range when generating reports. Historical data is maintained indefinitely. For archived periods, reports may take longer to generate.</div>

<div class="faq-q">Q: How are refunds processed?</div>
<div class="faq-a">A: Refunds are recorded as negative income or as expenses depending on school policy. Create a payment voucher for the refund amount, get approval, and process payment to the recipient.</div>

<div class="faq-q">Q: What happens if the system goes down during a transaction?</div>
<div class="faq-a">A: Incomplete transactions are automatically rolled back. Check if the transaction was saved by looking at the relevant list. If not saved, re-enter the transaction.</div>

<div class="faq-q">Q: How do I add a new expense category?</div>
<div class="faq-a">A: Only the Finance Manager can add new expense categories. Navigate to Accounts → Settings → Expense Categories. Click "Add New", enter the category name and linked account code, then save.</div>

<div class="faq-q">Q: Why can I not approve my own payment voucher?</div>
<div class="faq-a">A: This is a security control called "segregation of duties". The person who creates a voucher cannot approve it to prevent fraud. Someone with higher authority must approve.</div>

<div class="faq-q">Q: How long are records kept in the system?</div>
<div class="faq-a">A: All financial records are retained for a minimum of 7 years as required by law. Transaction logs and audit trails are kept permanently.</div>

<div class="page-break"></div>

{{-- SECTION 14: TROUBLESHOOTING --}}
<h1>14. Troubleshooting Guide</h1>

<table>
    <tr><th>Problem</th><th>Solution</th></tr>
    <tr><td><strong>Cannot login</strong></td><td>Check username/password. Use "Forgot Password" if needed. Contact IT after 3 failed attempts.</td></tr>
    <tr><td><strong>Page not loading</strong></td><td>Refresh page (F5). Clear browser cache. Try different browser. Check internet connection.</td></tr>
    <tr><td><strong>Transaction not saving</strong></td><td>Check all required fields are filled. Look for red error messages. Try again.</td></tr>
    <tr><td><strong>Report not generating</strong></td><td>Check date range. Ensure data exists for period. Reduce date range if too large.</td></tr>
    <tr><td><strong>Bank balance incorrect</strong></td><td>Review recent transactions. Check for unrecorded items. Run reconciliation.</td></tr>
    <tr><td><strong>Cannot approve voucher</strong></td><td>Check your permission level. Amount may exceed your limit. Contact supervisor.</td></tr>
    <tr><td><strong>PDF not downloading</strong></td><td>Check popup blocker settings. Try right-click "Save As". Use different browser.</td></tr>
    <tr><td><strong>Session expired</strong></td><td>System logs out after 30 minutes of inactivity. Log in again. Save work frequently.</td></tr>
    <tr><td><strong>Duplicate transaction</strong></td><td>Do not delete. Create reversing entry. Document the error. Record correctly.</td></tr>
    <tr><td><strong>Missing menu options</strong></td><td>Your role may not have access. Contact Finance Manager for permission changes.</td></tr>
</table>

<div class="info-box-blue" style="margin-top: 30px; text-align: center;">
    <strong>NEED HELP?</strong><br><br>
    IT Support: support@stfrancis.edu.zm | Extension 101<br>
    Finance Manager: finance@stfrancis.edu.zm | Extension 105<br>
    System Admin: admin@stfrancis.edu.zm | Extension 102
</div>

<div class="page-break"></div>

{{-- SECTION 15: QUICK REFERENCE --}}
<h1>15. Quick Reference</h1>

<h2>15.1 Navigation Shortcuts</h2>
<table>
    <tr><th>To Do This...</th><th>Navigate To...</th></tr>
    <tr><td>View financial overview</td><td><strong>Accounts → Accounts Dashboard</strong></td></tr>
    <tr><td>Record a new expense</td><td><strong>Accounts → Expenses → New Expense</strong></td></tr>
    <tr><td>Create payment voucher</td><td><strong>Accounts → Payment Vouchers → New</strong></td></tr>
    <tr><td>Record bank transaction</td><td><strong>Accounts → Bank Transactions → New</strong></td></tr>
    <tr><td>Generate reports</td><td><strong>Accounts → Financial Reports</strong></td></tr>
    <tr><td>Reconcile bank account</td><td><strong>Accounts → Bank Reconciliation</strong></td></tr>
    <tr><td>View Chart of Accounts</td><td><strong>Accounts → Chart of Accounts</strong></td></tr>
    <tr><td>Manage vendors</td><td><strong>Accounts → Vendors</strong></td></tr>
    <tr><td>Record manual income</td><td><strong>Accounts → Income Records → New</strong></td></tr>
</table>

<h2>15.2 Keyboard Shortcuts</h2>
<table>
    <tr><th>Shortcut</th><th>Action</th></tr>
    <tr><td><strong>Ctrl + S</strong></td><td>Save current form</td></tr>
    <tr><td><strong>Ctrl + P</strong></td><td>Print current page</td></tr>
    <tr><td><strong>Ctrl + F</strong></td><td>Open search/filter</td></tr>
    <tr><td><strong>F5</strong></td><td>Refresh page</td></tr>
    <tr><td><strong>Esc</strong></td><td>Cancel/Close modal</td></tr>
    <tr><td><strong>Tab</strong></td><td>Move to next field</td></tr>
</table>

<div class="footer">
    <p>— End of Document —</p>
    <p>Version 1.0 | {{ $generatedAt->format('F Y') }}</p>
</div>

</body>
</html>
